<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataReguPengamanan;
use App\Models\Upt;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DataReguPengamananController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataReguPengamanan::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_input', $request->tanggal);

        $dataregupengamanans = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/dataregupengamanan/Index', [
            'dataregupengamanans' => $dataregupengamanans,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        return Inertia::render('admin/pengamanan/dataregupengamanan/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'shift_rupam' => 'required|string',
            'nama_regu' => 'required|string|max:255',
            'waktu_tugas_mulai' => 'required',
            'waktu_tugas_selesai' => 'required',
            'jumlah_regu_jaga' => 'required|integer|min:0',
            'jumlah_hadir' => 'required|integer|min:0',
            'jumlah_tidak_hadir' => 'required|integer|min:0',
            'nama_komandan_jaga' => 'nullable|string|max:255',
            'nama_wadan_jaga' => 'nullable|string|max:255',
            'nama_p2u' => 'nullable|string|max:255',
            'nama_petugas_penjagaan' => 'nullable|string|max:255',
            'jumlah_petugas_luar' => 'required|integer|min:0',
            'jenis_bantuan' => 'nullable|string',
            'jumlah_bantuan' => 'required|integer|min:0',
            'jumlah_titik_pos' => 'required|integer|min:0',
            'pos_pengamanan_diisi' => 'nullable|string',
            'pos_pengamanan_tidak_diisi' => 'nullable|string',
            'dokumentasi' => 'required|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('dokumentasi')) {
            $validated['dokumentasi'] = $request->file('dokumentasi')->store('regu_pengamanan/dokumentasi', 'public');
        }

        DataReguPengamanan::create($validated);
        return redirect()->route('data-regu-pengamanans.index')->with('success', 'Data Regu Pengamanan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $dataregupengamanan = DataReguPengamanan::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $dataregupengamanan->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/dataregupengamanan/Edit', [
            'dataregupengamanan' => $dataregupengamanan,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $dataregupengamanan = DataReguPengamanan::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'shift_rupam' => 'required|string',
            'nama_regu' => 'required|string|max:255',
            'waktu_tugas_mulai' => 'required',
            'waktu_tugas_selesai' => 'required',
            'jumlah_regu_jaga' => 'required|integer|min:0',
            'jumlah_hadir' => 'required|integer|min:0',
            'jumlah_tidak_hadir' => 'required|integer|min:0',
            'nama_komandan_jaga' => 'nullable|string|max:255',
            'nama_wadan_jaga' => 'nullable|string|max:255',
            'nama_p2u' => 'nullable|string|max:255',
            'nama_petugas_penjagaan' => 'nullable|string|max:255',
            'jumlah_petugas_luar' => 'required|integer|min:0',
            'jenis_bantuan' => 'nullable|string',
            'jumlah_bantuan' => 'required|integer|min:0',
            'jumlah_titik_pos' => 'required|integer|min:0',
            'pos_pengamanan_diisi' => 'nullable|string',
            'pos_pengamanan_tidak_diisi' => 'nullable|string',
            'dokumentasi' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        unset($validated['dokumentasi']);

        if ($request->hasFile('dokumentasi')) {
            if ($dataregupengamanan->dokumentasi) Storage::disk('public')->delete($dataregupengamanan->dokumentasi);
            $validated['dokumentasi'] = $request->file('dokumentasi')->store('regu_pengamanan/dokumentasi', 'public');
        }

        $dataregupengamanan->update($validated);
        return redirect()->route('data-regu-pengamanans.index')->with('success', 'Data Regu Pengamanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dataregupengamanan = DataReguPengamanan::findOrFail($id);
        if ($dataregupengamanan->dokumentasi) Storage::disk('public')->delete($dataregupengamanan->dokumentasi);
        $dataregupengamanan->delete();

        return redirect()->route('data-regu-pengamanans.index')->with('success', 'Data Regu Pengamanan berhasil dihapus.');
    }
}