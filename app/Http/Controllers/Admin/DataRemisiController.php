<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataRemisi;
use Inertia\Inertia;
use App\Models\Upt;
use App\Models\JenisRemisi;
use Illuminate\Support\Facades\Storage;

class DataRemisiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 

        $query = DataRemisi::with(['upt']);

        if ($user->upt_id) {
            $query->where('upt_id', $user->upt_id);
        } elseif ($request->upt_id) {
            $query->where('upt_id', $request->upt_id);
        }

        if ($request->tanggal) {
            $query->whereDate('tanggal_input', $request->tanggal);
        }

        $dataremisis = $query->latest()->paginate(10)->withQueryString();

        $upts = $user->upt_id 
            ? Upt::where('id', $user->upt_id)->get() 
            : Upt::where('is_active', true)->get();
            
        $jenis_remisis = JenisRemisi::all();

        return Inertia::render('admin/pembinaan/dataremisi/Index', [
            'dataremisis' => $dataremisis,
            'upts' => $upts,
            'jenis_remisis' => $jenis_remisis,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        $jenis_remisis = JenisRemisi::all();

        return Inertia::render('admin/pembinaan/dataremisi/Create', [
            'upts' => $upts,
            'jenis_remisis' => $jenis_remisis
        ]);
    }

    public function store(Request $request)
    {
        set_time_limit(300); // 🛠️ Perpanjang nafas server karena pakai Google Drive

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'detail_remisi' => 'required|array|min:1',
            'detail_remisi.*.jenis_remisi_id' => 'required',
            'detail_remisi.*.jumlah_diusulkan' => 'required|integer|min:0',
            'detail_remisi.*.jumlah_remisi' => 'required|integer|min:0',
            'detail_remisi.*.jumlah_selisih' => 'required|string',
            'detail_remisi.*.keterangan' => 'nullable|string',
            'detail_remisi.*.sk_remisi' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        foreach ($validated['detail_remisi'] as $index => $item) {
            if ($request->hasFile("detail_remisi.$index.sk_remisi")) {
                $file = $request->file("detail_remisi.$index.sk_remisi");
                // 🛠️ UBAH KE GOOGLE DRIVE
                $path = $file->store('remisi/sk', 'google');
                $validated['detail_remisi'][$index]['sk_remisi'] = $path;
            }
        }

        DataRemisi::create($validated);

        return redirect()->route('data-remisis.index')->with('success', 'Laporan Remisi Harian berhasil disimpan ke Google Drive.');
    }

    public function edit($id)
    {
        $dataremisi = DataRemisi::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $dataremisi->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        $jenis_remisis = JenisRemisi::all();

        return Inertia::render('admin/pembinaan/dataremisi/Edit', [
            'dataremisi' => $dataremisi,
            'upts' => $upts,
            'jenis_remisis' => $jenis_remisis
        ]);
    }

    public function update(Request $request, $id)
    {
        set_time_limit(300); // 🛠️ Perpanjang nafas server

        $dataremisi = DataRemisi::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'detail_remisi' => 'required|array|min:1',
            'detail_remisi.*.jenis_remisi_id' => 'required',
            'detail_remisi.*.jumlah_diusulkan' => 'required|integer|min:0',
            'detail_remisi.*.jumlah_remisi' => 'required|integer|min:0',
            'detail_remisi.*.jumlah_selisih' => 'required|string',
            'detail_remisi.*.keterangan' => 'nullable|string',
            'detail_remisi.*.sk_remisi' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        foreach ($validated['detail_remisi'] as $index => $item) {
            $validated['detail_remisi'][$index]['sk_remisi'] = $dataremisi->detail_remisi[$index]['sk_remisi'] ?? null;

            if ($request->hasFile("detail_remisi.$index.sk_remisi")) {
                $oldFile = $dataremisi->detail_remisi[$index]['sk_remisi'] ?? null;

                // 🛠️ HAPUS FILE LAMA DI GOOGLE DRIVE
                if ($oldFile && Storage::disk('google')->exists($oldFile)) {
                    Storage::disk('google')->delete($oldFile);
                }

                $file = $request->file("detail_remisi.$index.sk_remisi");
                // 🛠️ UPLOAD FILE BARU KE GOOGLE DRIVE
                $path = $file->store('remisi/sk', 'google');
                $validated['detail_remisi'][$index]['sk_remisi'] = $path;
            }
        }

        $dataremisi->update($validated);

        return redirect()->route('data-remisis.index')->with('success', 'Laporan Remisi Harian berhasil diperbarui di Google Drive.');
    }

    public function destroy($id)
    {
        $dataremisi = DataRemisi::findOrFail($id);

        foreach ($dataremisi->detail_remisi as $item) {
            // 🛠️ HAPUS FILE DARI GOOGLE DRIVE SAAT DATA DIHAPUS
            if (!empty($item['sk_remisi']) && Storage::disk('google')->exists($item['sk_remisi'])) {
                Storage::disk('google')->delete($item['sk_remisi']);
            }
        }

        $dataremisi->delete();

        return redirect()->route('data-remisis.index')->with('success', 'Laporan Remisi Harian dan filenya berhasil dihapus.');
    }
}