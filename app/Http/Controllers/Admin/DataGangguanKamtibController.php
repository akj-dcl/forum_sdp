<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataGangguanKamtib;
use App\Models\Upt;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DataGangguanKamtibController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataGangguanKamtib::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_kejadian', $request->tanggal);

        $datagangguankamtibs = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datagangguankamtib/Index', [
            'datagangguankamtibs' => $datagangguankamtibs,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/pengamanan/datagangguankamtib/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jenis_gangguan' => 'required|string|max:255',
            'uraian_singkat' => 'required|string',
            'tanggal_kejadian' => 'required|date',
            'waktu_kejadian' => 'required',
            'jumlah_korban' => 'required|integer|min:0',
            'detail_korban' => 'nullable|array',
            'jumlah_barbuk' => 'required|integer|min:0',
            'detail_barbuk' => 'nullable|array',
            'area_dirusak' => 'nullable|string|max:255',
            'pengamanan_tkp' => 'nullable|string',
            'upaya_dilakukan' => 'nullable|string',
            'permohonan_bantuan' => 'nullable|string',
            'dokumentasi' => 'required|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('dokumentasi')) {
            $validated['dokumentasi'] = $request->file('dokumentasi')->store('gangguan_kamtib/dokumentasi', 'public');
        }

        DataGangguanKamtib::create($validated);

        return redirect()->route('data-gangguan-kamtibs.index')->with('success', 'Laporan Gangguan Kamtib berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datagangguankamtib = DataGangguanKamtib::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datagangguankamtib->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datagangguankamtib/Edit', [
            'datagangguankamtib' => $datagangguankamtib,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $datagangguankamtib = DataGangguanKamtib::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jenis_gangguan' => 'required|string|max:255',
            'uraian_singkat' => 'required|string',
            'tanggal_kejadian' => 'required|date',
            'waktu_kejadian' => 'required',
            'jumlah_korban' => 'required|integer|min:0',
            'detail_korban' => 'nullable|array',
            'jumlah_barbuk' => 'required|integer|min:0',
            'detail_barbuk' => 'nullable|array',
            'area_dirusak' => 'nullable|string|max:255',
            'pengamanan_tkp' => 'nullable|string',
            'upaya_dilakukan' => 'nullable|string',
            'permohonan_bantuan' => 'nullable|string',
            'dokumentasi' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        unset($validated['dokumentasi']); // Agar tidak menimpa jadi null

        if ($request->hasFile('dokumentasi')) {
            if ($datagangguankamtib->dokumentasi) Storage::disk('public')->delete($datagangguankamtib->dokumentasi);
            $validated['dokumentasi'] = $request->file('dokumentasi')->store('gangguan_kamtib/dokumentasi', 'public');
        }

        $datagangguankamtib->update($validated);

        return redirect()->route('data-gangguan-kamtibs.index')->with('success', 'Laporan Gangguan Kamtib berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $datagangguankamtib = DataGangguanKamtib::findOrFail($id);
        if ($datagangguankamtib->dokumentasi) Storage::disk('public')->delete($datagangguankamtib->dokumentasi);
        $datagangguankamtib->delete();

        return redirect()->route('data-gangguan-kamtibs.index')->with('success', 'Laporan Gangguan Kamtib berhasil dihapus.');
    }
}