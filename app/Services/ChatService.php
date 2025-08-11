<?php

namespace App\Services;

use App\Repositories\ChatRepository;
use Illuminate\Support\Facades\Auth;

class ChatService
{
    protected $chatRepository;

    public function __construct(ChatRepository $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function all()
    {
        return $this->chatRepository->all();
    }

    public function find($id)
    {
        return $this->chatRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->chatRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->chatRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->chatRepository->delete($id);
    }
    public function sendMessage(array $data)
    {
        $data['sender_id'] = Auth::id();
        $data['sent_at'] = now();
        return $this->chatRepository->create($data);
    }

    public function getMessagesWithUser($userId)
    {
        return $this->chatRepository->getConversationWithUser($userId, Auth::id());
    }

    public function getInbox()
    {
        return $this->chatRepository->getInboxByUser(Auth::id());
    }

    public function isRiderConnectedToUser($riderId, $userId)
    {
        return \App\Models\SendParcel::where(function ($query) use ($riderId, $userId) {
            $query->where('rider_id', $riderId)
                ->where('user_id', $userId);
        })
            ->orWhere(function ($query) use ($riderId, $userId) {
                $query->where('rider_id', $userId)
                    ->where('user_id', $riderId);
            })
            ->exists();
    }


    public function getUsersConnectedToRider($riderId)
    {
        return \App\Models\User::whereIn('id', function ($query) use ($riderId) {
            $query->select('user_id')
                ->from('send_parcels')
                ->where('rider_id', $riderId);
        })->get(['id', 'name', 'email', 'phone', 'profile_picture']);
    }
    public function getRidersConnectedToUser($userId)
    {
        $riders = \App\Models\User::whereIn('id', function ($query) use ($userId) {
            $query->select('rider_id')
                ->from('send_parcels')
                ->where('user_id', $userId);
        })->get(['id', 'name', 'email', 'phone', 'profile_picture']);

        // Attach last message & unread count
        $riders->map(function ($rider) use ($userId) {
            // Last message
            $lastMessage = \App\Models\Chat::where(function ($query) use ($rider, $userId) {
                $query->where('sender_id', $userId)->where('receiver_id', $rider->id);
            })
                ->orWhere(function ($query) use ($rider, $userId) {
                    $query->where('sender_id', $rider->id)->where('receiver_id', $userId);
                })
                ->latest('sent_at')
                ->first();

            $rider->last_message = $lastMessage ? [
                'message' => $lastMessage->message,
                'sender_id' => $lastMessage->sender_id,
                'sent_at' => $lastMessage->sent_at,
            ] : null;

            // Unread count (messages from rider to user that are not read)
            $unreadCount = \App\Models\Chat::where('sender_id', $rider->id)
                ->where('receiver_id', $userId)
                ->where('is_read', 0)
                ->count();

            $rider->unread_count = $unreadCount;

            return $rider;
        });

        return $riders;
    }


    public function getConversationBetweenUsers($userId, $receiverId)
    {
        return $this->chatRepository->getConversationBetweenUsers($userId, $receiverId);
    }
}
