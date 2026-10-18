<?php

namespace App\Http\Controllers;

use App\Models\MasterSurvei;
use App\Models\ResponSurvei;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SurveiController extends Controller
{
    // 1. Menampilkan daftar semua survei (saat diakses via menu Sidebar)
    public function index()
    {
        $user = auth()->user();
        $userRoles = $user->getRoleNames()->toArray();
        $today = now()->toDateString();

        $surveis = MasterSurvei::with(['pertanyaans', 'channel'])
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

    public function show(Request $request, $id) // <-- Tambahkan Request $request
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $survei = MasterSurvei::with(['pertanyaans', 'channel'])->findOrFail($id);

        $action = $request->query('action', 'edit'); // default 'edit' jika tidak ada param
        if ($survei->batas_pengisian === 'sekali') {
            $action = 'edit'; // Paksa ke edit jika settingannya hanya 1x
        }

        $respon = null;
        if ($action === 'edit') {
            $respon = ResponSurvei::where('survei_id', $survei->id)
                ->where('user_id', $user->id)
                ->latest()
                ->first();
        }

        $isExpired = $survei->tanggal_selesai && $today > $survei->tanggal_selesai->toDateString();

        $formattedSurvei = [
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

        return Inertia::render('Survei', [
            'surveis' => [$formattedSurvei],
        ]);
    }

    public function store(Request $request, $id)
    {
        $user = auth()->user();
        $survei = MasterSurvei::with(['pertanyaans', 'channel'])->findOrFail($id);

        // Agar setelah submit, user dikembalikan ke Channel asal
        $redirectUrl = $survei->channel_id ? '/dashboard?channel=' . $survei->channel->slug : '/dashboard';

        if ($survei->batas_pengisian === 'sekali') {
            $alreadyExists = ResponSurvei::where('survei_id', $survei->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyExists) {
                return redirect($redirectUrl)->with('error', 'Anda sudah pernah mengisi survei ini.');
            }
        }

        $jawabanInput = $request->input('jawaban', []);
        $soalDilewati = $request->input('soal_dilewati', []);
        $jawabanFinal = [];

        foreach ($survei->pertanyaans as $pertanyaan) {
            $qId = $pertanyaan->id;

            // Logika Skip / Percabangan
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

            // Validasi soal wajib diisi
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

        return redirect($redirectUrl)->with('success', 'Jawaban survei Anda berhasil disimpan!');
    }

    // 4. Memperbarui jawaban yang sudah pernah dikirim
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $survei = MasterSurvei::with(['pertanyaans', 'channel'])->findOrFail($id);
        $redirectUrl = $survei->channel_id ? '/dashboard?channel=' . $survei->channel->slug : '/dashboard';

        $respon = ResponSurvei::where('survei_id', $survei->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $today = now()->toDateString();
        if ($survei->tanggal_selesai && $today > $survei->tanggal_selesai->toDateString()) {
            return redirect($redirectUrl)->with('error', 'Tenggat waktu telah berakhir. Jawaban tidak dapat diubah lagi.');
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

        return redirect($redirectUrl)->with('success', 'Jawaban survei Anda berhasil diperbarui!');
    }
}
