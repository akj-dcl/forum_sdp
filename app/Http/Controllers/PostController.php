<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Create a new post / broadcast.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'channel_id' => 'nullable|exists:channels,id',
            'attachment' => 'nullable|file|max:51200', // max 50MB
            'link_url' => 'nullable|string',
            'link_title' => 'nullable|string',
            'sticker_url' => 'nullable|string'
        ]);

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;
        $attachmentSize = null;

        if ($request->filled('link_url')) {
            $attachmentPath = $request->link_url;
            $attachmentName = $request->input('link_title') ?: $request->link_url;
            $attachmentType = 'link';
        } elseif ($request->filled('sticker_url')) {
            $attachmentPath = $request->sticker_url;
            $attachmentName = basename($request->sticker_url);
            $attachmentType = 'image';
        } elseif ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            // Store file under standard public disk
            $attachmentPath = $file->store('attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
            
            // Format size string
            $bytes = $file->getSize();
            if ($bytes >= 1073741824) {
                $attachmentSize = number_format($bytes / 1073741824, 1) . ' GB';
            } elseif ($bytes >= 1048576) {
                $attachmentSize = number_format($bytes / 1048576, 1) . ' MB';
            } elseif ($bytes >= 1024) {
                $attachmentSize = number_format($bytes / 1024, 1) . ' KB';
            } else {
                $attachmentSize = $bytes . ' B';
            }

            // Determine mime type classification
            $mime = $file->getMimeType();
            if (str_starts_with($mime, 'image/')) {
                $attachmentType = 'image';
            } elseif (str_starts_with($mime, 'video/')) {
                $attachmentType = 'video';
            } elseif (str_starts_with($mime, 'audio/')) {
                $attachmentType = 'audio';
            } else {
                $attachmentType = 'file';
            }
        }

        Post::create([
            'user_id' => auth()->id(),
            'channel_id' => $request->channel_id,
            'content' => $request->content,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
            'attachment_size' => $attachmentSize
        ]);

        return redirect()->back();
    }

    /**
     * Toggle reaction (like or bulb) on a post.
     */
    public function toggleReaction(Request $request, Post $post)
    {
        $request->validate([
            'type' => 'required|string|in:like,bulb'
        ]);

        $userId = auth()->id();
        $type = $request->type;

        $reaction = PostReaction::where('user_id', $userId)
            ->where('post_id', $post->id)
            ->where('type', $type)
            ->first();

        if ($reaction) {
            $reaction->delete();
        } else {
            // Keep unique constraint safe by catching duplicate tries
            PostReaction::firstOrCreate([
                'user_id' => $userId,
                'post_id' => $post->id,
                'type' => $type
            ]);
        }

        return redirect()->back();
    }

    /**
     * Add a comment to a post.
     */
    public function storeComment(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $post->id,
            'content' => $request->content
        ]);

        return redirect()->back();
    }

    /**
     * Update an existing post.
     */
    public function update(Request $request, Post $post)
    {
        // Otorisasi: hanya pemilik postingan atau Super Admin
        if (auth()->id() !== $post->user_id && !auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:51200', // max 50MB
            'link_url' => 'nullable|string',
            'link_title' => 'nullable|string',
            'sticker_url' => 'nullable|string',
            'delete_attachment' => 'nullable|boolean'
        ]);

        $updateData = [
            'content' => $request->content
        ];

        // 1. Delete attachment if requested
        if ($request->delete_attachment == true) {
            if ($post->attachment_path && !str_starts_with($post->attachment_path, 'http')) {
                \Storage::disk('public')->delete($post->attachment_path);
            }
            $updateData['attachment_path'] = null;
            $updateData['attachment_name'] = null;
            $updateData['attachment_type'] = null;
            $updateData['attachment_size'] = null;
        }

        // 2. Process new attachment
        if ($request->filled('link_url')) {
            // Delete old file if existed
            if ($post->attachment_path && !str_starts_with($post->attachment_path, 'http')) {
                \Storage::disk('public')->delete($post->attachment_path);
            }
            $updateData['attachment_path'] = $request->link_url;
            $updateData['attachment_name'] = $request->input('link_title') ?: $request->link_url;
            $updateData['attachment_type'] = 'link';
            $updateData['attachment_size'] = null;
        } elseif ($request->filled('sticker_url')) {
            // Delete old file if existed
            if ($post->attachment_path && !str_starts_with($post->attachment_path, 'http')) {
                \Storage::disk('public')->delete($post->attachment_path);
            }
            $updateData['attachment_path'] = $request->sticker_url;
            $updateData['attachment_name'] = basename($request->sticker_url);
            $updateData['attachment_type'] = 'image';
            $updateData['attachment_size'] = null;
        } elseif ($request->hasFile('attachment')) {
            // Delete old file if existed
            if ($post->attachment_path && !str_starts_with($post->attachment_path, 'http')) {
                \Storage::disk('public')->delete($post->attachment_path);
            }
            
            $file = $request->file('attachment');
            $attachmentPath = $file->store('attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
            
            // Format size string
            $bytes = $file->getSize();
            if ($bytes >= 1073741824) {
                $attachmentSize = number_format($bytes / 1073741824, 1) . ' GB';
            } elseif ($bytes >= 1048576) {
                $attachmentSize = number_format($bytes / 1048576, 1) . ' MB';
            } elseif ($bytes >= 1024) {
                $attachmentSize = number_format($bytes / 1024, 1) . ' KB';
            } else {
                $attachmentSize = $bytes . ' B';
            }

            // Determine mime type classification
            $mime = $file->getMimeType();
            if (str_starts_with($mime, 'image/')) {
                $attachmentType = 'image';
            } elseif (str_starts_with($mime, 'video/')) {
                $attachmentType = 'video';
            } elseif (str_starts_with($mime, 'audio/')) {
                $attachmentType = 'audio';
            } else {
                $attachmentType = 'file';
            }

            $updateData['attachment_path'] = $attachmentPath;
            $updateData['attachment_name'] = $attachmentName;
            $updateData['attachment_type'] = $attachmentType;
            $updateData['attachment_size'] = $attachmentSize;
        }

        $post->update($updateData);

        return redirect()->back();
    }

    /**
     * Delete a post.
     */
    public function destroy(Post $post)
    {
        // Otorisasi: hanya pemilik postingan atau Super Admin
        if (auth()->id() !== $post->user_id && !auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }

        // Hapus file lampiran jika ada
        if ($post->attachment_path) {
            Storage::disk('public')->delete($post->attachment_path);
        }

        $post->delete();

        return redirect()->back();
    }

    /**
     * Update a comment.
     */
    public function updateComment(Request $request, Comment $comment)
    {
        // Otorisasi: hanya pemilik komentar atau Super Admin
        if (auth()->id() !== $comment->user_id && !auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update([
            'content' => $request->content
        ]);

        return redirect()->back();
    }

    /**
     * Delete a comment.
     */
    public function destroyComment(Comment $comment)
    {
        // Otorisasi: pemilik komentar, pemilik postingan tempat komentar berada, atau Super Admin
        if (auth()->id() !== $comment->user_id && 
            auth()->id() !== $comment->post->user_id && 
            !auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }

        $comment->delete();

        return redirect()->back();
    }
}
