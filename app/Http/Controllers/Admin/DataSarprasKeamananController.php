<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataSarprasKeamanan;
use App\Models\Upt;
use Inertia\Inertia;

class DataSarprasKeamananController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataSarprasKeamanan::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_input', $request->tanggal);

        $datasarpraskeamanans = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datasarpraskeamanan/Index', [
            'datasarpraskeamanans' => $datasarpraskeamanans,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/pengamanan/datasarpraskeamanan/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jumlah_senjata_api_baik' => 'required|integer|min:0',
            'jumlah_senjata_api_rusak' => 'required|integer|min:0',
            'jumlah_amunisi' => 'required|integer|min:0',
            'jumlah_xray' => 'required|integer|min:0',
            'jumlah_body_scanner' => 'required|integer|min:0',
            'jumlah_phh' => 'required|integer|min:0',
            'jumlah_borgol' => 'required|integer|min:0',
            'jumlah_metal_detector' => 'required|integer|min:0',
            'jumlah_cctv' => 'required|integer|min:0',
            'jumlah_apar' => 'required|integer|min:0',
            'jumlah_lonceng' => 'required|integer|min:0',
            'jumlah_ht' => 'required|integer|min:0',
        ]);

        DataSarprasKeamanan::create($validated);

        return redirect()->route('data-sarpras-keamanans.index')->with('success', 'Data Sarana & Prasarana Keamanan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datasarpraskeamanan = DataSarprasKeamanan::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datasarpraskeamanan->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datasarpraskeamanan/Edit', [
            'datasarpraskeamanan' => $datasarpraskeamanan,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $datasarpraskeamanan = DataSarprasKeamanan::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'jumlah_senjata_api_baik' => 'required|integer|min:0',
            'jumlah_senjata_api_rusak' => 'required|integer|min:0',
            'jumlah_amunisi' => 'required|integer|min:0',
            'jumlah_xray' => 'required|integer|min:0',
            'jumlah_body_scanner' => 'required|integer|min:0',
            'jumlah_phh' => 'required|integer|min:0',
            'jumlah_borgol' => 'required|integer|min:0',
            'jumlah_metal_detector' => 'required|integer|min:0',
            'jumlah_cctv' => 'required|integer|min:0',
            'jumlah_apar' => 'required|integer|min:0',
            'jumlah_lonceng' => 'required|integer|min:0',
            'jumlah_ht' => 'required|integer|min:0',
        ]);

        $datasarpraskeamanan->update($validated);

        return redirect()->route('data-sarpras-keamanans.index')->with('success', 'Data Sarana & Prasarana Keamanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DataSarprasKeamanan::findOrFail($id)->delete();
        return redirect()->route('data-sarpras-keamanans.index')->with('success', 'Data Sarana & Prasarana Keamanan berhasil dihapus.');
    }
}