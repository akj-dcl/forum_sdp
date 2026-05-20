<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataIntegrasi;
use Inertia\Inertia;
use App\Models\Upt;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DataIntegrasiController extends Controller
{
    private $jenisIntegrasi = ['pb', 'cb', 'cmb', 'asimilasi'];

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = DataIntegrasi::with(['upt']);

        if ($user->upt_id) {
            $query->where('upt_id', $user->upt_id);
        } elseif ($request->upt_id) {
            $query->where('upt_id', $request->upt_id);
        }

        if ($request->tanggal) {
            $query->whereDate('tanggal_input', $request->tanggal);
        }

        $dataintegrasis = $query->latest()->paginate(10)->withQueryString();

        $upts = $user->upt_id
            ? Upt::where('id', $user->upt_id)->get()
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataintegrasi/Index', [
            'dataintegrasis' => $dataintegrasis,
            'upts' => $upts,
            'filters' => $request->only(['upt_id', 'tanggal'])
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        $upts = $user->upt_id
            ? Upt::where('id', $user->upt_id)->get()
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataintegrasi/Create', [
            'upts' => $upts
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
        ];

        foreach ($this->jenisIntegrasi as $jenis) {

            $rules["tanggal_pelaksanaan_{$jenis}"] = 'required|date';

            $rules["jumlah_{$jenis}"] = 'required|integer|min:0';

            $rules["sk_{$jenis}"] = 'required|file|mimes:pdf|max:5120';

            // MULTIPLE IMAGE
            $rules["dokumentasi_{$jenis}"] = 'required|array';
            $rules["dokumentasi_{$jenis}.*"] = 'image|mimes:jpg,jpeg,png|max:5120';
        }

        $validated = $request->validate($rules);

        $manager = ImageManager::usingDriver(Driver::class);

        foreach ($this->jenisIntegrasi as $jenis) {

            /**
             * =========================================================
             * UPLOAD PDF
             * =========================================================
             */
            if ($request->hasFile("sk_{$jenis}")) {

                $validated["sk_{$jenis}"] =
                    $request->file("sk_{$jenis}")
                    ->store("integrasi/{$jenis}/sk", 'public');
            }

            /**
             * =========================================================
             * MULTIPLE FOTO
             * =========================================================
             */
            if ($request->hasFile("dokumentasi_{$jenis}")) {

                $images = [];

                foreach ($request->file("dokumentasi_{$jenis}") as $file) {

                    $filename =
                        time() .
                        "_{$jenis}_" .
                        uniqid() .
                        '.' .
                        $file->getClientOriginalExtension();

                    $folderPath =
                        storage_path("app/public/integrasi/{$jenis}/dokumentasi");

                    if (!file_exists($folderPath)) {
                        mkdir($folderPath, 0755, true);
                    }

                    $path = $folderPath . '/' . $filename;

                    $image = $manager->decodePath($file->getPathname());

                    $image->scale(width: 1200);

                    $image->save($path, 75);

                    $images[] =
                        "integrasi/{$jenis}/dokumentasi/" . $filename;
                }

                $validated["dokumentasi_{$jenis}"] = $images;
            }
        }

        DataIntegrasi::create($validated);

        return redirect()
            ->route('data-integrasis.index')
            ->with('success', 'Data Pelaksanaan Integrasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $dataintegrasi = DataIntegrasi::findOrFail($id);

        $user = auth()->user();

        if (
            $user->upt_id &&
            $dataintegrasi->upt_id !== $user->upt_id
        ) {
            abort(403, 'Akses Ditolak!');
        }

        $upts = $user->upt_id
            ? Upt::where('id', $user->upt_id)->get()
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/dataintegrasi/Edit', [
            'dataintegrasi' => $dataintegrasi,
            'upts' => $upts,
        ]);
    }

    public function update(Request $request, $id)
    {
        $dataintegrasi = DataIntegrasi::findOrFail($id);

        $rules = [
            'upt_id' => 'required|exists:upts,id',
            'tanggal_input' => 'required|date',
        ];

        foreach ($this->jenisIntegrasi as $jenis) {

            $rules["tanggal_pelaksanaan_{$jenis}"] = 'required|date';

            $rules["jumlah_{$jenis}"] = 'required|integer|min:0';

            $rules["sk_{$jenis}"] =
                'nullable|file|mimes:pdf|max:5120';

            // MULTIPLE IMAGE
            $rules["dokumentasi_{$jenis}"] = 'nullable|array';

            $rules["dokumentasi_{$jenis}.*"] =
                'image|mimes:jpg,jpeg,png|max:5120';
        }

        $validated = $request->validate($rules);

        $manager = ImageManager::usingDriver(Driver::class);

        foreach ($this->jenisIntegrasi as $jenis) {

            unset($validated["sk_{$jenis}"]);
            unset($validated["dokumentasi_{$jenis}"]);

            /**
             * =========================================================
             * UPDATE PDF
             * =========================================================
             */
            if ($request->hasFile("sk_{$jenis}")) {

                if ($dataintegrasi->{"sk_{$jenis}"}) {

                    Storage::disk('public')
                        ->delete($dataintegrasi->{"sk_{$jenis}"});
                }

                $validated["sk_{$jenis}"] =
                    $request->file("sk_{$jenis}")
                    ->store("integrasi/{$jenis}/sk", 'public');
            }

            /**
             * =========================================================
             * UPDATE MULTIPLE FOTO
             * =========================================================
             */
            if ($request->hasFile("dokumentasi_{$jenis}")) {

                // HAPUS FOTO LAMA
                if ($dataintegrasi->{"dokumentasi_{$jenis}"}) {

                    foreach (
                        $dataintegrasi->{"dokumentasi_{$jenis}"} as $oldImage
                    ) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                $images = [];

                foreach ($request->file("dokumentasi_{$jenis}") as $file) {

                    $filename =
                        time() .
                        "_{$jenis}_" .
                        uniqid() .
                        '.' .
                        $file->getClientOriginalExtension();

                    $folderPath =
                        storage_path("app/public/integrasi/{$jenis}/dokumentasi");

                    if (!file_exists($folderPath)) {
                        mkdir($folderPath, 0755, true);
                    }

                    $path = $folderPath . '/' . $filename;

                    $image = $manager->decodePath($file->getPathname());

                    $image->scale(width: 1200);

                    $image->save($path, 75);

                    $images[] =
                        "integrasi/{$jenis}/dokumentasi/" . $filename;
                }

                $validated["dokumentasi_{$jenis}"] = $images;
            }
        }

        $dataintegrasi->update($validated);

        return redirect()
            ->route('data-integrasis.index')
            ->with('success', 'Data Pelaksanaan Integrasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dataintegrasi = DataIntegrasi::findOrFail($id);

        foreach ($this->jenisIntegrasi as $jenis) {

            /**
             * =========================================================
             * HAPUS PDF
             * =========================================================
             */
            if ($dataintegrasi->{"sk_{$jenis}"}) {

                Storage::disk('public')
                    ->delete($dataintegrasi->{"sk_{$jenis}"});
            }

            /**
             * =========================================================
             * HAPUS MULTIPLE FOTO
             * =========================================================
             */
            if ($dataintegrasi->{"dokumentasi_{$jenis}"}) {

                foreach (
                    $dataintegrasi->{"dokumentasi_{$jenis}"} as $image
                ) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        $dataintegrasi->delete();

        return redirect()
            ->route('data-integrasis.index')
            ->with('success', 'Data Pelaksanaan Integrasi berhasil dihapus.');
    }
}