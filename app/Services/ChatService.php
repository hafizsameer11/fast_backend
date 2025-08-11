<?php

namespace App\Services;

use App\Repositories\ChatRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $file = $data['image'] ?? null;   // <-- get image here
        // unset($data['image']);            // don't persist raw file

        $data['sender_id'] = Auth::id();
        $data['sent_at']   = now();
        $data['is_read']   = 0;

        if ($file) {
            // store on public disk: storage/app/public/chat_uploads/YYYY/MM/...
            $path = $file->store('chat_uploads/'.date('Y/m'), 'public');

            // /storage/... -> absolute URL
            $data['image']    = url(Storage::url($path));
            $data['image_path']   = $path;           // optional but useful
            $data['message_type'] = 'image';
            $data['message']      = $data['message'] ?? null; // allow image-only
        } else {
            $data['message_type'] = 'text';
        }

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

    $riders->map(function ($rider) use ($userId) {
        // Fetch last message between user <-> rider
        $lastMessage = \App\Models\Chat::where(function ($q) use ($rider, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $rider->id);
            })
            ->orWhere(function ($q) use ($rider, $userId) {
                $q->where('sender_id', $rider->id)->where('receiver_id', $userId);
            })
            ->latest('sent_at')
            ->first();

        // 💡 Fallback text if message is null but image exists
        $lastMessageText = null;
        $isImage = false;
        $imageUrl = null;

        if ($lastMessage) {
            $hasText = isset($lastMessage->message) && trim($lastMessage->message) !== '';
            $hasImage = !empty($lastMessage->image); // adjust if your column is named differently

            if ($hasText) {
                $lastMessageText = $lastMessage->message;
            } elseif ($hasImage) {
                $lastMessageText = 'sent an image';
                $isImage = true;
                $imageUrl = $lastMessage->image; // full URL if you store it; else build with Storage::url(...)
            }
        }

        $rider->last_message = $lastMessage ? [
            'message'    => $lastMessageText,
            'is_image'   => $isImage,
            'image_url'  => $imageUrl,
            'sender_id'  => $lastMessage->sender_id,
            'sent_at'    => $lastMessage->sent_at,
        ] : null;

        // Unread count (from rider -> user)
        $rider->unread_count = \App\Models\Chat::where('sender_id', $rider->id)
            ->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->count();

        return $rider;
    });

    return $riders;
}



    public function getConversationBetweenUsers($userId, $receiverId)
    {
        return $this->chatRepository->getConversationBetweenUsers($userId, $receiverId);
    }
}
