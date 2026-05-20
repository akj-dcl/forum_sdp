<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataResidivis;
use Inertia\Inertia;
use App\Models\Upt;
use App\Models\JenisPidana;
use Illuminate\Support\Facades\Storage;

class DataResidivisController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataResidivis::with(['upt', 'jenis_pidana_sekarang', 'jenis_pidana_sebelumnya']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        else if ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->search) $query->where('nama', 'like', "%{$request->search}%");
        if ($request->tanggal) $query->whereDate('tanggal', $request->tanggal);

        $dataresidivises = $query->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataresidivis/Index', [
            'dataresidivises' => $dataresidivises,
            'upts' => $upts,
            'filters' => $request->only(['search', 'upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataresidivis/Create', [
            'upts' => $upts,
            'jenis_pidanas' => JenisPidana::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal'=> 'required|date',
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'jenis_pidana_sekarang_id' => 'required|exists:jenis_pidanas,id',
            'lama_pidana_sekarang' => 'required|integer',
            'tempat_pidana_sekarang' => 'required|string|max:255',
            'berapa_kali_dipidana' => 'required|integer|min:1',
            'putusan_pengadilan' => 'nullable|file|mimes:pdf|max:5120',
            'jenis_pidana_sebelumnya_id' => 'required|exists:jenis_pidanas,id',
            'lama_pidana_sebelumnya' => 'required|integer',
            'tempat_pidana_sebelumnya' => 'required|string|max:255',
            'jenis_pembebasan_sebelumnya' => 'required|string|max:255',
        ]);

        if ($request->hasFile('putusan_pengadilan')) {
            $validated['putusan_pengadilan'] = $request->file('putusan_pengadilan')->store('residivis/putusan', 'public');
        }

        DataResidivis::create($validated);
        return redirect()->route('data-residivises.index')->with('success', 'Data Residivis berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $dataresidivis = DataResidivis::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $dataresidivis->upt_id !== $user->upt_id) {
            abort(403, 'Akses Ditolak! Anda tidak dapat mengedit data Lapas lain.');
        }

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataresidivis/Edit', [
            'dataresidivis' => $dataresidivis,
            'upts' => $upts,
            'jenis_pidanas' => JenisPidana::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $dataresidivis = DataResidivis::findOrFail($id);
        
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal'=> 'required|date',
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'jenis_pidana_sekarang_id' => 'required|exists:jenis_pidanas,id',
            'lama_pidana_sekarang' => 'required|integer',
            'tempat_pidana_sekarang' => 'required|string|max:255',
            'berapa_kali_dipidana' => 'required|integer|min:1',
            'putusan_pengadilan' => 'nullable|file|mimes:pdf|max:5120',
            'jenis_pidana_sebelumnya_id' => 'required|exists:jenis_pidanas,id',
            'lama_pidana_sebelumnya' => 'required|integer',
            'tempat_pidana_sebelumnya' => 'required|string|max:255',
            'jenis_pembebasan_sebelumnya' => 'required|string|max:255',
        ]);

        if ($request->hasFile('putusan_pengadilan')) {
            if ($dataresidivis->putusan_pengadilan) {
                Storage::disk('public')->delete($dataresidivis->putusan_pengadilan);
            }
            $validated['putusan_pengadilan'] = $request->file('putusan_pengadilan')->store('residivis/putusan', 'public');
        } else {
            unset($validated['putusan_pengadilan']); // Jangan hapus path lama jika tidak ada file baru
        }

        $dataresidivis->update($validated);
        return redirect()->route('data-residivises.index')->with('success', 'Data Residivis berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dataresidivis = DataResidivis::findOrFail($id);
        if ($dataresidivis->putusan_pengadilan) {
            Storage::disk('public')->delete($dataresidivis->putusan_pengadilan);
        }
        $dataresidivis->delete();
        
        return redirect()->route('data-residivises.index')->with('success', 'Data Residivis berhasil dihapus.');
    }
}