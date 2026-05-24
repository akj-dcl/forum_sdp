<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataMouPks;
use App\Models\Upt;
use Inertia\Inertia;

class DataMouPksController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = DataMouPks::with(['upt']);

        // Proteksi Data UPT
        if ($user->upt_id) {
            $query->where(function ($q) use ($user) {
                $q->where('upt_id', $user->upt_id)
                    ->orWhereIn('kategori_pemrakarsa', ['KANWIL', 'DITJENPAS']);
            });
        } elseif ($request->upt_id) {
            $query->where('upt_id', $request->upt_id);
        }

        // 🛠️ FILTER 1: PENULISAN FIELD KETIK (PENCARIAN JUDUL / MITRA)
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('judul_kerjasama', 'like', "%{$request->search}%")
                  ->orWhere('instansi_kerjasama', 'like', "%{$request->search}%");
            });
        }

        // 🛠️ FILTER 2: TINGKAT MOU / PEMRAKARSA
        if ($request->kategori_pemrakarsa) {
            $query->where('kategori_pemrakarsa', $request->kategori_pemrakarsa);
        }

        // 🛠️ FILTER 3: STATUS TAHANAN / PROSES
        if ($request->status_tahapan) {
            if ($request->status_tahapan === 'Selesai') {
                $query->where('status_tahapan', 'like', '%Selesai%');
            } else {
                $query->where('status_tahapan', $request->status_tahapan);
            }
        }

        $datamoupks = $query->latest()
            ->paginate(10)
            ->withQueryString();

        $upts = Upt::where('is_active', true)->get();
//  
        return Inertia::render('admin/tudanumum/datamoupks/Index', [
            'datamoupks' => $datamoupks,
            'upts' => $upts,
            // 🛠️ Lempar balik filter ke view agar tidak hilang saat halaman berganti
            'filters' => $request->only(['upt_id', 'search', 'kategori_pemrakarsa', 'status_tahapan']),
            'isUptUser' => $user->upt_id ? true : false,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        $upts = $user->upt_id
            ? Upt::where('id', $user->upt_id)->get()
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/datamoupks/Create', [
            'upts' => $upts,
            'isUptUser' => $user->upt_id ? true : false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_pemrakarsa' => 'required|string',
            'upt_id' => 'nullable|exists:upts,id',
            'judul_kerjasama' => 'required|string',
            'jenis_kerjasama' => 'required|string|max:255',
            'instansi_kerjasama' => 'required|string|max:255',

            // LINK URL
            'draft_awal' => 'nullable|url',

            'masa_berlaku_mulai' => 'nullable|date',
            'masa_berlaku_selesai' => 'nullable|date',

            // LINK URL
            'file_mou_pks' => 'nullable|url',
            'dokumentasi_penandatanganan' => 'nullable|url',
        ]);

        $mou = new DataMouPks();

        $mou->kategori_pemrakarsa = $request->kategori_pemrakarsa;
        $mou->upt_id = $request->upt_id;
        $mou->judul_kerjasama = $request->judul_kerjasama;
        $mou->jenis_kerjasama = $request->jenis_kerjasama;
        $mou->instansi_kerjasama = $request->instansi_kerjasama;

        // =========================
        // ALUR UPT
        // =========================
        if ($request->kategori_pemrakarsa === 'UPT') {

            $mou->status_tahapan = 'Draft Awal (UPT)';

            if ($request->draft_awal) {

                $mou->riwayat_tahapan = [[
                    'tanggal' => now()->format('Y-m-d H:i'),
                    'tahapan' => 'Draft Awal (UPT)',
                    'file' => $request->draft_awal,
                    'catatan' => 'Pengajuan draft awal oleh UPT',
                ]];
            }

        } else {

            // =========================
            // REFERENSI FINAL
            // =========================

            $mou->status_tahapan = 'Selesai (Referensi)';

            $mou->masa_berlaku_mulai = $request->masa_berlaku_mulai;
            $mou->masa_berlaku_selesai = $request->masa_berlaku_selesai;

            $mou->file_mou_pks = $request->file_mou_pks;
            $mou->dokumentasi_penandatanganan = $request->dokumentasi_penandatanganan;
        }

        $mou->save();

        return redirect()
            ->route('data-mou-pks.index')
            ->with('success', 'Data MOU/PKS berhasil dibuat.');
    }

    public function edit($id)
    {
        $datamoupks = DataMouPks::findOrFail($id);

        $user = auth()->user();

        $upts = $user->upt_id
            ? Upt::where('id', $user->upt_id)->get()
            : Upt::where('is_active', true)->get();

        return Inertia::render('admin/tudanumum/datamoupks/Edit', [
            'datamoupks' => $datamoupks,
            'upts' => $upts,
            'isUptUser' => $user->upt_id ? true : false,
        ]);
    }

    public function update(Request $request, $id)
    {
        $mou = DataMouPks::findOrFail($id);

        // ====================================================
        // 1. UPDATE INFO DASAR
        // ====================================================

        if ($request->has('is_update_info') && $request->is_update_info == 'yes') {

            $request->validate([
                'judul_kerjasama' => 'required|string|max:255',
                'jenis_kerjasama' => 'required|string|max:255',
                'instansi_kerjasama' => 'required|string|max:255',
            ]);

            $mou->judul_kerjasama = $request->judul_kerjasama;
            $mou->jenis_kerjasama = $request->jenis_kerjasama;
            $mou->instansi_kerjasama = $request->instansi_kerjasama;

            $mou->save();

            return redirect()->back()
                ->with('success', 'Informasi dasar berhasil diperbarui.');
        }

        // ====================================================
        // 2. TAMBAH TAHAPAN BARU
        // ====================================================

        if ($request->has('tahapan_baru') && $request->tahapan_baru != null) {

            $request->validate([
                'file_draft_baru' => 'required|url',
            ]);

            $riwayat = $mou->riwayat_tahapan ?? [];

            $riwayat[] = [
                'tanggal' => now()->format('Y-m-d H:i'),
                'tahapan' => $request->tahapan_baru,
                'file' => $request->file_draft_baru,
                'catatan' => $request->catatan_baru ?? 'Tanpa catatan',
            ];

            $mou->riwayat_tahapan = $riwayat;
            $mou->status_tahapan = $request->tahapan_baru;

            $mou->save();

            return redirect()->back()
                ->with('success', 'Tahapan baru berhasil ditambahkan.');
        }

        // ====================================================
        // 3. FINALISASI
        // ====================================================

        if ($request->has('is_finalisasi') && $request->is_finalisasi == 'yes') {

            $request->validate([
                'masa_berlaku_mulai' => 'required|date',
                'masa_berlaku_selesai' => 'required|date',

                'file_mou_pks' => 'nullable|url',
                'dokumentasi_penandatanganan' => 'nullable|url',
            ]);

            $mou->masa_berlaku_mulai = $request->masa_berlaku_mulai;
            $mou->masa_berlaku_selesai = $request->masa_berlaku_selesai;

            $mou->status_tahapan = 'Selesai';

            if ($request->file_mou_pks) {
                $mou->file_mou_pks = $request->file_mou_pks;
            }

            if ($request->dokumentasi_penandatanganan) {
                $mou->dokumentasi_penandatanganan = $request->dokumentasi_penandatanganan;
            }

            $mou->save();

            return redirect()
                ->route('data-mou-pks.index')
                ->with('success', 'MOU/PKS berhasil difinalisasi.');
        }

        // ====================================================
        // 4. UPDATE DATA FINAL
        // ====================================================

        if ($request->has('is_update_final') && $request->is_update_final == 'yes') {

            $request->validate([
                'masa_berlaku_mulai' => 'required|date',
                'masa_berlaku_selesai' => 'required|date',

                'file_mou_pks' => 'nullable|url',
                'dokumentasi_penandatanganan' => 'nullable|url',
            ]);

            $mou->masa_berlaku_mulai = $request->masa_berlaku_mulai;
            $mou->masa_berlaku_selesai = $request->masa_berlaku_selesai;

            if ($request->file_mou_pks) {
                $mou->file_mou_pks = $request->file_mou_pks;
            }

            if ($request->dokumentasi_penandatanganan) {
                $mou->dokumentasi_penandatanganan = $request->dokumentasi_penandatanganan;
            }

            $mou->save();

            return redirect()->back()
                ->with('success', 'Dokumen final berhasil diperbarui.');
        }

        return redirect()->back();
    }

    public function destroy($id)
    {
        $mou = DataMouPks::findOrFail($id);

        $mou->delete();

        return redirect()
            ->route('data-mou-pks.index')
            ->with('success', 'MOU/PKS berhasil dihapus.');
    }
}