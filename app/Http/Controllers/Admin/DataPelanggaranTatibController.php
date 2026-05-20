<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPelanggaranTatib;
use App\Models\Upt;
use Inertia\Inertia;

class DataPelanggaranTatibController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataPelanggaranTatib::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_pelanggaran', $request->tanggal);

        $datapelanggarantatibs = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datapelanggarantatib/Index', [
            'datapelanggarantatibs' => $datapelanggarantatibs,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/pengamanan/datapelanggarantatib/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'nama_wbp' => 'required|string|max:255',
            'tanggal_pelanggaran' => 'nullable|date',
            'waktu_pelanggaran' => 'nullable',
            'jenis_pelanggaran' => 'nullable|string|max:255',
            'pasal_pelanggaran' => 'nullable|string|max:255',
            'tindak_lanjut' => 'nullable|string',
            'rekomendasi_pembinaan' => 'nullable|string',
        ]);

        DataPelanggaranTatib::create($validated);

        return redirect()->route('data-pelanggaran-tatibs.index')->with('success', 'Data Pelanggaran Tatib berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datapelanggarantatib = DataPelanggaranTatib::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datapelanggarantatib->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datapelanggarantatib/Edit', [
            'datapelanggarantatib' => $datapelanggarantatib,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $datapelanggarantatib = DataPelanggaranTatib::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'nama_wbp' => 'required|string|max:255',
            'tanggal_pelanggaran' => 'nullable|date',
            'waktu_pelanggaran' => 'nullable',
            'jenis_pelanggaran' => 'nullable|string|max:255',
            'pasal_pelanggaran' => 'nullable|string|max:255',
            'tindak_lanjut' => 'nullable|string',
            'rekomendasi_pembinaan' => 'nullable|string',
        ]);

        $datapelanggarantatib->update($validated);

        return redirect()->route('data-pelanggaran-tatibs.index')->with('success', 'Data Pelanggaran Tatib berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DataPelanggaranTatib::findOrFail($id)->delete();
        return redirect()->route('data-pelanggaran-tatibs.index')->with('success', 'Data Pelanggaran Tatib berhasil dihapus.');
    }
}