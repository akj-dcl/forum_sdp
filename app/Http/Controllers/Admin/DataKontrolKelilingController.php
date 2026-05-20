<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataKontrolKeliling;
use App\Models\Upt;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DataKontrolKelilingController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataKontrolKeliling::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_input', $request->tanggal);

        $datakontrolkelilings = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datakontrolkeliling/Index', [
            'datakontrolkelilings' => $datakontrolkelilings,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/pengamanan/datakontrolkeliling/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'waktu_kontrol' => 'nullable|string',
            'nama_petugas_kontrol' => 'required|string|max:255',
            'area_kontrol' => 'nullable|string|max:255',
            'hasil_kontrol' => 'nullable|string|max:255',
            'tindak_lanjut' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
            'dokumentasi_kontrol' => 'required|file|mimes:pdf|max:5120', // Wajib PDF maks 5MB
        ]);

        if ($request->hasFile('dokumentasi_kontrol')) {
            $validated['dokumentasi_kontrol'] = $request->file('dokumentasi_kontrol')->store('kontrol_keliling/dokumentasi', 'public');
        }

        DataKontrolKeliling::create($validated);

        return redirect()->route('data-kontrol-kelilings.index')->with('success', 'Data Kontrol Keliling berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datakontrolkeliling = DataKontrolKeliling::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datakontrolkeliling->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datakontrolkeliling/Edit', [
            'datakontrolkeliling' => $datakontrolkeliling,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $datakontrolkeliling = DataKontrolKeliling::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'waktu_kontrol' => 'nullable|string',
            'nama_petugas_kontrol' => 'required|string|max:255',
            'area_kontrol' => 'nullable|string|max:255',
            'hasil_kontrol' => 'nullable|string|max:255',
            'tindak_lanjut' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
            'dokumentasi_kontrol' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        unset($validated['dokumentasi_kontrol']); // Cegah tertimpa null

        if ($request->hasFile('dokumentasi_kontrol')) {
            if ($datakontrolkeliling->dokumentasi_kontrol) Storage::disk('public')->delete($datakontrolkeliling->dokumentasi_kontrol);
            $validated['dokumentasi_kontrol'] = $request->file('dokumentasi_kontrol')->store('kontrol_keliling/dokumentasi', 'public');
        }

        $datakontrolkeliling->update($validated);

        return redirect()->route('data-kontrol-kelilings.index')->with('success', 'Data Kontrol Keliling berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $datakontrolkeliling = DataKontrolKeliling::findOrFail($id);
        if ($datakontrolkeliling->dokumentasi_kontrol) Storage::disk('public')->delete($datakontrolkeliling->dokumentasi_kontrol);
        $datakontrolkeliling->delete();

        return redirect()->route('data-kontrol-kelilings.index')->with('success', 'Data Kontrol Keliling berhasil dihapus.');
    }
}