<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataIntegrasiTpp;
use Inertia\Inertia;
use App\Models\Upt;
use Illuminate\Support\Facades\Storage;

class DataIntegrasiTppController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 

        $query = DataIntegrasiTpp::with(['upt']);

        if ($user->upt_id) {
            $query->where('upt_id', $user->upt_id);
        } elseif ($request->upt_id) {
            $query->where('upt_id', $request->upt_id);
        }

        if ($request->search) {
            $query->where('nomor_sidang', 'like', "%{$request->search}%");
        }

        $dataintegrasitpps = $query->latest()->paginate(10)->withQueryString();

        $upts = $user->upt_id 
            ? Upt::where('id', $user->upt_id)->get() 
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataintegrasitpp/Index', [
            'dataintegrasitpps' => $dataintegrasitpps,
            'upts' => $upts,
            'filters' => $request->only(['search', 'upt_id'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataintegrasitpp/Create', [
            'upts' => $upts,
        ]);
    }

    public function store(Request $request)
    {
        set_time_limit(300); // 🛠️ TAMBAHAN: Mencegah server RTO saat upload banyak foto

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'tanggal_pelaksanaan' => 'required|date',
            'jumlah_narapidana_sidang' => 'required|integer|min:0',
            'nomor_sidang' => 'required|string|max:255',
            'rekomendasi_sidang' => 'required|string',
            'permasalahan' => 'required|string',
            'upaya' => 'required|string',
            'berita_acara' => 'required|file|mimes:pdf|max:5120',
            'absensi' => 'required|file|mimes:pdf|max:5120',
            'dokumentasi_sidang' => 'required|array',
            'dokumentasi_sidang.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('berita_acara')) {
            $validated['berita_acara'] = $request->file('berita_acara')->store('tpp/berita_acara', 'google');
        }
        if ($request->hasFile('absensi')) {
            $validated['absensi'] = $request->file('absensi')->store('tpp/absensi', 'google');
        }

        $images = [];
        if ($request->hasFile('dokumentasi_sidang')) {
            foreach ($request->file('dokumentasi_sidang') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('tpp/dokumentasi', $filename, 'google');
                $images[] = $path;
            }
        }
        $validated['dokumentasi_sidang'] = $images;

        DataIntegrasiTpp::create($validated);
        return redirect()->route('data-integrasi-tpps.index')->with('success', 'Data Sidang TPP berhasil disimpan ke Google Drive.');
    }

    public function edit($id)
    {
        $dataintegrasitpp = DataIntegrasiTpp::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $dataintegrasitpp->upt_id !== $user->upt_id) {
            abort(403, 'Akses Ditolak!');
        }

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataintegrasitpp/Edit', [
            'dataintegrasitpp' => $dataintegrasitpp,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        set_time_limit(300); // 🛠️ TAMBAHAN: Mencegah server RTO saat upload banyak foto

        $dataintegrasitpp = DataIntegrasiTpp::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'tanggal_pelaksanaan' => 'required|date',
            'jumlah_narapidana_sidang' => 'required|integer|min:0',
            'nomor_sidang' => 'required|string|max:255',
            'rekomendasi_sidang' => 'required|string',
            'permasalahan' => 'required|string',
            'upaya' => 'required|string',
            'berita_acara' => 'nullable|file|mimes:pdf|max:5120',
            'absensi' => 'nullable|file|mimes:pdf|max:5120',
            'dokumentasi_sidang' => 'nullable|array',
            'dokumentasi_sidang.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        unset($validated['berita_acara']);
        unset($validated['absensi']);
        unset($validated['dokumentasi_sidang']);

        if ($request->hasFile('berita_acara')) {
            if ($dataintegrasitpp->berita_acara && Storage::disk('google')->exists($dataintegrasitpp->berita_acara)) {
                Storage::disk('google')->delete($dataintegrasitpp->berita_acara);
            }
            $validated['berita_acara'] = $request->file('berita_acara')->store('tpp/berita_acara', 'google');
        }

        if ($request->hasFile('absensi')) {
            if ($dataintegrasitpp->absensi && Storage::disk('google')->exists($dataintegrasitpp->absensi)) {
                Storage::disk('google')->delete($dataintegrasitpp->absensi);
            }
            $validated['absensi'] = $request->file('absensi')->store('tpp/absensi', 'google');
        }

        if ($request->hasFile('dokumentasi_sidang')) {
            if (!empty($dataintegrasitpp->dokumentasi_sidang)) {
                foreach ($dataintegrasitpp->dokumentasi_sidang as $oldFile) {
                    if (Storage::disk('google')->exists($oldFile)) {
                        Storage::disk('google')->delete($oldFile);
                    }
                }
            }
            
            $images = [];
            foreach ($request->file('dokumentasi_sidang') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('tpp/dokumentasi', $filename, 'google');
                $images[] = $path;
            }
            $validated['dokumentasi_sidang'] = $images;
        }

        $dataintegrasitpp->update($validated);

        return redirect()->route('data-integrasi-tpps.index')->with('success', 'Data Sidang TPP berhasil diperbarui di Google Drive.');
    }

    public function destroy($id)
    {
        $dataintegrasitpp = DataIntegrasiTpp::findOrFail($id);
        
        if ($dataintegrasitpp->berita_acara && Storage::disk('google')->exists($dataintegrasitpp->berita_acara)) {
            Storage::disk('google')->delete($dataintegrasitpp->berita_acara);
        }
        if ($dataintegrasitpp->absensi && Storage::disk('google')->exists($dataintegrasitpp->absensi)) {
            Storage::disk('google')->delete($dataintegrasitpp->absensi);
        }
        
        if (!empty($dataintegrasitpp->dokumentasi_sidang)) {
            foreach ($dataintegrasitpp->dokumentasi_sidang as $file) {
                if (Storage::disk('google')->exists($file)) {
                    Storage::disk('google')->delete($file);
                }
            }
        }

        $dataintegrasitpp->delete();
        
        return redirect()->route('data-integrasi-tpps.index')->with('success', 'Data Sidang TPP dan file di Drive berhasil dihapus.');
    }
}