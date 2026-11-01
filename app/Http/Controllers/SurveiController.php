<?php

namespace App\Http\Controllers;

use App\Models\MasterSurvei;
use App\Models\ResponSurvei;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SurveiController extends Controller
{
    // 1. Menampilkan daftar survei (Portal Survei Umum)
    public function index()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        // Ambil nama-nama tier dari role user
        $userTiers = $user ? $user->roles()
            ->join('role_tiers', 'roles.role_tier_id', '=', 'role_tiers.id')
            ->pluck('role_tiers.nama_tier')
            ->unique()
            ->toArray() : [];

        $surveis = MasterSurvei::with(['pertanyaans', 'channel'])
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_mulai')->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', $today);
            })
            ->get()
            ->filter(function ($survei) use ($userTiers) {
                if (empty($survei->target_roles)) return true;
                return count(array_intersect($userTiers, $survei->target_roles)) > 0;
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

    // 2. Menampilkan SATU survei spesifik (Saat diklik dari Dashboard Channel)
    public function show(Request $request, $id)
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $survei = MasterSurvei::with(['pertanyaans', 'channel'])->findOrFail($id);

        // Validasi Hak Akses Tier User
        $userTiers = $user ? $user->roles()
            ->join('role_tiers', 'roles.role_tier_id', '=', 'role_tiers.id')
            ->pluck('role_tiers.nama_tier')
            ->unique()
            ->toArray() : [];

        $redirectUrl = $survei->channel_id ? '/dashboard?channel=' . $survei->channel->slug : '/dashboard';

        if (!empty($survei->target_roles) && count(array_intersect($userTiers, $survei->target_roles)) === 0) {
            return redirect($redirectUrl)->with('error', 'Akun Anda tidak memiliki hak akses untuk mengisi survei ini.');
        }

        // Tentukan apakah membuka respon lama (edit) atau formulir baru
        $action = $request->query('action', 'edit');
        if ($survei->batas_pengisian === 'sekali') {
            $action = 'edit';
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

    // 3. Menyimpan jawaban survei baru
    public function store(Request $request, $id)
    {
        $user = auth()->user();
        $survei = MasterSurvei::with(['pertanyaans', 'channel'])->findOrFail($id);

        $redirectUrl = $survei->channel_id ? '/dashboard?channel=' . $survei->channel->slug : '/dashboard';

        if ($survei->batas_pengisian === 'sekali') {
            $alreadyExists = ResponSurvei::where('survei_id', $survei->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyExists) {
                return redirect($redirectUrl)->with('error', 'Anda sudah pernah mengisi survei ini. Silakan gunakan opsi edit.');
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