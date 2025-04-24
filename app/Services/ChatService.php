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
        return \App\Models\SendParcel::where('rider_id', $riderId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function getUsersConnectedToRider($riderId)
    {
        return \App\Models\User::whereIn('id', function ($query) use ($riderId) {
            $query->select('user_id')
                ->from('send_parcels')
                ->where('rider_id', $riderId);
        })->get(['id', 'name', 'email', 'phone']);
    }
    public function getRidersConnectedToUser($userId)
    {
        return \App\Models\User::whereIn('id', function ($query) use ($userId) {
            $query->select('rider_id')
                  ->from('send_parcels')
                  ->where('user_id', $userId);
        })->get(['id', 'name', 'email', 'phone']);
    }
}
