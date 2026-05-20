<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataInformasiIntelejen;
use App\Models\Upt;
use Inertia\Inertia;

class DataInformasiIntelejenController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataInformasiIntelejen::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_pelaksanaan', $request->tanggal);

        $datainformasiintelejens = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datainformasiintelejen/Index', [
            'datainformasiintelejens' => $datainformasiintelejens,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/pengamanan/datainformasiintelejen/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'tanggal_pelaksanaan' => 'nullable|date',
            'waktu_pelaksanaan' => 'nullable',
            'narasumber' => 'nullable|string|max:255',
            'data_yang_masuk' => 'nullable|string',
            'data_dukung' => 'nullable|string',
            'potensi_gangguan' => 'nullable|string',
            'rekomendasi_antisipasi' => 'nullable|string',
            'tindak_lanjut_rekomendasi' => 'nullable|string',
        ]);

        DataInformasiIntelejen::create($validated);

        return redirect()->route('data-informasi-intelejens.index')->with('success', 'Data Informasi Intelijen berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datainformasiintelejen = DataInformasiIntelejen::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datainformasiintelejen->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datainformasiintelejen/Edit', [
            'datainformasiintelejen' => $datainformasiintelejen,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $datainformasiintelejen = DataInformasiIntelejen::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'tanggal_pelaksanaan' => 'nullable|date',
            'waktu_pelaksanaan' => 'nullable',
            'narasumber' => 'nullable|string|max:255',
            'data_yang_masuk' => 'nullable|string',
            'data_dukung' => 'nullable|string',
            'potensi_gangguan' => 'nullable|string',
            'rekomendasi_antisipasi' => 'nullable|string',
            'tindak_lanjut_rekomendasi' => 'nullable|string',
        ]);

        $datainformasiintelejen->update($validated);

        return redirect()->route('data-informasi-intelejens.index')->with('success', 'Data Informasi Intelijen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DataInformasiIntelejen::findOrFail($id)->delete();
        return redirect()->route('data-informasi-intelejens.index')->with('success', 'Data Informasi Intelijen berhasil dihapus.');
    }
}