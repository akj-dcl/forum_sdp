<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataSdmBulanan;
use App\Models\Upt;
use App\Models\JenisPendidikan;
use App\Models\JenisUsulan;
use App\Models\JenisStruktural;
use App\Models\JenisGolongan;
use App\Models\JenisBeban;
use App\Models\JenisStaff;
use Inertia\Inertia;

class DataSdmBulananController extends Controller
{
    // Fungsi pembantu untuk memanggil semua data master
    private function getMasterData()
    {
        return [
            'jenis_pendidikans' => JenisPendidikan::all(),
            'jenis_usulans' => JenisUsulan::all(),
            'jenis_strukturals' => JenisStruktural::all(),
            'jenis_golongans' => JenisGolongan::all(),
            'jenis_bebans' => JenisBeban::all(),
            'jenis_staffs' => JenisStaff::all(),
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataSdmBulanan::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_input', $request->tanggal);

        $datasdmbulanans = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/datasdmbulanan/Index', array_merge([
            'datasdmbulanans' => $datasdmbulanans,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ], $this->getMasterData()));
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/tudanumum/datasdmbulanan/Create', array_merge([
            'upts' => $upts,
        ], $this->getMasterData()));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            
            // 6 Array JSON
            'jumlah_pegawai_bypendidikan' => 'nullable|array',
            'jumlah_pegawai_byusulan' => 'nullable|array',
            'jumlah_pejabat_struktural' => 'nullable|array',
            'jumlah_pegawai_bygolongan' => 'nullable|array',
            'jumlah_pegawai_bybeban' => 'nullable|array',
            'jumlah_petugas_penjagaan' => 'nullable|array',
            
            // Angka Biasa
            'jumlah_pegawai_laki' => 'required|integer|min:0',
            'jumlah_pegawai_perempuan' => 'required|integer|min:0',
            'jumlah_jf' => 'required|integer|min:0',
            'jumlah_staff_pembinaan' => 'required|integer|min:0',
            'jumlah_staff_pengamanan' => 'required|integer|min:0',
            'jumlah_staff_tudanumum' => 'required|integer|min:0',
            'jumlah_staff_bimker' => 'required|integer|min:0',
            'jumlah_pegawai_diktek' => 'required|integer|min:0',
            'jumlah_pegawai_dikman' => 'required|integer|min:0',
            'jumlah_pegawai_hukdis' => 'required|integer|min:0',
        ]);

        DataSdmBulanan::create($validated);

        return redirect()->route('data-sdm-bulanans.index')->with('success', 'Data SDM Bulanan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datasdmbulanan = DataSdmBulanan::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datasdmbulanan->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/datasdmbulanan/Edit', array_merge([
            'datasdmbulanan' => $datasdmbulanan,
            'upts' => $upts,
        ], $this->getMasterData()));
    }

    public function update(Request $request, $id)
    {
        $datasdmbulanan = DataSdmBulanan::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            
            'jumlah_pegawai_bypendidikan' => 'nullable|array',
            'jumlah_pegawai_byusulan' => 'nullable|array',
            'jumlah_pejabat_struktural' => 'nullable|array',
            'jumlah_pegawai_bygolongan' => 'nullable|array',
            'jumlah_pegawai_bybeban' => 'nullable|array',
            'jumlah_petugas_penjagaan' => 'nullable|array',
            
            'jumlah_pegawai_laki' => 'required|integer|min:0',
            'jumlah_pegawai_perempuan' => 'required|integer|min:0',
            'jumlah_jf' => 'required|integer|min:0',
            'jumlah_staff_pembinaan' => 'required|integer|min:0',
            'jumlah_staff_pengamanan' => 'required|integer|min:0',
            'jumlah_staff_tudanumum' => 'required|integer|min:0',
            'jumlah_staff_bimker' => 'required|integer|min:0',
            'jumlah_pegawai_diktek' => 'required|integer|min:0',
            'jumlah_pegawai_dikman' => 'required|integer|min:0',
            'jumlah_pegawai_hukdis' => 'required|integer|min:0',
        ]);

        $datasdmbulanan->update($validated);

        return redirect()->route('data-sdm-bulanans.index')->with('success', 'Data SDM Bulanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DataSdmBulanan::findOrFail($id)->delete();
        return redirect()->route('data-sdm-bulanans.index')->with('success', 'Data SDM Bulanan berhasil dihapus.');
    }
}