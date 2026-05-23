<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPembinaanKepribadian;
use Inertia\Inertia;
use App\Models\Upt;
use App\Models\JenisKepribadian;
use Illuminate\Support\Facades\Storage;

class DataPembinaanKepribadianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 
        $query = DataPembinaanKepribadian::with(['upt', 'jenis_kepribadian']);

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

        $data_pembinaan_kepribadian = $query->paginate(10)->withQueryString();

        $upts = $user->upt_id 
            ? Upt::where('id', $user->upt_id)->get() 
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapembinaankepribadian/Index', [
            'datapembinaankepribadians' => $data_pembinaan_kepribadian,
            'upts' => $upts, 
            'filters' => $request->only(['search', 'upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapembinaankepribadian/Create', [
            'upts' => $upts,
            'jenis_kepribadians' => JenisKepribadian::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'upt_id' => 'required|exists:upts,id',
            'jenis_kepribadian_id' => 'required|exists:jenis_kepribadians,id',
            'detail_lain_lain' => 'nullable|string|max:255', 
            'nama_kegiatan' => 'required|string|max:255',
            'pemateri' => 'required|string|max:255',
            'jumlah_peserta' => 'required|integer|min:1',
            'hasil_kegiatan' => 'required|string',
            'rekomendasi_kegiatan' => 'nullable|string',
            'dokumentasi_kegiatan' => 'required|array',
            'dokumentasi_kegiatan.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:512000',
        ]);

        $filePaths = [];

        if ($request->hasFile('dokumentasi_kegiatan')) {
            foreach ($request->file('dokumentasi_kegiatan') as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = time() . '_' . uniqid() . '.' . $ext;
                
                // Langsung tembak file asli ke Google Drive tanpa kompresi (Biar Cepat!)
                $path = $file->storeAs('kepribadian/dokumentasi', $filename, 'google');
                $filePaths[] = $path;
            }
        }

        $validated['dokumentasi_kegiatan'] = $filePaths;

        DataPembinaanKepribadian::create($validated);
        return redirect()->route('data-pembinaan-kepribadians.index')->with('success', 'Kegiatan kepribadian berhasil disimpan ke Google Drive.');
    }

    public function edit(DataPembinaanKepribadian $data_pembinaan_kepribadian)
    {
        $user = auth()->user();

        if ($user->upt_id && $data_pembinaan_kepribadian->upt_id !== $user->upt_id) {
            abort(403, 'Akses Ditolak!');
        }

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapembinaankepribadian/Edit', [
            'datapembinaankepribadians' => $data_pembinaan_kepribadian, 
            'upts' => $upts,
            'jenis_kepribadians' => JenisKepribadian::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $kegiatan = DataPembinaanKepribadian::findOrFail($id);
        
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'upt_id' => 'required|exists:upts,id',
            'jenis_kepribadian_id' => 'required|exists:jenis_kepribadians,id',
            'detail_lain_lain' => 'nullable|string|max:255', 
            'nama_kegiatan' => 'required|string|max:255',
            'pemateri' => 'required|string|max:255',
            'jumlah_peserta' => 'required|integer|min:1',
            'hasil_kegiatan' => 'required|string',
            'rekomendasi_kegiatan' => 'nullable|string',
            'dokumentasi_kegiatan' => 'nullable|array',
            'dokumentasi_kegiatan.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:204800',
        ]);

        if ($request->hasFile('dokumentasi_kegiatan')) {
            // Hapus file lama di Google Drive
            if (!empty($kegiatan->dokumentasi_kegiatan)) {
                foreach ($kegiatan->dokumentasi_kegiatan as $oldFile) {
                    if (Storage::disk('google')->exists($oldFile)) {
                        Storage::disk('google')->delete($oldFile);
                    }
                }
            }
            
            // Simpan file baru langsung ke Google Drive
            $filePaths = [];
            foreach ($request->file('dokumentasi_kegiatan') as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = time() . '_' . uniqid() . '.' . $ext;
                $path = $file->storeAs('kepribadian/dokumentasi', $filename, 'google');
                $filePaths[] = $path;
            }
            $validated['dokumentasi_kegiatan'] = $filePaths;
        } else {
            unset($validated['dokumentasi_kegiatan']); 
        }

        $kegiatan->update($validated);
        return redirect()->route('data-pembinaan-kepribadians.index')->with('success', 'Kegiatan kepribadian berhasil diperbarui di Google Drive.');
    }

    public function destroy(DataPembinaanKepribadian $data_pembinaan_kepribadian)
    {
        // Hapus file terkait dari Google Drive saat baris data dihapus
        if (!empty($data_pembinaan_kepribadian->dokumentasi_kegiatan)) {
            foreach ($data_pembinaan_kepribadian->dokumentasi_kegiatan as $file) {
                if (Storage::disk('google')->exists($file)) {
                    Storage::disk('google')->delete($file);
                }
            }
        }

        $data_pembinaan_kepribadian->delete();
        return redirect()->route('data-pembinaan-kepribadians.index')->with('success', 'Data Pembinaan Kepribadian berhasil dihapus beserta filenya.');
    }
}