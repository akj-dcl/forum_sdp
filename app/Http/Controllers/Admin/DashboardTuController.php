<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Upt;
use App\Models\DataHarianSdm;
use App\Models\DataBulananSdm;
use App\Models\DataRealisasiAnggaran;
use App\Models\DataPublikasiSdm;
use App\Models\DataMouPks;

class DashboardTuController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isUptUser = !empty($user->upt_id);
        
        $tanggal = $request->tanggal ?? date('Y-m-d'); 
        $uptId = $isUptUser ? $user->upt_id : $request->upt_id; 
        $modul = $request->modul ?? 'ringkasan'; 
        
        // 1. Ambil Data Harian SDM
        $qHarianSdm = DataHarianSdm::with('upt')->whereDate('tanggal_input', $tanggal);
        if ($uptId) $qHarianSdm->where('upt_id', $uptId);
        $dataHarianSdm = $qHarianSdm->get();

        // 2. Ambil Data Publikasi Kehumasan
        $qPublikasi = DataPublikasiSdm::with('upt')->whereDate('tanggal_publikasi', $tanggal);
        if ($uptId) $qPublikasi->where('upt_id', $uptId);
        $dataPublikasi = $qPublikasi->get();

        // 3. Ambil Data Realisasi Anggaran (Harian)
        $qAnggaran = DataRealisasiAnggaran::with('upt')->whereDate('tanggal_input', $tanggal);
        if ($uptId) $qAnggaran->where('upt_id', $uptId);
        $dataAnggaran = $qAnggaran->get();

        // 4. Ambil Data MOU / PKS (Kumulatif / Aktif)
        $qMou = DataMouPks::with('upt');
        if ($uptId) {
            $qMou->where(function ($q) use ($uptId) {
                $q->where('upt_id', $uptId)->orWhereIn('kategori_pemrakarsa', ['KANWIL', 'DITJENPAS']);
            });
        }
        $dataMou = $qMou->get();

        // 5. Highlight & Kalkulasi untuk Kartu Atas
        $totalPegawaiHarian = $dataHarianSdm->sum('jumlah_pegawai_keseluruhan');
        $totalPublikasi = $dataPublikasi->count();
        
        // Kalkulasi Anggaran
        $totalPagu = $dataAnggaran->sum('belanja_pegawai_pagu') + $dataAnggaran->sum('belanja_barang_pagu') + $dataAnggaran->sum('belanja_modal_pagu');
        $totalRealisasi = $dataAnggaran->sum('belanja_pegawai_realisasi') + $dataAnggaran->sum('belanja_barang_realisasi') + $dataAnggaran->sum('belanja_modal_realisasi');
        $persentaseAnggaran = $totalPagu > 0 ? round(($totalRealisasi / $totalPagu) * 100, 2) : 0;

        $mouAktif = $dataMou->where('status_tahapan', 'Selesai')->count();
        $mouProses = $dataMou->where('status_tahapan', '!=', 'Selesai')->count();

        $highlight = [
            'total_pegawai_harian' => $totalPegawaiHarian,
            'total_publikasi' => $totalPublikasi,
            'persentase_anggaran' => $persentaseAnggaran,
            'mou_aktif' => $mouAktif,
            'mou_proses' => $mouProses,
        ];

        // 6. Data Dinamis Sesuai Modul
        $dataDinamis = [];
        if ($modul === 'sdm') { $dataDinamis = $dataHarianSdm; }
        elseif ($modul === 'keuangan') { $dataDinamis = $dataAnggaran; }
        elseif ($modul === 'kehumasan') { $dataDinamis = $dataPublikasi; }
        elseif ($modul === 'kerjasama') { $dataDinamis = $dataMou; }

        if ($isUptUser) {
            $upts = Upt::where('id', $user->upt_id)->get();
        } else {
            $upts = Upt::where('is_active', true)->get();
        }

        return Inertia::render('admin/tudanumum/dashboard/Index', [
            'filters' => [
                'tanggal' => $tanggal, 
                'upt_id' => $uptId ?? '', 
                'modul' => $modul,
                'is_upt_user' => $isUptUser
            ],
            'upts' => $upts,
            'highlight' => $highlight,
            'data_dinamis' => $dataDinamis,
            'summary' => [
                'publikasi' => $dataPublikasi,
                'anggaran' => $dataAnggaran
            ]
        ]);
    }
}