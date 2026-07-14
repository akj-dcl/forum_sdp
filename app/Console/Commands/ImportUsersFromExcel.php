<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportUsersFromExcel extends Command
{
    protected $signature = 'import:users-excel';
    protected $description = 'Import user roles and accounts from data_user_upt.xlsx';

    public function handle()
    {
        $filePath = base_path('data_user_upt.xlsx');
        if (!file_exists($filePath)) {
            $this->error("Excel file not found at: " . $filePath);
            return 1;
        }

        $this->info("Performing database cleanup...");
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::table('posts')->truncate();
        DB::table('comments')->truncate();
        DB::table('post_reactions')->truncate();
        DB::table('direct_messages')->truncate();
        DB::table('corporate_highlights')->truncate();
        
        DB::table('users')->where('id', '>', 1)->delete();
        
        DB::table('model_has_roles')->where('model_id', '>', 1)->delete();
        DB::table('model_has_permissions')->where('model_id', '>', 1)->delete();
        
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 2;');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        $this->info("Database cleanup completed successfully!");

        $this->info("Loading Excel file...");
        set_error_handler(function($errno, $errstr) {
            return (strpos($errstr, 'simplexml_load_string') !== false);
        });
        $spreadsheet = IOFactory::load($filePath);
        restore_error_handler();
        $this->info("Excel file loaded successfully!");

        $dbKanwils = DB::table('kanwils')->get()->toArray();
        $dbUpts = DB::table('upts')->get()->toArray();

        $generatedNips = [];
        $totalRolesCreated = 0;
        $totalUsersImported = 0;

        $this->info("Step 1: Importing and creating all unique roles...");
        $uniqueRoles = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $rows = $sheet->toArray(null, true, true, true);
            array_shift($rows);
            foreach ($rows as $row) {
                $roleField = $row['D'] ?? '';
                if (empty($roleField)) continue;
                $roles = array_map('trim', explode(',', $roleField));
                foreach ($roles as $r) {
                    if ($r !== '') {
                        $uniqueRoles[$r] = true;
                    }
                }
            }
        }

        foreach (array_keys($uniqueRoles) as $roleName) {
            Role::findOrCreate($roleName, 'web');
            $totalRolesCreated++;
        }
        $this->info("Successfully registered {$totalRolesCreated} unique roles in database.");
        $this->info("Step 2: Importing users...");
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = $sheet->getTitle();
            $this->info("Processing sheet: {$sheetName}...");

            $rows = $sheet->toArray(null, true, true, true);
            array_shift($rows);

            $currentKanwil = null;
            $currentUpt = null;

            foreach ($rows as $row) {
                $nama = trim($row['E'] ?? '');
                if (empty($nama)) continue;
                if (!empty($row['B'])) {
                    $currentKanwil = trim($row['B']);
                }
                if (!empty($row['C'])) {
                    $currentUpt = trim($row['C']);
                }
                $kanwilId = null;
                if (!empty($currentKanwil)) {
                    $kanwilId = $this->resolveKanwilId($currentKanwil, $dbKanwils);
                }
                $uptId = null;
                if (!empty($currentUpt)) {
                    $uptId = $this->resolveUptId($currentUpt, $kanwilId, $dbUpts);
                }
                $golonganField = trim($row['G'] ?? '');
                $golonganId = $this->getGolonganId($golonganField);
                $nip = trim($row['F'] ?? '');
                if (empty($nip) || $nip === '-') {
                    do {
                        $nip = '9999' . mt_rand(10000000, 99999999) . mt_rand(100000, 999999);
                    } while (User::where('username', $nip)->orWhere('nip', $nip)->exists());
                    
                    $generatedNips[] = [
                        'sheet' => $sheetName,
                        'name' => $nama,
                        'kanwil' => $currentKanwil,
                        'upt' => $currentUpt,
                        'nip' => $nip
                    ];
                }
                $roleField = $row['D'] ?? '';
                $userRoles = [];
                if (!empty($roleField)) {
                    $userRoles = array_map('trim', explode(',', $roleField));
                    $userRoles = array_filter($userRoles, function($r) {
                        return $r !== '';
                    });
                }
                $user = User::updateOrCreate(
                    ['username' => $nip],
                    [
                        'nip' => $nip,
                        'name' => $nama,
                        'email' => null,
                        'jabatan' => trim($row['H'] ?? '') ?: null,
                        'kanwil_id' => $kanwilId,
                        'upt_id' => $uptId,
                        'jenis_golongan_id' => $golonganId,
                        'password' => Hash::make($nip)
                    ]
                );

                if (!empty($userRoles)) {
                    $user->syncRoles($userRoles);
                }

                $totalUsersImported++;
                if ($totalUsersImported % 500 === 0) {
                    $this->info("Imported {$totalUsersImported} users...");
                }
            }
        }

        $this->info("Saving generated NIPs report...");
        $this->writeReport($generatedNips);

        $this->info("Import finished! Total users imported/updated: {$totalUsersImported}. Total unique roles: {$totalRolesCreated}.");
        return 0;
    }

    private function resolveKanwilId($name, $dbKanwils)
    {
        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $overrides = [
            'ntt' => 'nusatenggaratimur',
            'daerahistimewayogyakarta' => 'diyogyakarta',
            'diyogyakarta' => 'diyogyakarta'
        ];
        if (isset($overrides[$cleanName])) {
            $cleanName = $overrides[$cleanName];
        }

        foreach ($dbKanwils as $k) {
            $cleanDbName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $k->name));
            if ($cleanDbName === 'diyogyakarta') {
                $cleanDbName = 'diyogyakarta';
            }
            if ($cleanDbName === $cleanName) {
                return $k->id;
            }
        }
        return null;
    }

    private function resolveUptId($name, $kanwilId, $dbUpts)
    {
        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        if ($cleanName === '' || $cleanName === '-' || $cleanName === 'kanwil') {
            return null;
        }

        foreach ($dbUpts as $u) {
            if ($kanwilId !== null && $u->kanwil_id !== $kanwilId) {
                continue;
            }
            $cleanDbName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $u->name));
            if ($cleanDbName === $cleanName) {
                return $u->id;
            }
        }
        foreach ($dbUpts as $u) {
            $cleanDbName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $u->name));
            if ($cleanDbName === $cleanName) {
                return $u->id;
            }
        }

        return null;
    }

    private function getGolonganId($rankString)
    {
        if (empty($rankString)) return null;
        $clean = strtolower(str_replace(' ', '', $rankString));
        
        if (strpos($clean, 'iv/e') !== false || strpos($clean, 'ive') !== false) return 17;
        if (strpos($clean, 'iv/d') !== false || strpos($clean, 'ivd') !== false) return 16;
        if (strpos($clean, 'iv/c') !== false || strpos($clean, 'ivc') !== false) return 15;
        if (strpos($clean, 'iv/b') !== false || strpos($clean, 'ivb') !== false) return 14;
        if (strpos($clean, 'iv/a') !== false || strpos($clean, 'iva') !== false) return 13;
        
        if (strpos($clean, 'iii/d') !== false || strpos($clean, 'iiid') !== false) return 12;
        if (strpos($clean, 'iii/c') !== false || strpos($clean, 'iiic') !== false) return 11;
        if (strpos($clean, 'iii/b') !== false || strpos($clean, 'iiib') !== false) return 10;
        if (strpos($clean, 'iii/a') !== false || strpos($clean, 'iiia') !== false) return 9;
        
        if (strpos($clean, 'ii/d') !== false || strpos($clean, 'iid') !== false) return 8;
        if (strpos($clean, 'ii/c') !== false || strpos($clean, 'iic') !== false) return 7;
        if (strpos($clean, 'ii/b') !== false || strpos($clean, 'iib') !== false) return 6;
        if (strpos($clean, 'ii/a') !== false || strpos($clean, 'iia') !== false) return 5;
        
        if (strpos($clean, 'i/d') !== false || strpos($clean, 'id') !== false) return 4;
        if (strpos($clean, 'i/c') !== false || strpos($clean, 'ic') !== false) return 3;
        if (strpos($clean, 'i/b') !== false || strpos($clean, 'ib') !== false) return 2;
        if (strpos($clean, 'i/a') !== false || strpos($clean, 'ia') !== false) return 1;
        
        return null;
    }

    private function writeReport($generatedNips)
    {
        $reportPath = '/Users/agustokenyjia/.gemini/antigravity-ide/brain/e2b71cf4-cff6-4fec-a206-9a78322fed84/import_nips_report.md';
        
        $md = "# Laporan Hasil NIP Buatan Acak (Excel Import)\n\n";
        $md .= "Berikut adalah daftar 34 user yang memiliki NIP `-` di file Excel, dan telah dibuatkan NIP acak unik beserta password yang disamakan dengan NIP tersebut:\n\n";
        $md .= "| No | Nama | Sheet | Kanwil | UPT | NIP Hasil / Password |\n";
        $md .= "|---|---|---|---|---|---|\n";
        
        foreach ($generatedNips as $index => $item) {
            $no = $index + 1;
            $md .= "| {$no} | {$item['name']} | {$item['sheet']} | {$item['kanwil']} | {$item['upt']} | `{$item['nip']}` |\n";
        }
        
        file_put_contents($reportPath, $md);
    }
}
