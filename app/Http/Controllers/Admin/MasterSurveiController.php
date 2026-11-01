<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterSurvei;
use App\Models\PertanyaanSurvei;
use App\Models\RoleTier;
use App\Models\ResponSurvei;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Channel;
use Spatie\Permission\Models\Role;

class MasterSurveiController extends Controller
{
    public function index(Request $request) 
    {
        $surveis = MasterSurvei::with(['user'])
            ->withCount(['pertanyaans', 'respons'])
            ->when($request->search, function ($q, $search) {
                $q->where('judul', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/datamaster/survei/Index', [
            'surveis' => $surveis,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $roles = RoleTier::pluck('nama_tier')->toArray();
        $channels = Channel::orderBy('name')->get();

        return Inertia::render('admin/datamaster/survei/Create', [
            'roles' => $roles,
            'channels' => $channels,
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'channel_id' => 'nullable|exists:channels,id',
            'deskripsi' => 'nullable|string',
            'batas_pengisian' => 'required|in:sekali,berkali_kali',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'target_roles' => 'nullable|array',
            'is_active' => 'boolean',
            'pertanyaans' => 'required|array|min:1',
            'pertanyaans.*.teks_pertanyaan' => 'required|string',
            'pertanyaans.*.tipe_jawaban' => 'required|string|in:teks,pilihan,angka,foto',
            'pertanyaans.*.wajib_diisi' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            $survei = MasterSurvei::create([
                'user_id' => auth()->id(),
                'channel_id' => $request->channel_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'batas_pengisian' => $request->batas_pengisian,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'target_roles' => $request->target_roles ?: [],
                'is_active' => $request->is_active ?? true,
            ]);

            foreach ($request->pertanyaans as $index => $q) {
                PertanyaanSurvei::create([
                    'survei_id' => $survei->id,
                    'teks_pertanyaan' => $q['teks_pertanyaan'],
                    'tipe_jawaban' => $q['tipe_jawaban'],
                    'opsi_pilihan' => isset($q['opsi_pilihan']) && is_array($q['opsi_pilihan']) ? $q['opsi_pilihan'] : null,
                    'wajib_diisi' => $q['wajib_diisi'] ?? true,
                    'urutan' => $index + 1,
                ]);
            }
        });

        return redirect()->route('admin.master-survei.index')->with('success', 'Master Survei berhasil diterbitkan.');
    }

    public function show($id)
    {
        $survei = MasterSurvei::with(['pertanyaans', 'respons.user', 'respons.upt'])->findOrFail($id);

        return Inertia::render('admin/datamaster/survei/Hasil', [
            'survei' => $survei,
        ]);
    }

    public function destroy($id)
    {
        $survei = MasterSurvei::findOrFail($id);
        $survei->delete();

        return redirect()->back()->with('success', 'Survei berhasil dihapus.');
    }
}
