<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveiLayanan;
use App\Models\Upt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SurveiController extends Controller
{
    public function index(Request $request)
    {
        $query = SurveiLayanan::with(['user', 'upt.kanwil'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_layanan', 'like', "%{$search}%")
                        ->orWhereHas('upt', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($usr) use ($search) {
                            $usr->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->layanan, function ($q, $layanan) {
                $q->where('nama_layanan', $layanan);
            })
            ->when($request->status !== null && $request->status !== '', function ($q) use ($request) {
                $q->where('status_berjalan', $request->status == '1');
            })
            ->when($request->kondisi, function ($q, $kondisi) {
                $q->where('status_perangkat', $kondisi);
            });

        $totalSurvei = SurveiLayanan::count();
        $totalBerjalan = SurveiLayanan::where('status_berjalan', true)->count();
        $totalTidakBerjalan = SurveiLayanan::where('status_berjalan', false)->count();
        $totalIdeal = SurveiLayanan::where('status_perangkat', 'Ideal')->count();
        $totalTidakIdeal = SurveiLayanan::where('status_perangkat', 'Tidak Ideal')->count();

        $surveys = $query->latest()->paginate(10)->withQueryString();

        $surveys->getCollection()->transform(function ($item) {
            $photos = [];
            if ($item->foto_perangkat) {
                $rawPhotos = is_array($item->foto_perangkat) ? $item->foto_perangkat : json_decode($item->foto_perangkat, true);
                if (is_array($rawPhotos)) {
                    foreach ($rawPhotos as $path) {
                        $photos[] = asset('storage/' . $path);
                    }
                } elseif (is_string($item->foto_perangkat)) {
                    $photos[] = asset('storage/' . $item->foto_perangkat);
                }
            }

            return [
                'id' => $item->id,
                'nama_layanan' => $item->nama_layanan,
                'status_berjalan' => (bool) $item->status_berjalan,
                'sarana' => $item->sarana ?? '-',
                'status_kelengkapan' => $item->status_kelengkapan ?? '-',
                'periode_pengadaan' => $item->periode_pengadaan ?? '-',
                'jumlah_perangkat' => $item->jumlah_perangkat ?? 0,
                'status_perangkat' => $item->status_perangkat ?? '-',
                'foto_urls' => $photos,
                'tanggal' => $item->created_at ? $item->created_at->format('d M Y, H:i') : '-',
                'petugas' => [
                    'name' => $item->user->name ?? 'User Terhapus',
                    'nip' => $item->user->nip ?? '-',
                ],
                'upt' => [
                    'name' => $item->upt->name ?? 'Pusat / Kanwil',
                    'kanwil' => $item->upt->kanwil->name ?? '-',
                ],
            ];
        });

        return Inertia::render('admin/survei/index', [
            'surveys' => $surveys,
            'stats' => [
                'total' => $totalSurvei,
                'berjalan' => $totalBerjalan,
                'tidak_berjalan' => $totalTidakBerjalan,
                'ideal' => $totalIdeal,
                'tidak_ideal' => $totalTidakIdeal,
            ],
            'filters' => $request->only(['search', 'layanan', 'status', 'kondisi']),
        ]);
    }

    public function destroy($id)
    {
        $survei = SurveiLayanan::findOrFail($id);
        if ($survei->foto_perangkat) {
            $photos = is_array($survei->foto_perangkat)
                ? $survei->foto_perangkat
                : json_decode($survei->foto_perangkat, true);

            if (is_array($photos)) {
                foreach ($photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
        }

        $survei->delete();

        return redirect()->back()->with('success', 'Data hasil survei berhasil dihapus.');
    }
}
