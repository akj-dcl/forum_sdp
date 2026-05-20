<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPerawatanBulanan;
use App\Models\Upt;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DataPerawatanBulananController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataPerawatanBulanan::with(['upt']);

        if ($user->upt_id) {
            $query->where('upt_id', $user->upt_id);
        } elseif ($request->upt_id) {
            $query->where('upt_id', $request->upt_id);
        }

        if ($request->tanggal) {
            $query->whereDate('tanggal_input', $request->tanggal);
        }

        $dataperawatanbulanans = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/dataperawatanbulanan/Index', [
            'dataperawatanbulanans' => $dataperawatanbulanans,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        return Inertia::render('admin/pengamanan/dataperawatanbulanan/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jumlah_penghuni_hiv' => 'required|integer|min:0',
            'jumlah_penghuni_tbc' => 'required|integer|min:0',
            'jumlah_penghuni_disabilitas' => 'required|integer|min:0',
            'jumlah_penghuni_jiwa' => 'required|integer|min:0',
            'jumlah_penghuni_tidakmenular' => 'required|integer|min:0',
            'jumlah_penghuni_menular' => 'required|integer|min:0',
            'jenis_penyakit_dominan' => 'required|string|max:255',
            'jumlah_wbp_lansia' => 'required|integer|min:0',
            'jumlah_wbp_berkepanjangan' => 'required|integer|min:0',
            'jumlah_peserta_bpjs' => 'required|integer|min:0',
            'laporan' => 'required|file|mimes:pdf|max:5120', // PDF max 5MB
        ]);

        if ($request->hasFile('laporan')) {
            $validated['laporan'] = $request->file('laporan')->store('perawatan_bulanan/laporan', 'public');
        }

        DataPerawatanBulanan::create($validated);

        return redirect()->route('data-perawatan-bulanans.index')->with('success', 'Data Perawatan Bulanan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $dataperawatanbulanan = DataPerawatanBulanan::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $dataperawatanbulanan->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/dataperawatanbulanan/Edit', [
            'dataperawatanbulanan' => $dataperawatanbulanan,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $dataperawatanbulanan = DataPerawatanBulanan::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jumlah_penghuni_hiv' => 'required|integer|min:0',
            'jumlah_penghuni_tbc' => 'required|integer|min:0',
            'jumlah_penghuni_disabilitas' => 'required|integer|min:0',
            'jumlah_penghuni_jiwa' => 'required|integer|min:0',
            'jumlah_penghuni_tidakmenular' => 'required|integer|min:0',
            'jumlah_penghuni_menular' => 'required|integer|min:0',
            'jenis_penyakit_dominan' => 'required|string|max:255',
            'jumlah_wbp_lansia' => 'required|integer|min:0',
            'jumlah_wbp_berkepanjangan' => 'required|integer|min:0',
            'jumlah_peserta_bpjs' => 'required|integer|min:0',
            'laporan' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        unset($validated['laporan']); // Cegah overwrite null

        if ($request->hasFile('laporan')) {
            if ($dataperawatanbulanan->laporan) Storage::disk('public')->delete($dataperawatanbulanan->laporan);
            $validated['laporan'] = $request->file('laporan')->store('perawatan_bulanan/laporan', 'public');
        }

        $dataperawatanbulanan->update($validated);

        return redirect()->route('data-perawatan-bulanans.index')->with('success', 'Data Perawatan Bulanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dataperawatanbulanan = DataPerawatanBulanan::findOrFail($id);
        if ($dataperawatanbulanan->laporan) Storage::disk('public')->delete($dataperawatanbulanan->laporan);
        $dataperawatanbulanan->delete();

        return redirect()->route('data-perawatan-bulanans.index')->with('success', 'Data Perawatan Bulanan berhasil dihapus.');
    }
}