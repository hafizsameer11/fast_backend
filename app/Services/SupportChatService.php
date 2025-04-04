<?php

namespace App\Services;

use App\Repositories\SupportChatRepository;

class SupportChatService
{
    protected $supportChatRepository;

    public function __construct(SupportChatRepository $supportChatRepository)
    {
        $this->supportChatRepository = $supportChatRepository;
    }

    public function all()
    {
        return $this->supportChatRepository->all();
    }

    public function find($id)
    {
        return $this->supportChatRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->supportChatRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->supportChatRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->supportChatRepository->delete($id);
    }
    public function createMessage($data)
    {
        return $this->supportChatRepository->createMessage($data);
    }

    public function getMessages($senderId, $receiverId)
    {
        return $this->supportChatRepository->getMessages($senderId, $receiverId);
    }

    public function replyToMessage($messageId, $message)
    {
        return $this->supportChatRepository->replyToMessage($messageId, $message);
    }
}