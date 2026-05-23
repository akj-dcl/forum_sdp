<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataRegistrasi;
use Inertia\Inertia;
use App\Models\Upt;
use App\Models\RegistrasiUmum;
use App\Models\RegistrasiPidsus;
use App\Models\RegistrasiPidum;
use App\Models\RegistrasiOverstaying;
use App\Models\JenisIntegrasi;
use App\Models\RegistrasiIdentitas;
use App\Models\JenisAgama;
use App\Models\JenisPengeluaran;
use App\Models\RegistrasiPendidikan;
use App\Models\RegistrasiDetailNapi;
use App\Models\RegistrasiDetailTahanan;
use App\Models\RegistrasiPetugas;
use App\Models\RegistrasiPengunjung;
use App\Models\RegistrasiWbpDikunjungi;
use App\Models\RegistrasiWbpVidcall;
use App\Models\RegistrasiWbpWartel;
use App\Models\RegistrasiBarangTitipan;

class DataRegistrasiController extends Controller
{
    private function getMasterData()
    {
        return [
            'umums' => RegistrasiUmum::all(),
            'pidsuses' => RegistrasiPidsus::all(),
            'pidums' => RegistrasiPidum::all(),
            'overstayings' => RegistrasiOverstaying::all(),
            'integrasis' => JenisIntegrasi::all(),
            'identitases' => RegistrasiIdentitas::all(),
            'agamas' => JenisAgama::all(),
            'pengeluarans' => JenisPengeluaran::all(),
            'pendidikans' => RegistrasiPendidikan::all(),
            'detail_napis' => RegistrasiDetailNapi::all(),
            'detail_tahanans' => RegistrasiDetailTahanan::all(),
            'petugases' => RegistrasiPetugas::all(),
            'pengunjungs' => RegistrasiPengunjung::all(),
            'wbp_dikunjungis' => RegistrasiWbpDikunjungi::all(),
            'wbp_vidcalls' => RegistrasiWbpVidcall::all(),
            'wbp_wartels' => RegistrasiWbpWartel::all(),
            'barang_titipans' => RegistrasiBarangTitipan::all(),
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataRegistrasi::with(['upt']);

        if ($user->upt_id) {
            $query->where('upt_id', $user->upt_id);
        } elseif ($request->upt_id) {
            $query->where('upt_id', $request->upt_id);
        }

        if ($request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $dataregistrasis = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataregistrasi/Index', array_merge([
            'dataregistrasis' => $dataregistrasis,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ], $this->getMasterData()));
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataregistrasi/Create', array_merge([
            'upts' => $upts,
        ], $this->getMasterData()));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'upt_id' => 'required|exists:upts,id',
            'rekap_umum' => 'nullable|array',
            'rekap_pidsus' => 'nullable|array',
            'rekap_pidum' => 'nullable|array',
            'rekap_overstaying' => 'nullable|array',
            'rekap_integrasi' => 'nullable|array',
            'rekap_identitas' => 'nullable|array',
            'rekap_agama' => 'nullable|array',
            'rekap_pengeluaran' => 'nullable|array',
            'rekap_pendidikan' => 'nullable|array',
            'rekap_detail_napi' => 'nullable|array',
            'rekap_detail_tahanan' => 'nullable|array',
            'rekap_petugas' => 'nullable|array',
            'rekap_pengunjung' => 'nullable|array',
            'rekap_wbp_dikunjungi' => 'nullable|array',
            'rekap_wbp_vidcall' => 'nullable|array',
            'rekap_wbp_wartel' => 'nullable|array',
            'rekap_barang_titipan' => 'nullable|array',
        ]);

        DataRegistrasi::create($validated);
        return redirect()->route('data-registrasis.index')->with('success', 'Laporan Rekap Harian Registrasi berhasil disimpan.');
    }

    public function edit($id)
    {
        $dataregistrasi = DataRegistrasi::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $dataregistrasi->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataregistrasi/Edit', array_merge([
            'dataregistrasi' => $dataregistrasi,
            'upts' => $upts,
        ], $this->getMasterData()));
    }

    public function update(Request $request, $id)
    {
        $dataregistrasi = DataRegistrasi::findOrFail($id);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'upt_id' => 'required|exists:upts,id',
            'rekap_umum' => 'nullable|array',
            'rekap_pidsus' => 'nullable|array',
            'rekap_pidum' => 'nullable|array',
            'rekap_overstaying' => 'nullable|array',
            'rekap_integrasi' => 'nullable|array',
            'rekap_identitas' => 'nullable|array',
            'rekap_agama' => 'nullable|array',
            'rekap_pengeluaran' => 'nullable|array',
            'rekap_pendidikan' => 'nullable|array',
            'rekap_detail_napi' => 'nullable|array',
            'rekap_detail_tahanan' => 'nullable|array',
            'rekap_petugas' => 'nullable|array',
            'rekap_pengunjung' => 'nullable|array',
            'rekap_wbp_dikunjungi' => 'nullable|array',
            'rekap_wbp_vidcall' => 'nullable|array',
            'rekap_wbp_wartel' => 'nullable|array',
            'rekap_barang_titipan' => 'nullable|array',
        ]);

        $dataregistrasi->update($validated);
        return redirect()->route('data-registrasis.index')->with('success', 'Laporan Rekap Harian Registrasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DataRegistrasi::findOrFail($id)->delete();
        return redirect()->route('data-registrasis.index')->with('success', 'Laporan Rekap Registrasi berhasil dihapus.');
    }
}