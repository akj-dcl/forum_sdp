<?php

namespace App\Http\Controllers;

use App\Models\SurveiLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SurveiLayananController extends Controller
{
    public function create()
    {
        return Inertia::render('SurveiLayanan');
    }

    public function store(Request $request)
    {
        $request->validate([
            'surveys' => 'required|array|min:1',
            'surveys.*.nama_layanan' => 'required|string',
            'surveys.*.status_berjalan' => 'required|boolean',
        ]);

        $user = auth()->user();
        $uptId = $user->upt_id ?? 1;

        $minRules = [
            'Registrasi' => 3,
            'Integrasi' => 3,
            'Kunjungan' => 3,
            'Portir' => 1,
            'Perawatan Kesehatan' => 2,
            'Keamanan' => 2,
            'SPPN' => 5,
            'Self Service WBP' => 2,
        ];

        DB::transaction(function () use ($request, $user, $uptId, $minRules) {
            foreach ($request->surveys as $index => $item) {
                $statusBerjalan = filter_var($item['status_berjalan'], FILTER_VALIDATE_BOOLEAN);

                $data = [
                    'user_id' => $user->id,
                    'upt_id' => $uptId,
                    'nama_layanan' => $item['nama_layanan'],
                    'status_berjalan' => $statusBerjalan,
                    'sarana' => $statusBerjalan ? ($item['sarana'] ?? null) : null,
                    'status_kelengkapan' => $statusBerjalan ? ($item['status_kelengkapan'] ?? null) : null,
                    'periode_pengadaan' => $statusBerjalan ? ($item['periode_pengadaan'] ?? null) : null,
                    'jumlah_perangkat' => $statusBerjalan ? ($item['jumlah_perangkat'] ?? 0) : null,
                ];

                if ($statusBerjalan) {
                    $threshold = $minRules[$item['nama_layanan']] ?? 3;
                    $count = (int) ($item['jumlah_perangkat'] ?? 0);
                    $data['status_perangkat'] = ($count >= $threshold) ? 'Ideal' : 'Tidak Ideal';

                    $photoPaths = [];
                    if ($request->hasFile("surveys.{$index}.foto_perangkat")) {
                        $files = $request->file("surveys.{$index}.foto   _perangkat");
                        if (!is_array($files)) {
                            $files = [$files];
                        }
                        foreach ($files as $file) {
                            $photoPaths[] = $file->store('survei_foto', 'public');
                        }
                    }
                    $data['foto_perangkat'] = count($photoPaths) > 0 ? $photoPaths : null;
                }

                SurveiLayanan::create($data);
            }
        });

        return redirect()->route('dashboard')->with('success', count($request->surveys) . ' survei layanan berhasil disimpan!');
    }
}
