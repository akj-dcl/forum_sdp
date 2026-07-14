<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(User $user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }

        $user->load(['upt', 'kanwil', 'jenisGolongan']);

        $posts = Post::with(['user.upt', 'user.kanwil', 'channel', 'reactions', 'comments.user'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($post) {
                $likesCount = $post->reactions->where('type', 'like')->count();
                $bulbsCount = $post->reactions->where('type', 'bulb')->count();
                
                $userId = auth()->id();
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

        $comments = Comment::with('post')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'time' => $comment->created_at->diffForHumans(),
                    'post_id' => $comment->post_id,
                    'post_snippet' => Str::limit($comment->post?->content ?? '', 60)
                ];
            });

        $profileUser = [
            'id' => $user->id,
            'name' => $user->name,
            'nip' => $user->nip,
            'jabatan' => $user->jabatan,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'banner_url' => $user->banner_url,
            'upt' => $user->upt?->name,
            'kanwil' => $user->kanwil?->name,
            'pangkat' => $user->jenisGolongan?->name,
            'is_own_profile' => $user->id === auth()->id()
        ];

        return Inertia::render('Profile', [
            'profileUser' => $profileUser,
            'posts' => $posts,
            'comments' => $comments
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $user = auth()->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);

        return redirect()->back();
    }

    public function uploadBanner(Request $request)
    {
        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        $user = auth()->user();

        if ($user->banner_path) {
            Storage::disk('public')->delete($user->banner_path);
        }

        $path = $request->file('banner')->store('banners', 'public');
        $user->update(['banner_path' => $path]);

        return redirect()->back();
    }
}
