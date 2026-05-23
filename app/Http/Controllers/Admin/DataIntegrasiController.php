<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataIntegrasi;
use Inertia\Inertia;
use App\Models\Upt;
use Illuminate\Support\Facades\Storage;

class DataIntegrasiController extends Controller
{
    // 👇 Tambahkan 'bebas_murni' dan 'cmk' ke dalam array ini
    private $jenisIntegrasi = ['pb', 'cb', 'cmb', 'asimilasi', 'bebas_murni', 'cmk'];

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = DataIntegrasi::with(['upt']);

        if ($user->upt_id) $query->where('upt_id', $user->upt_id);
        elseif ($request->upt_id) $query->where('upt_id', $request->upt_id);

        if ($request->tanggal) $query->whereDate('tanggal_input', $request->tanggal);

        $dataintegrasis = $query->latest()->paginate(10)->withQueryString();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataintegrasi/Index', [
            'dataintegrasis' => $dataintegrasis,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataintegrasi/Create', ['upts' => $upts]);
    }

    public function store(Request $request)
    {
        set_time_limit(300);
        $rules = [
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
        ];

        foreach ($this->jenisIntegrasi as $jenis) {
            $rules["jumlah_{$jenis}"] = 'required|integer|min:0';
            
            // LOGIKA DINAMIS: Jika jumlah > 0, wajibkan file dan tanggal
            if ($request->input("jumlah_{$jenis}") > 0) {
                $rules["tanggal_pelaksanaan_{$jenis}"] = 'required|date';
                $rules["sk_{$jenis}"] = 'required|file|mimes:pdf|max:5120';
                $rules["dokumentasi_{$jenis}"] = 'required|array';
                $rules["dokumentasi_{$jenis}.*"] = 'image|mimes:jpg,jpeg,png|max:51200';
            } else {
                // Jika 0, boleh kosong (nullable)
                $rules["tanggal_pelaksanaan_{$jenis}"] = 'nullable|date';
                $rules["sk_{$jenis}"] = 'nullable|file|mimes:pdf|max:5120';
                $rules["dokumentasi_{$jenis}"] = 'nullable|array';
                $rules["dokumentasi_{$jenis}.*"] = 'image|mimes:jpg,jpeg,png|max:51200';
            }
        }

        $validated = $request->validate($rules);

        foreach ($this->jenisIntegrasi as $jenis) {
            if ($request->hasFile("sk_{$jenis}")) {
                $validated["sk_{$jenis}"] = $request->file("sk_{$jenis}")->store("integrasi/{$jenis}/sk", 'google');
            }

            if ($request->hasFile("dokumentasi_{$jenis}")) {
                $images = [];
                foreach ($request->file("dokumentasi_{$jenis}") as $file) {
                    $filename = time() . "_{$jenis}_" . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs("integrasi/{$jenis}/dokumentasi", $filename, 'google');
                    $images[] = $path;
                }
                $validated["dokumentasi_{$jenis}"] = $images;
            }
        }

        DataIntegrasi::create($validated);
        return redirect()->route('data-integrasis.index')->with('success', 'Data Pelaksanaan Integrasi berhasil ditambahkan ke Google Drive.');
    }

    public function edit($id)
    {
        $dataintegrasi = DataIntegrasi::findOrFail($id);
        $user = auth()->user();

        if ($user->upt_id && $dataintegrasi->upt_id !== $user->upt_id) abort(403, 'Akses Ditolak!');

        $upts = $user->upt_id ? Upt::where('id', $user->upt_id)->get() : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataintegrasi/Edit', [
            'dataintegrasi' => $dataintegrasi,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        set_time_limit(300);
        $dataintegrasi = DataIntegrasi::findOrFail($id);
        
        $rules = [
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
        ];

        foreach ($this->jenisIntegrasi as $jenis) {
            $rules["jumlah_{$jenis}"] = 'required|integer|min:0';
            
            // Di Update, file selalu nullable karena mungkin tidak ingin diganti
            if ($request->input("jumlah_{$jenis}") > 0) {
                $rules["tanggal_pelaksanaan_{$jenis}"] = 'required|date';
            } else {
                $rules["tanggal_pelaksanaan_{$jenis}"] = 'nullable|date';
            }
            
            $rules["sk_{$jenis}"] = 'nullable|file|mimes:pdf|max:5120';
            $rules["dokumentasi_{$jenis}"] = 'nullable|array';
            $rules["dokumentasi_{$jenis}.*"] = 'image|mimes:jpg,jpeg,png|max:51200';
        }

        $validated = $request->validate($rules);

        foreach ($this->jenisIntegrasi as $jenis) {
            unset($validated["sk_{$jenis}"]);
            unset($validated["dokumentasi_{$jenis}"]);

            if ($request->hasFile("sk_{$jenis}")) {
                if ($dataintegrasi->{"sk_{$jenis}"} && Storage::disk('google')->exists($dataintegrasi->{"sk_{$jenis}"})) {
                    Storage::disk('google')->delete($dataintegrasi->{"sk_{$jenis}"});
                }
                $validated["sk_{$jenis}"] = $request->file("sk_{$jenis}")->store("integrasi/{$jenis}/sk", 'google');
            }

            if ($request->hasFile("dokumentasi_{$jenis}")) {
                if ($dataintegrasi->{"dokumentasi_{$jenis}"}) {
                    foreach ($dataintegrasi->{"dokumentasi_{$jenis}"} as $oldImage) {
                        if (Storage::disk('google')->exists($oldImage)) Storage::disk('google')->delete($oldImage);
                    }
                }

                $images = [];
                foreach ($request->file("dokumentasi_{$jenis}") as $file) {
                    $filename = time() . "_{$jenis}_" . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs("integrasi/{$jenis}/dokumentasi", $filename, 'google');
                    $images[] = $path;
                }
                $validated["dokumentasi_{$jenis}"] = $images;
            }
        }

        $dataintegrasi->update($validated);
        return redirect()->route('data-integrasis.index')->with('success', 'Data Pelaksanaan Integrasi berhasil diperbarui di Google Drive.');
    }

    public function destroy($id)
    {
        $dataintegrasi = DataIntegrasi::findOrFail($id);

        foreach ($this->jenisIntegrasi as $jenis) {
            // HAPUS PDF
            if ($dataintegrasi->{"sk_{$jenis}"} && Storage::disk('google')->exists($dataintegrasi->{"sk_{$jenis}"})) {
                Storage::disk('google')->delete($dataintegrasi->{"sk_{$jenis}"});
            }

            // HAPUS FOTO
            if ($dataintegrasi->{"dokumentasi_{$jenis}"}) {
                foreach ($dataintegrasi->{"dokumentasi_{$jenis}"} as $image) {
                    if (Storage::disk('google')->exists($image)) {
                        Storage::disk('google')->delete($image);
                    }
                }
            }
        }

        $dataintegrasi->delete();
        return redirect()->route('data-integrasis.index')->with('success', 'Data Pelaksanaan Integrasi beserta filenya berhasil dihapus.');
    }
}