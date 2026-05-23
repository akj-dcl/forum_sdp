<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPembinaanKemandirian;
use Inertia\Inertia;
use App\Models\Upt;
use App\Models\JenisKemandirian;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DataPembinaanKemandirianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataPembinaanKemandirian::with(['upt', 'jenis_kemandirian']);

        if ($user->upt_id) {
            $query->where('upt_id', $user->upt_id);
        } else {
            if ($request->upt_id) {
                $query->where('upt_id', $request->upt_id);
            }
        }

        if ($request->search) {
            $query->where('nama_kegiatan', 'like', "%{$request->search}%");
        }

        if ($request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $data_pembinaan_kemandirian = $query->paginate(10)->withQueryString();

        $upts = $user->upt_id 
            ? Upt::where('id', $user->upt_id)->get() 
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapembinaankemandirian/Index', [
            'datapembinaankemandirians' => $data_pembinaan_kemandirian,
            'upts' => $upts,
            'filters' => $request->only(['search', 'upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapembinaankemandirian/Create', [
            'upts' => $upts,
            'jenis_kemandirians' => JenisKemandirian::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'jenis_kemandirian_id' => 'required|exists:jenis_kemandirians,id',
            'detail_lain_lain' => 'nullable|string|max:255',
            'tanggal'=> 'required|date',
            'nama_kegiatan' => 'required|string|max:255',
            'jumlah_peserta' => 'required|integer',
            'hasil_kegiatan' => 'required|string',
            'rekomendasi_kegiatan' => 'nullable|string',
            'dokumentasi_kegiatan' => 'required|array',
            'dokumentasi_kegiatan.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:512000',
        ]);

        $filePaths = [];

        if ($request->hasFile('dokumentasi_kegiatan')) {
            $manager = ImageManager::usingDriver(Driver::class);
            
            foreach ($request->file('dokumentasi_kegiatan') as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = time() . '_' . uniqid() . '.' . $ext;
                
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    // TRIK GOOGLE DRIVE: Simpan di folder temporary (sementara) server dulu
                    $tempPath = sys_get_temp_dir() . '/' . $filename;
                    
                    $image = $manager->decodePath($file->getPathname());
                    $image->scale(width: 1200);
                    $image->save($tempPath, 75); // Kompres dan simpan sementara
                    
                    // Tembak file yang sudah dikompres langsung ke Google Drive
                    Storage::disk('google')->put('dokumentasi/' . $filename, file_get_contents($tempPath));
                    
                    // Hapus file sementara di server agar tidak menuhin disk
                    unlink($tempPath);
                    
                    $filePaths[] = 'dokumentasi/' . $filename;
                } else {
                    // Jika file Video, langsung tembak ke Google Drive tanpa kompresi
                    $path = $file->storeAs('dokumentasi', $filename, 'google');
                    $filePaths[] = $path;
                }
            }
        }

        $validated['dokumentasi_kegiatan'] = $filePaths;
        DataPembinaanKemandirian::create($validated);

        return redirect()->route('data-pembinaan-kemandirians.index')->with('success', 'Data Pembinaan Kemandirian berhasil ditambahkan dan disimpan ke Google Drive.');
    }

    public function edit(DataPembinaanKemandirian $data_pembinaan_kemandirian)
    {
        $user = auth()->user();

        if ($user->upt_id && $data_pembinaan_kemandirian->upt_id !== $user->upt_id) {
            abort(403, 'Akses Ditolak!');
        }

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapembinaankemandirian/Edit', [
            'datapembinaankemandirians' => $data_pembinaan_kemandirian, 
            'upts' => $upts,
            'jenis_kemandirians' => JenisKemandirian::all(),
        ]);
    }

    public function update(Request $request, DataPembinaanKemandirian $data_pembinaan_kemandirian)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',
            'jenis_kemandirian_id' => 'required|exists:jenis_kemandirians,id',
            'detail_lain_lain' => 'nullable|string|max:255',
            'tanggal'=> 'required|date',
            'nama_kegiatan' => 'required|string|max:255',
            'jumlah_peserta' => 'required|integer',
            'hasil_kegiatan' => 'required|string',
            'rekomendasi_kegiatan' => 'nullable|string',
            'dokumentasi_kegiatan' => 'nullable|array', 
            'dokumentasi_kegiatan.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:204800',
        ]);

        if ($request->hasFile('dokumentasi_kegiatan')) {
            // Hapus file lama di Google Drive
            if (!empty($data_pembinaan_kemandirian->dokumentasi_kegiatan)) {
                foreach ($data_pembinaan_kemandirian->dokumentasi_kegiatan as $oldFile) {
                    if (Storage::disk('google')->exists($oldFile)) {
                        Storage::disk('google')->delete($oldFile);
                    }
                }
            }

            $filePaths = [];
            $manager = ImageManager::usingDriver(Driver::class); 
            
            foreach ($request->file('dokumentasi_kegiatan') as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = time() . '_' . uniqid() . '.' . $ext;
                
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $tempPath = sys_get_temp_dir() . '/' . $filename;
                    
                    $image = $manager->decodePath($file->getPathname());
                    $image->scale(width: 1200);
                    $image->save($tempPath, 75);
                    
                    Storage::disk('google')->put('dokumentasi/' . $filename, file_get_contents($tempPath));
                    unlink($tempPath);
                    
                    $filePaths[] = 'dokumentasi/' . $filename;
                } else {
                    $path = $file->storeAs('dokumentasi', $filename, 'google');
                    $filePaths[] = $path;
                }
            }

            $validated['dokumentasi_kegiatan'] = $filePaths;
        } else {
            unset($validated['dokumentasi_kegiatan']);
        }

        $data_pembinaan_kemandirian->update($validated);
        return redirect()->route('data-pembinaan-kemandirians.index')->with('success', 'Data Pembinaan Kemandirian berhasil diperbarui di Google Drive.');
    }

    public function destroy(DataPembinaanKemandirian $data_pembinaan_kemandirian)
    {
        // Hapus file di Google Drive saat data dihapus
        if (!empty($data_pembinaan_kemandirian->dokumentasi_kegiatan)) {
            foreach ($data_pembinaan_kemandirian->dokumentasi_kegiatan as $file) {
                if (Storage::disk('google')->exists($file)) {
                    Storage::disk('google')->delete($file);
                }
            }
        }

        $data_pembinaan_kemandirian->delete();
        return redirect()->route('data-pembinaan-kemandirians.index')->with('success', 'Data Pembinaan Kemandirian berhasil dihapus beserta filenya.');
    }
}