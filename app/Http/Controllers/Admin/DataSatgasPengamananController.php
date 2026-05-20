<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataSatgasPengamanan;
use App\Models\Upt;
use Inertia\Inertia;

class DataSatgasPengamananController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataSatgasPengamanan::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_input', $request->tanggal);

        $datasatgaspengamanans = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datasatgaspengamanan/Index', [
            'datasatgaspengamanans' => $datasatgaspengamanans,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/pengamanan/datasatgaspengamanan/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            
            'tanggal_pelaksanaan_penggeledahan' => 'nullable|date',
            'waktu_pelaksanaan_penggeledahan' => 'nullable',
            'lokasi_kamar_penggeledahan' => 'nullable|string|max:255',
            'jumlah_anggota_penggeledahan' => 'required|integer|min:0',
            'hasil_penggeledahan' => 'nullable|string',
            'tindak_lanjut_penggeledahan' => 'nullable|string',
            
            'tanggal_pelaksanaan_narkoba' => 'nullable|date',
            'waktu_pelaksanaan_narkoba' => 'nullable',
            'jumlah_yang_dites_narkoba' => 'required|integer|min:0',
            'hasil_tes_narkoba' => 'nullable|string',
            'tindak_lanjut_hasil_tes' => 'nullable|string',
        ]);

        DataSatgasPengamanan::create($validated);

        return redirect()->route('data-satgas-pengamanans.index')->with('success', 'Laporan Satgas Pengamanan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datasatgaspengamanan = DataSatgasPengamanan::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datasatgaspengamanan->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datasatgaspengamanan/Edit', [
            'datasatgaspengamanan' => $datasatgaspengamanan,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $datasatgaspengamanan = DataSatgasPengamanan::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            
            'tanggal_pelaksanaan_penggeledahan' => 'nullable|date',
            'waktu_pelaksanaan_penggeledahan' => 'nullable',
            'lokasi_kamar_penggeledahan' => 'nullable|string|max:255',
            'jumlah_anggota_penggeledahan' => 'required|integer|min:0',
            'hasil_penggeledahan' => 'nullable|string',
            'tindak_lanjut_penggeledahan' => 'nullable|string',
            
            'tanggal_pelaksanaan_narkoba' => 'nullable|date',
            'waktu_pelaksanaan_narkoba' => 'nullable',
            'jumlah_yang_dites_narkoba' => 'required|integer|min:0',
            'hasil_tes_narkoba' => 'nullable|string',
            'tindak_lanjut_hasil_tes' => 'nullable|string',
        ]);

        $datasatgaspengamanan->update($validated);

        return redirect()->route('data-satgas-pengamanans.index')->with('success', 'Laporan Satgas Pengamanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DataSatgasPengamanan::findOrFail($id)->delete();
        return redirect()->route('data-satgas-pengamanans.index')->with('success', 'Laporan Satgas Pengamanan berhasil dihapus.');
    }
}