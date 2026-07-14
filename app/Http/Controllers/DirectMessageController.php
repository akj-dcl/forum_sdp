<?php

namespace App\Http\Controllers;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DirectMessageController extends Controller
{
    public function index()
    {
        return Inertia::render('DirectMessages');
    }
    public function getContacts()
    {
        $currentUserId = auth()->id();
        
        $users = User::with(['upt', 'kanwil'])
            ->where('id', '!=', $currentUserId)
            ->get()
            ->map(function ($user) use ($currentUserId) {
                $lastMessage = DirectMessage::where(function ($q) use ($currentUserId, $user) {
                        $q->where('sender_id', $currentUserId)->where('receiver_id', $user->id);
                    })
                    ->orWhere(function ($q) use ($currentUserId, $user) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $currentUserId);
                    })
                    ->latest()
                    ->first();

                $unreadCount = DirectMessage::where('sender_id', $user->id)
                    ->where('receiver_id', $currentUserId)
                    ->where('is_read', false)
                    ->count();

                $isOnline = $user->last_seen_at && $user->last_seen_at >= now()->subMinutes(5);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->upt?->name ?? ($user->kanwil ? 'Kanwil ' . $user->kanwil->name : 'Kantor Pusat'),
                    'avatar' => $user->avatar_url,
                    'status' => $isOnline ? 'online' : 'offline',
                    'last_message' => $lastMessage ? $lastMessage->message : null,
                    'last_message_time' => $lastMessage ? $lastMessage->created_at->diffForHumans() : null,
                    'unread_count' => $unreadCount
                ];
            })
            ->sortByDesc(function ($contact) {
                return [$contact['status'] === 'online' ? 1 : 0, $contact['last_message_time'] ? 1 : 0];
            })
            ->values();

        return response()->json($users);
    }
    public function getHistory($userId)
    {
        $currentUserId = auth()->id();

        DirectMessage::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = DirectMessage::where(function ($q) use ($currentUserId, $userId) {
                $q->where('sender_id', $currentUserId)->where('receiver_id', $userId);
            })
            ->orWhere(function ($q) use ($currentUserId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $currentUserId);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($currentUserId) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'receiver_id' => $msg->receiver_id,
                    'message' => $msg->message,
                    'is_mine' => $msg->sender_id === $currentUserId,
                    'time' => $msg->created_at->format('H:i')
                ];
            });

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        $msg = DirectMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'receiver_id' => $msg->receiver_id,
                'message' => $msg->message,
                'is_mine' => true,
                'time' => $msg->created_at->format('H:i')
            ]
        ]);
    }

    public function markAsRead($senderId)
    {
        DirectMessage::where('sender_id', $senderId)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
