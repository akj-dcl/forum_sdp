<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPublikasiSdm;
use App\Models\Upt;
use Inertia\Inertia;

class DataPublikasiSdmController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataPublikasiSdm::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        // Filter berdasarkan tanggal publikasi (bukan tanggal input)
        if ($request->tanggal) $query->whereDate('tanggal_publikasi', $request->tanggal);

        $datapublikasisdms = $query->latest('tanggal_publikasi')->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/datapublikasisdm/Index', [
            'datapublikasisdms' => $datapublikasisdms,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/tudanumum/datapublikasisdm/Create', [
            'upts' => $upts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal_publikasi' => 'required|date',
            'uraian_singkat' => 'required|string',
            'media_publikasi' => 'required|string|max:255',
            'link_berita' => 'nullable|string', // Nullable karena kadang linknya menyusul
        ]);

        DataPublikasiSdm::create($validated);

        return redirect()->route('data-publikasi-sdms.index')->with('success', 'Data Publikasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datapublikasisdm = DataPublikasiSdm::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datapublikasisdm->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/datapublikasisdm/Edit', [
            'datapublikasisdm' => $datapublikasisdm,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $datapublikasisdm = DataPublikasiSdm::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal_publikasi' => 'required|date',
            'uraian_singkat' => 'required|string',
            'media_publikasi' => 'required|string|max:255',
            'link_berita' => 'nullable|string',
        ]);

        $datapublikasisdm->update($validated);

        return redirect()->route('data-publikasi-sdms.index')->with('success', 'Data Publikasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DataPublikasiSdm::findOrFail($id)->delete();
        return redirect()->route('data-publikasi-sdms.index')->with('success', 'Data Publikasi berhasil dihapus.');
    }
}