<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPemindahan;
use App\Models\Upt;
use App\Models\JenisPemindahan;
use App\Models\JenisPersonel;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DataPemindahanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = DataPemindahan::with([
            'upt',
            'uptAsal',
            'uptTujuan',
            'jenisPemindahan'
        ]);

        if ($user->upt_id) {
            $query->where('upt_id', $user->upt_id);
        } elseif ($request->upt_id) {
            $query->where('upt_id', $request->upt_id);
        }

        if ($request->tanggal) {
            $query->whereDate('tanggal_input', $request->tanggal);
        }

        $datapemindahans = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $upts = $user->upt_id
            ? Upt::where('id', $user->upt_id)->get()
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapemindahan/Index', [
            'datapemindahans' => $datapemindahans,
            'upts' => $upts,
            'jenis_personels' => JenisPersonel::all(),
            'filters' => $request->only([
                'upt_id',
                'tanggal'
            ])
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        $upts_lokasi = $user->upt_id
            ? Upt::where('id', $user->upt_id)->get()
            : Upt::where('is_active', true)->get();

        $semua_upt = Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapemindahan/Create', [
            'upts_lokasi' => $upts_lokasi,
            'semua_upt' => $semua_upt,
            'jenis_pemindahans' => JenisPemindahan::all(),
            'jenis_personels' => JenisPersonel::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',

            'tanggal_input' => 'required|date',

            'tanggal_pelaksanaan' => 'required|date',

            'upt_asal_id' => 'required|exists:upts,id',

            'is_keluar_wilayah' => 'required|boolean',

            'upt_tujuan_id' => $request->is_keluar_wilayah
                ? 'nullable'
                : 'required|exists:upts,id',

            'tujuan_luar_wilayah' => $request->is_keluar_wilayah
                ? 'required|string|max:255'
                : 'nullable|string|max:255',

            'jenis_pemindahan_id' => 'required|exists:jenis_pemindahans,id',

            'detail_lain_lain' => 'nullable|string',

            'jenis_kasus' => 'nullable|string|max:255',

            'jumlah_personel' => 'nullable|array',

            'surat_usulan' => 'required|file|mimes:pdf|max:5120',

            'surat_persetujuan' => 'required|file|mimes:pdf|max:5120',

            'laporan_pemindahan' => 'nullable|file|mimes:pdf|max:5120',

            'foto_pemindahan.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Fix tujuan wilayah
        |--------------------------------------------------------------------------
        */

        if ($request->is_keluar_wilayah) {

            $validated['upt_tujuan_id'] = null;

        } else {

            $validated['tujuan_luar_wilayah'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Upload Surat Usulan
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('surat_usulan')) {

            $validated['surat_usulan'] = $request
                ->file('surat_usulan')
                ->store('pemindahan/usulan', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Upload Surat Persetujuan
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('surat_persetujuan')) {

            $validated['surat_persetujuan'] = $request
                ->file('surat_persetujuan')
                ->store('pemindahan/persetujuan', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Upload Laporan Pemindahan
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('laporan_pemindahan')) {

            $validated['laporan_pemindahan'] = $request
                ->file('laporan_pemindahan')
                ->store('pemindahan/laporan', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Upload Multi Foto
        |--------------------------------------------------------------------------
        */

        $fotoPaths = [];

        if ($request->hasFile('foto_pemindahan')) {

            foreach ($request->file('foto_pemindahan') as $foto) {

                $fotoPaths[] = $foto->store(
                    'pemindahan/foto',
                    'public'
                );
            }
        }

        $validated['foto_pemindahan'] = $fotoPaths;

        DataPemindahan::create($validated);

        return redirect()
            ->route('data-pemindahans.index')
            ->with('success', 'Data Pemindahan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $datapemindahan = DataPemindahan::findOrFail($id);

        $user = auth()->user();

        if (
            $user->upt_id &&
            $datapemindahan->upt_id !== $user->upt_id
        ) {
            abort(403, 'Akses Ditolak!');
        }

        $upts_lokasi = $user->upt_id
            ? Upt::where('id', $user->upt_id)->get()
            : Upt::where('is_active', true)->get();

        $semua_upt = Upt::where('is_active', true)->get();

        return Inertia::render('admin/pembinaan/datapemindahan/Edit', [
            'datapemindahan' => $datapemindahan,
            'upts_lokasi' => $upts_lokasi,
            'semua_upt' => $semua_upt,
            'jenis_pemindahans' => JenisPemindahan::all(),
            'jenis_personels' => JenisPersonel::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $datapemindahan = DataPemindahan::findOrFail($id);

        $validated = $request->validate([
            'upt_id' => 'required|exists:upts,id',

            'tanggal_input' => 'required|date',

            'tanggal_pelaksanaan' => 'required|date',

            'upt_asal_id' => 'required|exists:upts,id',

            'is_keluar_wilayah' => 'required|boolean',

            'upt_tujuan_id' => $request->is_keluar_wilayah
                ? 'nullable'
                : 'required|exists:upts,id',

            'tujuan_luar_wilayah' => $request->is_keluar_wilayah
                ? 'required|string|max:255'
                : 'nullable|string|max:255',

            'jenis_pemindahan_id' => 'required|exists:jenis_pemindahans,id',

            'detail_lain_lain' => 'nullable|string',

            'jenis_kasus' => 'nullable|string|max:255',

            'jumlah_personel' => 'nullable|array',

            'surat_usulan' => 'nullable|file|mimes:pdf|max:5120',

            'surat_persetujuan' => 'nullable|file|mimes:pdf|max:5120',

            'laporan_pemindahan' => 'nullable|file|mimes:pdf|max:5120',

            'foto_pemindahan.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Fix tujuan wilayah
        |--------------------------------------------------------------------------
        */

        if ($request->is_keluar_wilayah) {

            $validated['upt_tujuan_id'] = null;

        } else {

            $validated['tujuan_luar_wilayah'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent overwrite null
        |--------------------------------------------------------------------------
        */

        unset($validated['surat_usulan']);
        unset($validated['surat_persetujuan']);
        unset($validated['laporan_pemindahan']);

        /*
        |--------------------------------------------------------------------------
        | Update Surat Usulan
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('surat_usulan')) {

            if ($datapemindahan->surat_usulan) {

                Storage::disk('public')
                    ->delete($datapemindahan->surat_usulan);
            }

            $validated['surat_usulan'] = $request
                ->file('surat_usulan')
                ->store('pemindahan/usulan', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Update Surat Persetujuan
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('surat_persetujuan')) {

            if ($datapemindahan->surat_persetujuan) {

                Storage::disk('public')
                    ->delete($datapemindahan->surat_persetujuan);
            }

            $validated['surat_persetujuan'] = $request
                ->file('surat_persetujuan')
                ->store('pemindahan/persetujuan', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Update Laporan Pemindahan
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('laporan_pemindahan')) {

            if ($datapemindahan->laporan_pemindahan) {

                Storage::disk('public')
                    ->delete($datapemindahan->laporan_pemindahan);
            }

            $validated['laporan_pemindahan'] = $request
                ->file('laporan_pemindahan')
                ->store('pemindahan/laporan', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Update Multi Foto
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('foto_pemindahan')) {

            if (is_array($datapemindahan->foto_pemindahan)) {

                foreach ($datapemindahan->foto_pemindahan as $foto) {

                    Storage::disk('public')->delete($foto);
                }
            }

            $fotoPaths = [];

            foreach ($request->file('foto_pemindahan') as $foto) {

                $fotoPaths[] = $foto->store(
                    'pemindahan/foto',
                    'public'
                );
            }

            $validated['foto_pemindahan'] = $fotoPaths;
        }

        $datapemindahan->update($validated);

        return redirect()
            ->route('data-pemindahans.index')
            ->with('success', 'Data Pemindahan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $datapemindahan = DataPemindahan::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Hapus file
        |--------------------------------------------------------------------------
        */

        if ($datapemindahan->surat_usulan) {

            Storage::disk('public')
                ->delete($datapemindahan->surat_usulan);
        }

        if ($datapemindahan->surat_persetujuan) {

            Storage::disk('public')
                ->delete($datapemindahan->surat_persetujuan);
        }

        if ($datapemindahan->laporan_pemindahan) {

            Storage::disk('public')
                ->delete($datapemindahan->laporan_pemindahan);
        }

        if (is_array($datapemindahan->foto_pemindahan)) {

            foreach ($datapemindahan->foto_pemindahan as $foto) {

                Storage::disk('public')->delete($foto);
            }
        }

        $datapemindahan->delete();

        return redirect()
            ->route('data-pemindahans.index')
            ->with('success', 'Data Pemindahan berhasil dihapus.');
    }
}