<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataRealisasiAnggaran;
use App\Models\Upt;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DataRealisasiAnggaranController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataRealisasiAnggaran::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_input', $request->tanggal);

        $datarealisasianggarans = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/datarealisasianggaran/Index', [
            'datarealisasianggarans' => $datarealisasianggarans,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        return Inertia::render('admin/tudanumum/datarealisasianggaran/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'belanja_pegawai_pagu' => 'required|numeric|min:0',
            'belanja_pegawai_realisasi' => 'required|numeric|min:0',
            'belanja_barang_pagu' => 'required|numeric|min:0',
            'belanja_barang_realisasi' => 'required|numeric|min:0',
            'belanja_modal_pagu' => 'required|numeric|min:0',
            'belanja_modal_realisasi' => 'required|numeric|min:0',
            'belanja_listrik_pagu' => 'required|numeric|min:0',
            'belanja_listrik_realisasi' => 'required|numeric|min:0',
            'belanja_telpon_pagu' => 'required|numeric|min:0',
            'belanja_telpon_realisasi' => 'required|numeric|min:0',
            'belanja_internet_pagu' => 'required|numeric|min:0',
            'belanja_internet_realisasi' => 'required|numeric|min:0',
            'belanja_pos_pagu' => 'required|numeric|min:0',
            'belanja_pos_realisasi' => 'required|numeric|min:0',
            'bama_nomor_kontrak' => 'nullable|string|max:255',
            'bama_pagu_anggaran' => 'required|numeric|min:0',
            'bama_realisasi_anggaran' => 'required|numeric|min:0',
            'bama_nama_penyedia' => 'nullable|string|max:255',
            'bama_nomor_bast' => 'nullable|string|max:255',
            'bama_tanggal_bast' => 'nullable|date',
            'bama_berita_acara' => 'nullable|file|mimes:pdf|max:5120', // Opsional PDF
        ]);

        if ($request->hasFile('bama_berita_acara')) {
            $validated['bama_berita_acara'] = $request->file('bama_berita_acara')->store('keuangan/bast', 'public');
        }

        DataRealisasiAnggaran::create($validated);
        return redirect()->route('data-realisasi-anggarans.index')->with('success', 'Data Realisasi Anggaran berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datarealisasianggaran = DataRealisasiAnggaran::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datarealisasianggaran->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/datarealisasianggaran/Edit', [
            'datarealisasianggaran' => $datarealisasianggaran,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $datarealisasianggaran = DataRealisasiAnggaran::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'belanja_pegawai_pagu' => 'required|numeric|min:0',
            'belanja_pegawai_realisasi' => 'required|numeric|min:0',
            'belanja_barang_pagu' => 'required|numeric|min:0',
            'belanja_barang_realisasi' => 'required|numeric|min:0',
            'belanja_modal_pagu' => 'required|numeric|min:0',
            'belanja_modal_realisasi' => 'required|numeric|min:0',
            'belanja_listrik_pagu' => 'required|numeric|min:0',
            'belanja_listrik_realisasi' => 'required|numeric|min:0',
            'belanja_telpon_pagu' => 'required|numeric|min:0',
            'belanja_telpon_realisasi' => 'required|numeric|min:0',
            'belanja_internet_pagu' => 'required|numeric|min:0',
            'belanja_internet_realisasi' => 'required|numeric|min:0',
            'belanja_pos_pagu' => 'required|numeric|min:0',
            'belanja_pos_realisasi' => 'required|numeric|min:0',
            'bama_nomor_kontrak' => 'nullable|string|max:255',
            'bama_pagu_anggaran' => 'required|numeric|min:0',
            'bama_realisasi_anggaran' => 'required|numeric|min:0',
            'bama_nama_penyedia' => 'nullable|string|max:255',
            'bama_nomor_bast' => 'nullable|string|max:255',
            'bama_tanggal_bast' => 'nullable|date',
            'bama_berita_acara' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        unset($validated['bama_berita_acara']);

        if ($request->hasFile('bama_berita_acara')) {
            if ($datarealisasianggaran->bama_berita_acara) Storage::disk('public')->delete($datarealisasianggaran->bama_berita_acara);
            $validated['bama_berita_acara'] = $request->file('bama_berita_acara')->store('keuangan/bast', 'public');
        }

        $datarealisasianggaran->update($validated);
        return redirect()->route('data-realisasi-anggarans.index')->with('success', 'Data Realisasi Anggaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $datarealisasianggaran = DataRealisasiAnggaran::findOrFail($id);
        if ($datarealisasianggaran->bama_berita_acara) Storage::disk('public')->delete($datarealisasianggaran->bama_berita_acara);
        $datarealisasianggaran->delete();

        return redirect()->route('data-realisasi-anggarans.index')->with('success', 'Data Realisasi Anggaran berhasil dihapus.');
    }
}