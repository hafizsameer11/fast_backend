<?php

namespace App\Repositories;

use App\Models\Chat;
use Illuminate\Support\Facades\DB;

class ChatRepository
{
    public function create(array $data)
    {
        return Chat::create($data);
    }
    public function getConversationBetweenUsers($userId, $receiverId)
    {
        return Chat::where(function ($query) use ($userId, $receiverId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $receiverId);
            })
            ->orWhere(function ($query) use ($userId, $receiverId) {
                $query->where('sender_id', $receiverId)
                      ->where('receiver_id', $userId);
            })
            ->orderBy('sent_at')
            ->get();
    }

    public function getConversationWithUser($otherUserId, $authUserId)
    {
        return Chat::where(function ($q) use ($otherUserId, $authUserId) {
            $q->where('sender_id', $authUserId)
                ->where('receiver_id', $otherUserId);
        })->orWhere(function ($q) use ($otherUserId, $authUserId) {
            $q->where('sender_id', $otherUserId)
                ->where('receiver_id', $authUserId);
        })->orderBy('sent_at')->get();
    }

    public function getInboxByUser($authUserId)
    {
        $chats = Chat::where('sender_id', $authUserId)
            ->orWhere('receiver_id', $authUserId)
            ->with(['sender:id,name,profile_picture', 'receiver:id,name,profile_picture'])
            ->orderByDesc('sent_at')
            ->get()
            ->groupBy(function ($chat) use ($authUserId) {
                return $chat->sender_id == $authUserId ? $chat->receiver_id : $chat->sender_id;
            });

        return $chats;
    }

    public function all()
    {
        // Add logic to fetch all data
    }

    public function find($id)
    {
        // Add logic to find data by ID
    }

    public function update($id, array $data)
    {
        // Add logic to update data
    }

    public function delete($id)
    {
        // Add logic to delete data
    }
}
