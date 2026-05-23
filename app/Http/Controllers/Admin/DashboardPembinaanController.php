<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Upt;
use App\Models\DataRegistrasi;
use App\Models\DataResidivis;
use App\Models\DataPemindahan;
use App\Models\DataPembinaanKepribadian;
use App\Models\DataPembinaanKemandirian;
use App\Models\DataIntegrasi;

class DashboardPembinaanController extends Controller
{
    private function getMasterData()
    {
        return [
            'umums' => \App\Models\RegistrasiUmum::all(),
            'pidsuses' => \App\Models\RegistrasiPidsus::all(),
            'pidums' => \App\Models\RegistrasiPidum::all(),
            'overstayings' => \App\Models\RegistrasiOverstaying::all(),
            'integrasis' => \App\Models\JenisIntegrasi::all(),
            'identitases' => \App\Models\RegistrasiIdentitas::all(),
            'agamas' => \App\Models\JenisAgama::all(),
            'pengeluarans' => \App\Models\JenisPengeluaran::all(),
            'pendidikans' => \App\Models\RegistrasiPendidikan::all(),
            'detail_napis' => \App\Models\RegistrasiDetailNapi::all(),
            'detail_tahanans' => \App\Models\RegistrasiDetailTahanan::all(),
            'petugases' => \App\Models\RegistrasiPetugas::all(),
            'pengunjungs' => \App\Models\RegistrasiPengunjung::all(),
            'wbp_dikunjungis' => \App\Models\RegistrasiWbpDikunjungi::all(),
            'wbp_vidcalls' => \App\Models\RegistrasiWbpVidcall::all(),
            'wbp_wartels' => \App\Models\RegistrasiWbpWartel::all(),
            'barang_titipans' => \App\Models\RegistrasiBarangTitipan::all(),
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isUptUser = !empty($user->upt_id); // 🛠️ Deteksi apakah user orang UPT
        
        $tanggal = $request->tanggal ?? date('Y-m-d'); 
        // 🛠️ Paksa $uptId ke UPT user jika dia orang UPT, kalau bukan ambil dari request
        $uptId = $isUptUser ? $user->upt_id : $request->upt_id; 
        $modul = $request->modul ?? 'ringkasan';
        
        $qRegistrasi = DataRegistrasi::with('upt')->whereDate('tanggal', $tanggal);
        if ($uptId) $qRegistrasi->where('upt_id', $uptId);
        $dataRegistrasi = $qRegistrasi->get();

        $qResidivis = DataResidivis::with(['upt', 'jenis_pidana_sekarang'])->whereDate('tanggal', $tanggal);
        if ($uptId) $qResidivis->where('upt_id', $uptId);
        $dataResidivis = $qResidivis->get();

        $qPemindahan = DataPemindahan::with(['uptAsal', 'uptTujuan', 'jenisPemindahan'])->whereDate('tanggal_pelaksanaan', $tanggal);
        if ($uptId) $qPemindahan->where('upt_id', $uptId);
        $dataPemindahan = $qPemindahan->get();

        $qIntegrasi = DataIntegrasi::with('upt')->whereDate('tanggal_input', $tanggal);
        if ($uptId) $qIntegrasi->where('upt_id', $uptId);
        $dataIntegrasi = $qIntegrasi->get();

        $qKepribadian = DataPembinaanKepribadian::whereDate('tanggal', $tanggal);
        if ($uptId) $qKepribadian->where('upt_id', $uptId);
        $dataKepribadian = $qKepribadian->get();

        $qKemandirian = DataPembinaanKemandirian::whereDate('tanggal', $tanggal);
        if ($uptId) $qKemandirian->where('upt_id', $uptId);
        $dataKemandirian = $qKemandirian->get();

        $highlight = [
            'total_residivis' => $dataResidivis->count(),
            'total_pemindahan' => $dataPemindahan->count(),
            'kegiatan_kepribadian' => $dataKepribadian->count(),
            'kegiatan_kemandirian' => $dataKemandirian->count(),
            'total_peserta_kepribadian' => $dataKepribadian->sum('jumlah_peserta'),
            'total_peserta_kemandirian' => $dataKemandirian->sum('jumlah_peserta'),
            'total_integrasi' => $dataIntegrasi->sum('jumlah_pb') + 
                                 $dataIntegrasi->sum('jumlah_cb') + 
                                 $dataIntegrasi->sum('jumlah_cmb') + 
                                 $dataIntegrasi->sum('jumlah_asimilasi') + 
                                 $dataIntegrasi->sum('jumlah_bebas_murni') + 
                                 $dataIntegrasi->sum('jumlah_cmk'),
        ];

        $dataDinamis = [];
        
        if ($modul === 'registrasi') {
            if (empty($uptId) && $dataRegistrasi->count() > 0) {
                $rekapFields = [
                    'rekap_umum', 'rekap_pidsus', 'rekap_pidum', 'rekap_overstaying', 
                    'rekap_integrasi', 'rekap_identitas', 'rekap_agama', 'rekap_pengeluaran',
                    'rekap_pendidikan', 'rekap_detail_napi', 'rekap_detail_tahanan',
                    'rekap_petugas', 'rekap_pengunjung', 'rekap_wbp_dikunjungi',
                    'rekap_wbp_vidcall', 'rekap_wbp_wartel', 'rekap_barang_titipan'
                ];

                $aggregated = [
                    'id' => 'kanwil',
                    'upt' => ['name' => 'KUMULATIF KANWIL (Semua UPT)'],
                    'tanggal' => $tanggal,
                ];

                foreach ($rekapFields as $field) {
                    $aggregated[$field] = [];
                }

                foreach ($dataRegistrasi as $item) {
                    foreach ($rekapFields as $field) {
                        $dataArray = $item->$field ?? [];
                        if (is_array($dataArray)) {
                            foreach ($dataArray as $key => $val) {
                                // 🛠️ Logika Rekap Spesifik WNA
                                if ($field === 'rekap_umum' && $key === 'detail_wna') {
                                    if (!isset($aggregated[$field][$key])) $aggregated[$field][$key] = [];
                                    if (is_array($val)) {
                                        foreach ($val as $wna) {
                                            $wna['upt_name'] = $item->upt->name ?? 'UPT Tidak Diketahui';
                                            $aggregated[$field][$key][] = $wna;
                                        }
                                    }
                                } 
                                // 🛠️ Logika Rekap Spesifik Overstaying (Filter text Nihil & jadikan Array)
                                elseif ($field === 'rekap_overstaying' && isset($val['jumlah'])) {
                                    if (!isset($aggregated[$field][$key])) {
                                        $aggregated[$field][$key] = ['jumlah' => 0, 'detail_keterangan' => []];
                                    }
                                    $aggregated[$field][$key]['jumlah'] += (int)$val['jumlah'];
                                    
                                    $ket = trim($val['keterangan'] ?? '');
                                    // Hanya masukkan keterangan jika isinya BUKAN Nihil, 0, atau kosong
                                    if (!empty($ket) && !in_array(strtolower($ket), ['nihil', '-', '0', 'tidak ada', 'kosong'])) {
                                        $aggregated[$field][$key]['detail_keterangan'][] = [
                                            'upt' => $item->upt->name ?? 'UPT',
                                            'text' => $ket
                                        ];
                                    }
                                }
                                // Sum untuk Number biasa
                                elseif (is_numeric($val)) {
                                    $aggregated[$field][$key] = ($aggregated[$field][$key] ?? 0) + $val;
                                } else {
                                    $aggregated[$field][$key] = $val;
                                }
                            }
                        }
                    }
                }
                
                $dataDinamis = collect([(object) $aggregated]);
            } else {
                $dataDinamis = $dataRegistrasi;
            }
        } 
        elseif ($modul === 'residivis') { $dataDinamis = $dataResidivis; } 
        elseif ($modul === 'pemindahan') { $dataDinamis = $dataPemindahan; } 
        elseif ($modul === 'integrasi') { $dataDinamis = $dataIntegrasi; }

        if ($isUptUser) {
            $upts = Upt::where('id', $user->upt_id)->get();
        } else {
            $upts = Upt::where('is_active', true)->get();
        }

        $viewData = array_merge([
            // 🛠️ Sisipkan 'is_upt_user' ke dalam filters agar bisa dibaca Vue
            'filters' => [
                'tanggal' => $tanggal, 
                'upt_id' => $uptId ?? '', 
                'modul' => $modul,
                'is_upt_user' => $isUptUser 
            ],
            'upts' => $upts,
            'highlight' => $highlight,
            'data_dinamis' => $dataDinamis,
            'summary_data' => ['kepribadian' => $dataKepribadian, 'kemandirian' => $dataKemandirian]
        ], $this->getMasterData());

        return Inertia::render('admin/pembinaan/dashboard/Index', $viewData);
    }
}