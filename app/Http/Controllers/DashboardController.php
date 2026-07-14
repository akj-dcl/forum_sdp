<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Post;
use App\Models\User;
use App\Models\CorporateHighlight;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $channels = Channel::orderBy('name')->get();

        // 1. Fetch filtered posts based on active channel query
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
            ->latest()
            ->get()
            ->map(function ($post) {
                $userId = auth()->id();
                
                // Group reactions by type and count them
                $likesCount = $post->reactions->where('type', 'like')->count();
                $bulbsCount = $post->reactions->where('type', 'bulb')->count();
                
                // Check if currently authenticated user reacted
                $userLiked = $post->reactions->where('user_id', $userId)->where('type', 'like')->isNotEmpty();
                $userBulbed = $post->reactions->where('user_id', $userId)->where('type', 'bulb')->isNotEmpty();
                
                return [
                    'id' => $post->id,
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

        // 2. Fetch active users (online within 5 min, idle within 15 min)
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

        // 3. Fetch corporate highlights
        $highlights = CorporateHighlight::latest()->take(5)->get()->map(function ($h) {
            return [
                'id' => $h->id,
                'title' => $h->title,
                'description' => $h->description,
                'link' => $h->link ?? '#'
            ];
        });

        return Inertia::render('Dashboard', [
            'posts' => $posts,
            'channels' => $channels,
            'directoryUsers' => $directoryUsers,
            'activeChannel' => $activeChannel,
            'highlights' => $highlights
        ]);
    }
}
