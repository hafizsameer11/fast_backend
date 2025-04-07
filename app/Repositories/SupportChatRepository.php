<?php

namespace App\Repositories;
use App\Models\SupportChat;

class SupportChatRepository
{
    public function all()
    {
        // Add logic to fetch all data
    }

    public function find($id)
    {
        // Add logic to find data by ID
    }

    public function create(array $data)
    {
        // Add logic to create data
    }

    public function update($id, array $data)
    {
        // Add logic to update data
    }

    public function delete($id)
    {
        // Add logic to delete data
    }
    public function createMessage(array $data)
    {
        return SupportChat::create($data);
    }

    public function getMessages($senderId, $receiverId)
    {
        return SupportChat::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)->where('receiver_id', $receiverId)
                ->orWhere('sender_id', $receiverId)->where('receiver_id', $senderId);
        })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function replyToMessage($messageId, $message)
    {
        $chat = SupportChat::findOrFail($messageId);
        $chat->message = $message;
        $chat->status = 'replied';
        $chat->save();

        return $chat;
    }
    public function getByUserId($userId)
    {
        return SupportChat::where('user_id', $userId)->get();
    }
}