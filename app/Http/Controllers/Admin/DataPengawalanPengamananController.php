<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPengawalanPengamanan;
use App\Models\Upt;
use App\Models\JenisPengawalan;
use App\Models\JenisKlasifikasi;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DataPengawalanPengamananController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataPengawalanPengamanan::with(['upt', 'jenisPengawalan', 'jenisKlasifikasi']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_pelaksanaan', $request->tanggal);

        $datapengawalanpengamanans = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datapengawalanpengamanan/Index', [
            'datapengawalanpengamanans' => $datapengawalanpengamanans,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();
        
        return Inertia::render('admin/pengamanan/datapengawalanpengamanan/Create', [
            'upts' => $upts,
            'jenis_pengawalans' => JenisPengawalan::all(),
            'jenis_klasifikasis' => JenisKlasifikasi::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'tanggal_pelaksanaan' => 'required|date',
            'waktu_pelaksanaan_mulai' => 'required',
            'waktu_pelaksanaan_selesai' => 'required',
            'jenis_pengawalan_id' => 'required|exists:jenis_pengawalans,id',
            'jenis_klasifikasi_id' => 'required|exists:jenis_klasifikasis,id',
            'jumlah_wbp' => 'required|integer|min:0',
            'detail_wbp_dikawal' => 'nullable|array',
            'jumlah_petugas' => 'required|integer|min:0',
            'detail_petugas_pengawalan' => 'nullable|array',
            'sarana_pengawalan' => 'required|string',
            
            // 3 File Upload
            'surat_perintah' => 'required|file|mimes:pdf|max:5120',
            'laporan_pelaksanaan_pengawalan' => 'required|file|mimes:pdf|max:5120',
            'dokumentasi' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // Upload Surat Perintah
        if ($request->hasFile('surat_perintah')) {
            $validated['surat_perintah'] = $request->file('surat_perintah')->store('pengawalan/surat', 'public');
        }

        // Upload Laporan
        if ($request->hasFile('laporan_pelaksanaan_pengawalan')) {
            $validated['laporan_pelaksanaan_pengawalan'] = $request->file('laporan_pelaksanaan_pengawalan')->store('pengawalan/laporan', 'public');
        }

        // Upload & Kompres Foto Dokumentasi
        if ($request->hasFile('dokumentasi')) {
            $file = $request->file('dokumentasi');
            $filename = time() . '_pengawalan_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $folderPath = storage_path('app/public/pengawalan/dokumentasi');
            if (!file_exists($folderPath)) mkdir($folderPath, 0755, true);
            $path = $folderPath . '/' . $filename;
            
            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->decodePath($file->getPathname());
            $image->scale(width: 1200);
            $image->save($path, 75);
            
            $validated['dokumentasi'] = 'pengawalan/dokumentasi/' . $filename;
        }

        DataPengawalanPengamanan::create($validated);

        return redirect()->route('data-pengawalan-pengamanans.index')->with('success', 'Data Pengawalan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datapengawalanpengamanan = DataPengawalanPengamanan::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $datapengawalanpengamanan->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pengamanan/datapengawalanpengamanan/Edit', [
            'datapengawalanpengamanan' => $datapengawalanpengamanan,
            'upts' => $upts,
            'jenis_pengawalans' => JenisPengawalan::all(),
            'jenis_klasifikasis' => JenisKlasifikasi::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $datapengawalanpengamanan = DataPengawalanPengamanan::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
            'tanggal_pelaksanaan' => 'required|date',
            'waktu_pelaksanaan_mulai' => 'required',
            'waktu_pelaksanaan_selesai' => 'required',
            'jenis_pengawalan_id' => 'required|exists:jenis_pengawalans,id',
            'jenis_klasifikasi_id' => 'required|exists:jenis_klasifikasis,id',
            'jumlah_wbp' => 'required|integer|min:0',
            'detail_wbp_dikawal' => 'nullable|array',
            'jumlah_petugas' => 'required|integer|min:0',
            'detail_petugas_pengawalan' => 'nullable|array',
            'sarana_pengawalan' => 'required|string',
            
            'surat_perintah' => 'nullable|file|mimes:pdf|max:5120',
            'laporan_pelaksanaan_pengawalan' => 'nullable|file|mimes:pdf|max:5120',
            'dokumentasi' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        unset($validated['surat_perintah']);
        unset($validated['laporan_pelaksanaan_pengawalan']);
        unset($validated['dokumentasi']);

        // Update Surat
        if ($request->hasFile('surat_perintah')) {
            if ($datapengawalanpengamanan->surat_perintah) Storage::disk('public')->delete($datapengawalanpengamanan->surat_perintah);
            $validated['surat_perintah'] = $request->file('surat_perintah')->store('pengawalan/surat', 'public');
        }

        // Update Laporan
        if ($request->hasFile('laporan_pelaksanaan_pengawalan')) {
            if ($datapengawalanpengamanan->laporan_pelaksanaan_pengawalan) Storage::disk('public')->delete($datapengawalanpengamanan->laporan_pelaksanaan_pengawalan);
            $validated['laporan_pelaksanaan_pengawalan'] = $request->file('laporan_pelaksanaan_pengawalan')->store('pengawalan/laporan', 'public');
        }

        // Update Dokumentasi
        if ($request->hasFile('dokumentasi')) {
            if ($datapengawalanpengamanan->dokumentasi) Storage::disk('public')->delete($datapengawalanpengamanan->dokumentasi);
            
            $file = $request->file('dokumentasi');
            $filename = time() . '_pengawalan_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $folderPath = storage_path('app/public/pengawalan/dokumentasi');
            if (!file_exists($folderPath)) mkdir($folderPath, 0755, true);
            $path = $folderPath . '/' . $filename;
            
            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->decodePath($file->getPathname());
            $image->scale(width: 1200);
            $image->save($path, 75);
            
            $validated['dokumentasi'] = 'pengawalan/dokumentasi/' . $filename;
        }

        $datapengawalanpengamanan->update($validated);

        return redirect()->route('data-pengawalan-pengamanans.index')->with('success', 'Data Pengawalan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $datapengawalanpengamanan = DataPengawalanPengamanan::findOrFail($id);
        
        if ($datapengawalanpengamanan->surat_perintah) Storage::disk('public')->delete($datapengawalanpengamanan->surat_perintah);
        if ($datapengawalanpengamanan->laporan_pelaksanaan_pengawalan) Storage::disk('public')->delete($datapengawalanpengamanan->laporan_pelaksanaan_pengawalan);
        if ($datapengawalanpengamanan->dokumentasi) Storage::disk('public')->delete($datapengawalanpengamanan->dokumentasi);
        
        $datapengawalanpengamanan->delete();

        return redirect()->route('data-pengawalan-pengamanans.index')->with('success', 'Data Pengawalan berhasil dihapus.');
    }
}