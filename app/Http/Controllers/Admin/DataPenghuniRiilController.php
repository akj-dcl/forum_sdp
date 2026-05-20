<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPenghuniRiil;
use App\Models\Upt;
use App\Models\JenisPengeluaran;
use Inertia\Inertia;

class DataPenghuniRiilController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataPenghuniRiil::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_input', $request->tanggal);

        $datapenghuniriils = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datapenghuniriil/Index', [
            'datapenghuniriils' => $datapenghuniriils,
            'upts' => $upts,
            'jenis_pengeluarans' => JenisPengeluaran::all(), // Kirim untuk Modal Detail
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        return Inertia::render('admin/pengamanan/datapenghuniriil/Create', [
            'upts' => $upts,
            'jenis_pengeluarans' => JenisPengeluaran::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'nama_regu' => 'required|string|max:255',
            'jumlah_regu' => 'required|integer|min:0',
            'jumlah_hadir' => 'required|integer|min:0',
            'jumlah_tidak_hadir' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
            'perwira_piket' => 'nullable|string|max:255',
            'jumlah_bantuan_piket' => 'required|integer|min:0',
            'pengawalan_rs' => 'nullable|string|max:255',
            'patroli_sambang' => 'nullable|string|max:255',
            'waktu_patroli_sambang' => 'nullable|string|max:255',
            'jumlah_kapasitas' => 'required|integer|min:0',
            'jumlah_napi_laki' => 'required|integer|min:0',
            'jumlah_napi_perempuan' => 'required|integer|min:0',
            'jumlah_napi_anak' => 'required|integer|min:0',
            'jumlah_tahanan_laki' => 'required|integer|min:0',
            'jumlah_tahanan_perempuan' => 'required|integer|min:0',
            'jumlah_tahanan_anak' => 'required|integer|min:0',
            'total_isi' => 'required|integer|min:0',
            'overcrowded' => 'required|numeric',
            'jumlah_pengeluaran' => 'nullable|array',
        ]);

        DataPenghuniRiil::create($validated);
        return redirect()->route('data-penghuni-riils.index')->with('success', 'Data Penghuni Riil berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datapenghuniriil = DataPenghuniRiil::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datapenghuniriil->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datapenghuniriil/Edit', [
            'datapenghuniriil' => $datapenghuniriil,
            'upts' => $upts,
            'jenis_pengeluarans' => JenisPengeluaran::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $datapenghuniriil = DataPenghuniRiil::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'nama_regu' => 'required|string|max:255',
            'jumlah_regu' => 'required|integer|min:0',
            'jumlah_hadir' => 'required|integer|min:0',
            'jumlah_tidak_hadir' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
            'perwira_piket' => 'nullable|string|max:255',
            'jumlah_bantuan_piket' => 'required|integer|min:0',
            'pengawalan_rs' => 'nullable|string|max:255',
            'patroli_sambang' => 'nullable|string|max:255',
            'waktu_patroli_sambang' => 'nullable|string|max:255',
            'jumlah_kapasitas' => 'required|integer|min:0',
            'jumlah_napi_laki' => 'required|integer|min:0',
            'jumlah_napi_perempuan' => 'required|integer|min:0',
            'jumlah_napi_anak' => 'required|integer|min:0',
            'jumlah_tahanan_laki' => 'required|integer|min:0',
            'jumlah_tahanan_perempuan' => 'required|integer|min:0',
            'jumlah_tahanan_anak' => 'required|integer|min:0',
            'total_isi' => 'required|integer|min:0',
            'overcrowded' => 'required|numeric',
            'jumlah_pengeluaran' => 'nullable|array',
        ]);

        $datapenghuniriil->update($validated);
        return redirect()->route('data-penghuni-riils.index')->with('success', 'Data Penghuni Riil berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DataPenghuniRiil::findOrFail($id)->delete();
        return redirect()->route('data-penghuni-riils.index')->with('success', 'Data Penghuni Riil berhasil dihapus.');
    }
}