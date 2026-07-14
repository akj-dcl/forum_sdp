<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kanwil;
use App\Models\Upt;
use App\Services\SdpMasterService;
use Illuminate\Support\Facades\DB;

class SyncSdpMasterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-sdp-master';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Kanwil and UPT data from SDP Amnesti API';

    /**
     * Execute the console command.
     */
    public function handle(SdpMasterService $sdp)
    {
        $this->info('Starting SDP Master data synchronization...');

        try {
            // 1. Sync Kanwils
            $this->info('Fetching Kanwils...');
            $kanwilsData = $sdp->getKanwil();
            $this->info('Found ' . count($kanwilsData) . ' Kanwils. Syncing to database...');

            DB::transaction(function () use ($kanwilsData) {
                foreach ($kanwilsData as $item) {
                    $id = (int) $item['KANWIL_ID'];
                    $kanwil = Kanwil::find($id);
                    if (!$kanwil) {
                        $kanwil = new Kanwil();
                        $kanwil->id = $id;
                    }
                    $kanwil->name = $item['NAMA_KANWIL'];
                    $kanwil->code = $item['KANWIL_ID'];
                    $kanwil->is_active = true;
                    $kanwil->save();
                }
            });
            $this->info('Kanwils sync completed.');

            // 2. Sync UPTs
            $this->info('Fetching UPTs...');
            $uptsData = $sdp->getUpt();
            $this->info('Found ' . count($uptsData) . ' UPTs. Syncing to database...');

            DB::transaction(function () use ($uptsData) {
                foreach ($uptsData as $item) {
                    $kanwilId = (int) $item['KANWIL_ID'];

                    // Double-check Kanwil exists to satisfy foreign key constraints
                    $kanwil = Kanwil::find($kanwilId);
                    if (!$kanwil) {
                        $kanwil = new Kanwil();
                        $kanwil->id = $kanwilId;
                        $kanwil->name = $item['NAMA_KANWIL'] ?? 'Kanwil ' . $item['KANWIL_ID'];
                        $kanwil->code = $item['KANWIL_ID'];
                        $kanwil->is_active = true;
                        $kanwil->save();
                    }

                    $uptId = (int) $item['UPT_ID'];
                    $upt = Upt::find($uptId);
                    if (!$upt) {
                        $upt = new Upt();
                        $upt->id = $uptId;
                    }
                    $upt->kanwil_id = $kanwilId;
                    $upt->name = $item['NAMA_UPT'];
                    $upt->address = $item['ALAMAT'] ?? null;
                    $upt->is_active = true;
                    $upt->save();
                }
            });
            $this->info('UPTs sync completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Synchronization failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
