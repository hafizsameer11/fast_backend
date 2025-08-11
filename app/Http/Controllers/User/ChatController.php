<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ChatRequest;
use App\Http\Requests\SendMessageRequest;
use App\Services\ChatService;
use App\Services\SupportChatService;
use App\Helpers\ResponseHelper;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected $chatService;
    protected $supportChatService;

    public function __construct(ChatService $chatService, SupportChatService $supportChatService)
    {
        $this->chatService = $chatService;
        $this->supportChatService = $supportChatService;
    }

    // ✅ Normal user-to-user chat
    public function send(ChatRequest $request)
    {
        try {
            Log::info('Request data: ' . json_encode($request->all()));
            $recipientId = $request->input('receiver_id');
            $riderId = auth()->id();

            if (!$this->chatService->isRiderConnectedToUser($riderId, $recipientId)) {
                return ResponseHelper::error("You are not allowed to chat with this user.");
            }
            if ($recipientId == $riderId) {
                return ResponseHelper::error("You cannot send a message to yourself.");
            }
            $chat = $this->chatService->sendMessage($request->validated());
            return ResponseHelper::success($chat, "Message sent successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function getMessagesWithUser($userId)
    {
        try {
            $riderId = auth()->id();

            if (!$this->chatService->isRiderConnectedToUser($riderId, $userId)) {
                return ResponseHelper::error("Access denied to this conversation.");
            }

            $messages = $this->chatService->getMessagesWithUser($userId);
            return ResponseHelper::success($messages, "Messages retrieved");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function unreadCount(){
       try{
         $user=Auth::user();
        $unreadCount=Chat::where('receiver_id', $user->id)->where('is_read', false)->count();
        return ResponseHelper::success($unreadCount, "Unread count loaded");
       }catch(\Throwable $th){
        return ResponseHelper::error($th->getMessage());
       }
    }
    public function connectedUsers()
    {
        try {
            $riderId = auth()->id();
            $users = $this->chatService->getUsersConnectedToRider($riderId);
            return ResponseHelper::success($users, "Connected users loaded");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }
    public function connectRiders()
    {
        try {
            $userId = auth()->id();
            $users = $this->chatService->getRidersConnectedToUser($userId);
            return ResponseHelper::success($users, "Connected users loaded");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function inbox()
    {
        try {
            $inbox = $this->chatService->getInbox();
            return ResponseHelper::success($inbox, "Inbox loaded");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    // ✅ Send support message (from user/rider to admin)
    public function sendSupport(SendMessageRequest $request)
    {
        try {
            $data = $request->validated();
            $data['sender_id'] = auth()->id();
            $data['sender_type'] = auth()->user()->role; // user or rider
            $data['receiver_id'] = null;
            $data['receiver_type'] = 'admin';

            $chat = $this->supportChatService->createMessage($data);
            return ResponseHelper::success($chat, "Support message sent");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    // ✅ Admin replying to support message
    public function adminReply(Request $request, $messageId)
    {
        try {
            $request->validate(['message' => 'required|string']);
            $chat = $this->supportChatService->replyToMessage($messageId, $request->message);
            return ResponseHelper::success($chat, "Support reply sent");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    // ✅ View support chat history
    public function supportMessages()
    {
        try {
            $userId = auth()->id();
            $chats = $this->supportChatService->getSupportChats($userId);

            return ResponseHelper::success($chats, "Support messages retrieved");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }
}
