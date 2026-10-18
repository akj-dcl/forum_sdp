<?php

namespace App\Http\Controllers;

use App\Models\MasterSurvei;
use App\Models\ResponSurvei;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SurveiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userRoles = $user->getRoleNames()->toArray();
        $today = now()->toDateString();

        $surveis = MasterSurvei::with(['pertanyaans'])
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_mulai')->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', $today);
            })
            ->get()
            ->filter(function ($survei) use ($userRoles) {
                if (empty($survei->target_roles)) return true;
                return count(array_intersect($userRoles, $survei->target_roles)) > 0;
            })
            ->map(function ($survei) use ($user, $today) {
                $respon = ResponSurvei::where('survei_id', $survei->id)
                    ->where('user_id', $user->id)
                    ->latest()
                    ->first();

                $isExpired = $survei->tanggal_selesai && $today > $survei->tanggal_selesai->toDateString();

                return [
                    'id' => $survei->id,
                    'judul' => $survei->judul,
                    'deskripsi' => $survei->deskripsi,
                    'batas_pengisian' => $survei->batas_pengisian,
                    'tanggal_mulai' => $survei->tanggal_mulai?->format('d M Y'),
                    'tanggal_selesai' => $survei->tanggal_selesai?->format('d M Y'),
                    'total_pertanyaan' => $survei->pertanyaans->count(),
                    'pertanyaans' => $survei->pertanyaans,
                    'sudah_diisi' => $respon !== null,
                    'existingResponse' => $respon ? [
                        'id' => $respon->id,
                        'jawaban' => $respon->jawaban ?? [],
                        'tanggal_submit' => $respon->created_at?->format('d M Y, H:i'),
                        'tanggal_update' => $respon->updated_at?->format('d M Y, H:i'),
                    ] : null,
                    'isReadOnly' => (bool) $isExpired,
                ];
            })
            ->values();

        return Inertia::render('Survei', [
            'surveis' => $surveis,
        ]);
    }

    public function store(Request $request, $id)
    {
        $user = auth()->user();
        $survei = MasterSurvei::with(['pertanyaans'])->findOrFail($id);

        if ($survei->batas_pengisian === 'sekali') {
            $alreadyExists = ResponSurvei::where('survei_id', $survei->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyExists) {
                return redirect()->route('survei.index')->with('error', 'Anda sudah pernah mengisi survei ini.');
            }
        }

        $jawabanInput = $request->input('jawaban', []);
        $soalDilewati = $request->input('soal_dilewati', []);
        $jawabanFinal = [];

        foreach ($survei->pertanyaans as $pertanyaan) {
            $qId = $pertanyaan->id;

            if (in_array($qId, $soalDilewati)) {
                $jawabanFinal[$qId] = null;
                continue;
            }

            if ($pertanyaan->tipe_jawaban === 'foto') {
                if ($request->hasFile("jawaban_foto.{$qId}")) {
                    $file = $request->file("jawaban_foto.{$qId}");
                    $path = $file->store('survei_lampiran', 'public');
                    $jawabanFinal[$qId] = asset('storage/' . $path);
                } else {
                    $jawabanFinal[$qId] = null;
                }
            } else {
                $jawabanFinal[$qId] = $jawabanInput[$qId] ?? null;
            }

            if ($pertanyaan->wajib_diisi && ($jawabanFinal[$qId] === null || $jawabanFinal[$qId] === '')) {
                return redirect()->back()->withErrors(["jawaban.{$qId}" => "Pertanyaan ini wajib diisi."]);
            }
        }

        ResponSurvei::create([
            'survei_id' => $survei->id,
            'user_id' => $user->id,
            'upt_id' => $user->upt_id ?? 1,
            'jawaban' => $jawabanFinal,
        ]);

        return redirect()->route('survei.index')->with('success', 'Jawaban survei Anda berhasil disimpan!');
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $survei = MasterSurvei::with(['pertanyaans'])->findOrFail($id);

        $respon = ResponSurvei::where('survei_id', $survei->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $today = now()->toDateString();
        if ($survei->tanggal_selesai && $today > $survei->tanggal_selesai->toDateString()) {
            return redirect()->route('survei.index')->with('error', 'Tenggat waktu telah berakhir.');
        }

        $jawabanInput = $request->input('jawaban', []);
        $soalDilewati = $request->input('soal_dilewati', []);
        $jawabanLama = $respon->jawaban ?: [];
        $jawabanFinal = [];

        foreach ($survei->pertanyaans as $pertanyaan) {
            $qId = $pertanyaan->id;

            if (in_array($qId, $soalDilewati)) {
                $jawabanFinal[$qId] = null;
                continue;
            }

            if ($pertanyaan->tipe_jawaban === 'foto') {
                if ($request->hasFile("jawaban_foto.{$qId}")) {
                    $file = $request->file("jawaban_foto.{$qId}");
                    $path = $file->store('survei_lampiran', 'public');
                    $jawabanFinal[$qId] = asset('storage/' . $path);
                } elseif (isset($jawabanLama[$qId]) && !empty($jawabanLama[$qId])) {
                    $jawabanFinal[$qId] = $jawabanLama[$qId];
                } else {
                    $jawabanFinal[$qId] = null;
                }
            } else {
                $jawabanFinal[$qId] = $jawabanInput[$qId] ?? null;
            }

            if ($pertanyaan->wajib_diisi && ($jawabanFinal[$qId] === null || $jawabanFinal[$qId] === '')) {
                return redirect()->back()->withErrors(["jawaban.{$qId}" => "Pertanyaan ini wajib diisi."]);
            }
        }

        $respon->update([
            'jawaban' => $jawabanFinal,
        ]);

        return redirect()->route('survei.index')->with('success', 'Jawaban survei Anda berhasil diperbarui!');
    }
}
