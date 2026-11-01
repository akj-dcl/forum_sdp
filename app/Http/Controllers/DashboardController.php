<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Post;
use App\Models\User;
use App\Models\CorporateHighlight;
use App\Models\MasterSurvei;
use App\Models\ResponSurvei;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $channels = Channel::orderBy('name')->get();

        $channelSlug = $request->query('channel');
        $activeChannel = null;
        if ($channelSlug) {
            $activeChannel = Channel::where('slug', $channelSlug)->first();
        }

        $posts = Post::with([
            'user.upt',
            'user.kanwil',
            'channel',
            'comments.user',
            'reactions'
        ])
            ->when($activeChannel, function ($query) use ($activeChannel) {
                $query->where('channel_id', $activeChannel->id);
            })
            ->orderByDesc('is_pinned')
            ->latest()
            ->get()
            ->map(function ($post) {
                $userId = auth()->id();

                $likesCount = $post->reactions->where('type', 'like')->count();
                $bulbsCount = $post->reactions->where('type', 'bulb')->count();

                $userLiked = $post->reactions->where('user_id', $userId)->where('type', 'like')->isNotEmpty();
                $userBulbed = $post->reactions->where('user_id', $userId)->where('type', 'bulb')->isNotEmpty();

                return [
                    'id' => $post->id,
                    'is_pinned' => (bool) $post->is_pinned,
                    'content' => $post->content,
                    'time' => $post->created_at->diffForHumans(),
                    'category' => $post->channel?->name ?? 'General Feed',
                    'channel_slug' => $post->channel?->slug,
                    'author_id' => $post->user_id,
                    'author' => [
                        'name' => $post->user->name,
                        'role' => $post->user->upt?->name ?? ($post->user->kanwil ? 'Kanwil ' . $post->user->kanwil->name : 'Kantor Pusat'),
                        'avatar' => $post->user->avatar_url
                    ],
                    'attachment' => $post->attachment_path ? [
                        'name' => $post->attachment_name,
                        'path' => ($post->attachment_type === 'link' || str_starts_with($post->attachment_path, 'http')) ? $post->attachment_path : asset('storage/' . $post->attachment_path),
                        'type' => $post->attachment_type,
                        'size' => $post->attachment_size
                    ] : null,
                    'likes' => $likesCount,
                    'bulbs' => $bulbsCount,
                    'liked' => $userLiked,
                    'bulbed' => $userBulbed,
                    'comments' => $post->comments->map(function ($comment) {
                        return [
                            'id' => $comment->id,
                            'author_id' => $comment->user_id,
                            'author' => [
                                'name' => $comment->user->name,
                                'avatar' => $comment->user->avatar_url
                            ],
                            'time' => $comment->created_at->diffForHumans(),
                            'content' => $comment->content
                        ];
                    })
                ];
            });

        $directoryUsers = User::with(['upt', 'kanwil'])
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', now()->subMinutes(15))
            ->orderBy('last_seen_at', 'desc')
            ->get()
            ->map(function ($user) {
                $isOnline = $user->last_seen_at >= now()->subMinutes(5);
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->upt?->name ?? ($user->kanwil ? 'Kanwil ' . $user->kanwil->name : 'Kantor Pusat'),
                    'avatar' => $user->avatar_url,
                    'status' => $isOnline ? 'online' : 'idle'
                ];
            });

        $highlights = CorporateHighlight::latest()->take(5)->get()->map(function ($h) {
            return [
                'id' => $h->id,
                'title' => $h->title,
                'description' => $h->description,
                'link' => $h->link ?? '#'
            ];
        });

        // --- FILTER SURVEI CHANNEL BERDASARKAN 4 ROLE TIER ---
        $channelId = $activeChannel ? $activeChannel->id : null;
        $user = auth()->user();
        $today = now()->toDateString();

        // Ambil nama-nama tier dari seluruh role milik user (Super Admin, Admin, Supervisor, Operator)
        $userTiers = $user ? $user->roles()
            ->join('role_tiers', 'roles.role_tier_id', '=', 'role_tiers.id')
            ->pluck('role_tiers.nama_tier')
            ->unique()
            ->toArray() : [];

        $activeSurveis = MasterSurvei::where('channel_id', $channelId)
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_mulai')->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', $today);
            })
            ->get()
            // Cocokkan target tier survei dengan tier user
            ->filter(function ($survei) use ($userTiers) {
                if (empty($survei->target_roles)) return true; // Jika kosong, semua tier boleh akses
                return count(array_intersect($userTiers, $survei->target_roles)) > 0;
            })
            ->map(function ($survei) use ($user) {
                $respon = ResponSurvei::where('survei_id', $survei->id)
                    ->where('user_id', $user->id)
                    ->latest()
                    ->first();

                return [
                    'id' => $survei->id,
                    'judul' => $survei->judul,
                    'batas_pengisian' => $survei->batas_pengisian,
                    'sudah_diisi' => $respon !== null,
                    'tanggal_selesai' => $survei->tanggal_selesai?->format('d M Y'),
                ];
            })->values();

        return Inertia::render('Dashboard', [
            'posts' => $posts,
            'channels' => $channels,
            'directoryUsers' => $directoryUsers,
            'activeChannel' => $activeChannel,
            'highlights' => $highlights,
            'activeSurveis' => $activeSurveis,
        ]);
    }
    public function togglePin(Post $post)
    {
        $post->update([
            'is_pinned' => !$post->is_pinned,
        ]);

        return back();
    }
}
