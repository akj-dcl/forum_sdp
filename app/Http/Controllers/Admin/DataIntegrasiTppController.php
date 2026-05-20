<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataIntegrasiTpp;
use Inertia\Inertia;
use App\Models\Upt;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
    $validated = $request->validate([
    'upt_id' => 'required|exists:upts,id',
    'tanggal_input' => 'required|date',
    'tanggal_pelaksanaan' => 'required|date',
    'jumlah_narapidana_sidang' => 'required|integer|min:0', // Pastikan ini ada
    'nomor_sidang' => 'required|string|max:255',
    'rekomendasi_sidang' => 'required|string',
    'permasalahan' => 'required|string', // Pastikan nama kolomnya sama
    'upaya' => 'required|string',
    'berita_acara' => 'nullable|file|mimes:pdf|max:5120',
    'absensi' => 'nullable|file|mimes:pdf|max:5120',
    'dokumentasi_sidang' => 'nullable|array',
    'dokumentasi_sidang.*' => 'image|mimes:jpg,jpeg,png|max:5120',
    ]);

    // Handle PDF
    if ($request->hasFile('berita_acara')) $validated['berita_acara'] = $request->file('berita_acara')->store('tpp/berita_acara', 'public');
    if ($request->hasFile('absensi')) $validated['absensi'] = $request->file('absensi')->store('tpp/absensi', 'public');

    // Handle Multiple Images
    $images = [];
    foreach ($request->file('dokumentasi_sidang') as $file) {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('tpp/dokumentasi', $filename, 'public');
        $images[] = 'tpp/dokumentasi/' . $filename;
    }
    $validated['dokumentasi_sidang'] = $images;

    DataIntegrasiTpp::create($validated);
    return redirect()->route('data-integrasi-tpps.index')->with('success', 'Data Sidang TPP berhasil ditambahkan.');
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
        $dataintegrasitpp = DataIntegrasiTpp::findOrFail($id);

    $validated = $request->validate([
        'upt_id' => 'required|exists:upts,id',
        'tanggal_input' => 'required|date',
        'tanggal_pelaksanaan' => 'required|date',
        'jumlah_narapidana_sidang' => 'required|integer|min:0', // Field baru
        'nomor_sidang' => 'required|string|max:255',
        'rekomendasi_sidang' => 'required|string',
        'permasalahan' => 'required|string', // Ganti kendala
        'upaya' => 'required|string',
        'berita_acara' => 'nullable|file|mimes:pdf|max:5120',
        'absensi' => 'nullable|file|mimes:pdf|max:5120',
        'dokumentasi_sidang' => 'nullable|array', // Jadi nullable
        'dokumentasi_sidang.*' => 'image|mimes:jpg,jpeg,png|max:5120',
    ]);

        // 1. PENTING: Hapus field file dari $validated agar tidak menimpa DB jadi null
        unset($validated['berita_acara']);
        unset($validated['absensi']);
        unset($validated['dokumentasi_sidang']);

        // 2. Proses Update PDF Berita Acara (JIKA ADA FILE BARU)
        if ($request->hasFile('berita_acara')) {
            if ($dataintegrasitpp->berita_acara) Storage::disk('public')->delete($dataintegrasitpp->berita_acara);
            $validated['berita_acara'] = $request->file('berita_acara')->store('tpp/berita_acara', 'public');
        }

        // 3. Proses Update PDF Absensi (JIKA ADA FILE BARU)
        if ($request->hasFile('absensi')) {
            if ($dataintegrasitpp->absensi) Storage::disk('public')->delete($dataintegrasitpp->absensi);
            $validated['absensi'] = $request->file('absensi')->store('tpp/absensi', 'public');
        }

        // 4. Proses Update Foto Dokumentasi (JIKA ADA FILE BARU)
        if ($request->hasFile('dokumentasi_sidang')) {
            if ($dataintegrasitpp->dokumentasi_sidang) Storage::disk('public')->delete($dataintegrasitpp->dokumentasi_sidang);
            
            $file = $request->file('dokumentasi_sidang');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $folderPath = storage_path('app/public/tpp/dokumentasi');
            if (!file_exists($folderPath)) mkdir($folderPath, 0755, true);
            $path = $folderPath . '/' . $filename;
            
            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->decodePath($file->getPathname());
            $image->scale(width: 1200);
            $image->save($path, 75);
            
            $validated['dokumentasi_sidang'] = 'tpp/dokumentasi/' . $filename;
        }

        $dataintegrasitpp->update($validated);

        return redirect()->route('data-integrasi-tpps.index')->with('success', 'Data Sidang TPP berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dataintegrasitpp = DataIntegrasiTpp::findOrFail($id);
        
        if ($dataintegrasitpp->berita_acara) Storage::disk('public')->delete($dataintegrasitpp->berita_acara);
        if ($dataintegrasitpp->absensi) Storage::disk('public')->delete($dataintegrasitpp->absensi);
        if ($dataintegrasitpp->dokumentasi_sidang) Storage::disk('public')->delete($dataintegrasitpp->dokumentasi_sidang);

        $dataintegrasitpp->delete();
        
        return redirect()->route('data-integrasi-tpps.index')->with('success', 'Data Sidang TPP berhasil dihapus.');
    }
}