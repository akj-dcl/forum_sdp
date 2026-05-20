<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPembinaanKepribadian;
use Inertia\Inertia;
use App\Models\Upt;
use App\Models\JenisKepribadian;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DataPembinaanKepribadianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); 

        // Modifikasi query untuk handle filter UPT
        $query = DataPembinaanKepribadian::with(['upt', 'jenis_kepribadian']);

        // Jika user adalah admin UPT, paksa data hanya untuk UPT-nya saja
        if ($user->upt_id) {
            $query->where('upt_id', $user->upt_id);
        } else {
            // Jika Super Admin/Kanwil, jalankan filter dari dropdown jika dipilih
            if ($request->upt_id) {
                $query->where('upt_id', $request->upt_id);
            }
        }

        // Filter pencarian nama kegiatan
        if ($request->search) {
            $query->where('nama_kegiatan', 'like', "%{$request->search}%");
        }

        // TAMBAHAN: Filter by Tanggal
        if ($request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $data_pembinaan_kepribadian = $query->paginate(10)->withQueryString();

        // Ambil data UPT untuk dropdown filter (Hanya tampilkan 1 jika user UPT)
        $upts = $user->upt_id 
            ? Upt::where('id', $user->upt_id)->get() 
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapembinaankepribadian/Index', [
            'datapembinaankepribadians' => $data_pembinaan_kepribadian,
            'upts' => $upts, // Lempar data UPT ke Vue
            'filters' => $request->only(['search', 'upt_id', 'tanggal']) // Lempar parameter upt_id
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        // Jika user punya upt_id, ambil 1 UPT saja. Jika Kanwil/Super Admin, ambil semua UPT.
        $upts = $user->upt_id 
            ? Upt::where('id', $user->upt_id)->get() 
            : Upt::where('is_active', true)->get();

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
            'detail_lain_lain' => 'nullable|string|max:255', // Validasi Baru
            'nama_kegiatan' => 'required|string|max:255',
            'pemateri' => 'required|string|max:255',
            'jumlah_peserta' => 'required|integer|min:1',
            'hasil_kegiatan' => 'required|string',
            'rekomendasi_kegiatan' => 'nullable|string',
            'dokumentasi_kegiatan.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:5120',
        ]);

        if ($request->hasFile('dokumentasi_kegiatan')) {
            $filePaths = [];
            foreach ($request->file('dokumentasi_kegiatan') as $file) {
                $filePaths[] = $file->store('kepribadian/dokumentasi', 'public');
            }
            $validated['dokumentasi_kegiatan'] = $filePaths;
        }

        DataPembinaanKepribadian::create($validated);
        return redirect()->route('data-pembinaan-kepribadians.index')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    // 1. Variabel di dalam kurung HARUS $data_pembinaan_kepribadian
    public function edit(DataPembinaanKepribadian $data_pembinaan_kepribadian)
    {
        $user = auth()->user();

        if ($user->upt_id && $data_pembinaan_kepribadian->upt_id !== $user->upt_id) {
            abort(403, 'Akses Ditolak! Anda tidak dapat mengedit data Lapas lain.');
        }

        $upts = $user->upt_id 
            ? Upt::where('id', $user->upt_id)->get() 
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapembinaankepribadian/Edit', [
            // 2. Nah, di sini kamu bebas pakai nama panjang untuk dikirim ke Vue
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
            'detail_lain_lain' => 'nullable|string|max:255', // Validasi Baru
            'nama_kegiatan' => 'required|string|max:255',
            'pemateri' => 'required|string|max:255',
            'jumlah_peserta' => 'required|integer|min:1',
            'hasil_kegiatan' => 'required|string',
            'rekomendasi_kegiatan' => 'nullable|string',
            'dokumentasi_kegiatan.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:5120',
        ]);

        if ($request->hasFile('dokumentasi_kegiatan')) {
            // Hapus file lama
            if ($kegiatan->dokumentasi_kegiatan) {
                foreach ($kegiatan->dokumentasi_kegiatan as $oldFile) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldFile);
                }
            }
            // Simpan file baru
            $filePaths = [];
            foreach ($request->file('dokumentasi_kegiatan') as $file) {
                $filePaths[] = $file->store('kepribadian/dokumentasi', 'public');
            }
            $validated['dokumentasi_kegiatan'] = $filePaths;
        } else {
            unset($validated['dokumentasi_kegiatan']); // Jangan timpa jika kosong
        }

        $kegiatan->update($validated);
        return redirect()->route('data-pembinaan-kepribadians.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(DataPembinaanKepribadian $data_pembinaan_kepribadian)
    {
        // Gunakan variabel yang benar
        if (!empty($data_pembinaan_kepribadian->dokumentasi_kegiatan)) {
            foreach ($data_pembinaan_kepribadian->dokumentasi_kegiatan as $file) {
                Storage::disk('public')->delete($file);
            }
        }

        // Perbaiki pemanggilan fungsi delete()
        $data_pembinaan_kepribadian->delete();
        
        return redirect()->route('data-pembinaan-kepribadians.index')->with('success', 'Data Pembinaan Kepribadian berhasil dihapus.');
    }
}
