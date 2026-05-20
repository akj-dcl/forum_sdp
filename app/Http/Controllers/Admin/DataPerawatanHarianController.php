<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPerawatanHarian;
use App\Models\Upt;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DataPerawatanHarianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataPerawatanHarian::with(['upt']);

        if ($user->upt_id) {
            $query->where('upt_id', $user->upt_id);
        } elseif ($request->upt_id) {
            $query->where('upt_id', $request->upt_id);
        }

        if ($request->tanggal) {
            $query->whereDate('tanggal_input', $request->tanggal);
        }

        $dataperawatanharians = $query->latest()->paginate(10)->withQueryString();

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/dataperawatanharian/Index', [
            'dataperawatanharians' => $dataperawatanharians,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        return Inertia::render('admin/pengamanan/dataperawatanharian/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jumlah_penghuni_berobat' => 'required|integer|min:0',
            'jumlah_penghuni_dirawat' => 'required|integer|min:0',
            'jumlah_wbp_meninggal' => 'required|integer|min:0',
            'jumlah_nama_berobatjalan' => 'required|integer|min:0',
            'jumlah_nama_rawatklinik' => 'required|integer|min:0',
            'daftar_nama_berobatjalan' => 'nullable|array',
            'daftar_nama_rawatklinik' => 'nullable|array',
            'dokumen' => 'required|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('dokumen')) {
            $validated['dokumen'] = $request->file('dokumen')->store('perawatan_harian/dokumen', 'public');
        }

        DataPerawatanHarian::create($validated);

        return redirect()->route('data-perawatan-harians.index')->with('success', 'Data Perawatan Harian berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $dataperawatanharian = DataPerawatanHarian::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $dataperawatanharian->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/dataperawatanharian/Edit', [
            'dataperawatanharian' => $dataperawatanharian,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $dataperawatanharian = DataPerawatanHarian::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jumlah_penghuni_berobat' => 'required|integer|min:0',
            'jumlah_penghuni_dirawat' => 'required|integer|min:0',
            'jumlah_wbp_meninggal' => 'required|integer|min:0',
            'jumlah_nama_berobatjalan' => 'required|integer|min:0',
            'jumlah_nama_rawatklinik' => 'required|integer|min:0',
            'daftar_nama_berobatjalan' => 'nullable|array',
            'daftar_nama_rawatklinik' => 'nullable|array',
            'dokumen' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        unset($validated['dokumen']);

        if ($request->hasFile('dokumen')) {
            if ($dataperawatanharian->dokumen) Storage::disk('public')->delete($dataperawatanharian->dokumen);
            $validated['dokumen'] = $request->file('dokumen')->store('perawatan_harian/dokumen', 'public');
        }

        $dataperawatanharian->update($validated);

        return redirect()->route('data-perawatan-harians.index')->with('success', 'Data Perawatan Harian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dataperawatanharian = DataPerawatanHarian::findOrFail($id);
        if ($dataperawatanharian->dokumen) Storage::disk('public')->delete($dataperawatanharian->dokumen);
        $dataperawatanharian->delete();

        return redirect()->route('data-perawatan-harians.index')->with('success', 'Data Perawatan Harian berhasil dihapus.');
    }
}