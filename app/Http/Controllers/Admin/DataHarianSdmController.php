<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataHarianSdm;
use App\Models\Upt;
use Inertia\Inertia;

class DataHarianSdmController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataHarianSdm::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_input', $request->tanggal);

        $datahariansdms = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        // Perhatikan foldernya: tudanumum
        return Inertia::render('admin/tudanumum/datahariansdm/Index', [
            'datahariansdms' => $datahariansdms,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/tudanumum/datahariansdm/Create', [
            'upts' => $upts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jumlah_pegawai_keseluruhan' => 'required|integer|min:0',
            'kehadiran_pegawai' => 'nullable|array',
            'pelanggaran_sdm' => 'nullable|string',
        ]);

        DataHarianSdm::create($validated);

        return redirect()->route('data-harian-sdms.index')->with('success', 'Data Harian SDM berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datahariansdm = DataHarianSdm::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datahariansdm->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/datahariansdm/Edit', [
            'datahariansdm' => $datahariansdm,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $datahariansdm = DataHarianSdm::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jumlah_pegawai_keseluruhan' => 'required|integer|min:0',
            'kehadiran_pegawai' => 'nullable|array',
            'pelanggaran_sdm' => 'nullable|string',
        ]);

        $datahariansdm->update($validated);

        return redirect()->route('data-harian-sdms.index')->with('success', 'Data Harian SDM berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DataHarianSdm::findOrFail($id)->delete();
        return redirect()->route('data-harian-sdms.index')->with('success', 'Data Harian SDM berhasil dihapus.');
    }
}