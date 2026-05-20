<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataBulananSdm;
use App\Models\Upt;
use Inertia\Inertia;

class DataBulananSdmController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataBulananSdm::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_input', $request->tanggal);

        $databulanansdms = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/databulanansdm/Index', [
            'databulanansdms' => $databulanansdms,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/tudanumum/databulanansdm/Create', [
            'upts' => $upts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jumlah_pegawai' => 'required|integer|min:0',
            'jumlah_pejabat_struktural' => 'required|integer|min:0',
            'jumlah_jf' => 'required|integer|min:0',
            'jumlah_petugas_penjagaan' => 'required|integer|min:0',
            'jumlah_staff_pembinaan' => 'required|integer|min:0',
            'jumlah_staff' => 'required|integer|min:0',
        ]);

        DataBulananSdm::create($validated);

        return redirect()->route('data-bulanan-sdms.index')->with('success', 'Data Bulanan SDM berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $databulanansdm = DataBulananSdm::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $databulanansdm->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/databulanansdm/Edit', [
            'databulanansdm' => $databulanansdm,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $databulanansdm = DataBulananSdm::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jumlah_pegawai' => 'required|integer|min:0',
            'jumlah_pejabat_struktural' => 'required|integer|min:0',
            'jumlah_jf' => 'required|integer|min:0',
            'jumlah_petugas_penjagaan' => 'required|integer|min:0',
            'jumlah_staff_pembinaan' => 'required|integer|min:0',
            'jumlah_staff' => 'required|integer|min:0',
        ]);

        $databulanansdm->update($validated);

        return redirect()->route('data-bulanan-sdms.index')->with('success', 'Data Bulanan SDM berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DataBulananSdm::findOrFail($id)->delete();
        return redirect()->route('data-bulanan-sdms.index')->with('success', 'Data Bulanan SDM berhasil dihapus.');
    }
}