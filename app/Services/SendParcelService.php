<?php

namespace App\Services;

use App\Repositories\SendParcelRepository;
use \Exception; // ✅ Explicitly import Exception

class SendParcelService
{
    protected $sendParcelRepository;

    public function __construct(SendParcelRepository $sendParcelRepository)
    {
        $this->sendParcelRepository = $sendParcelRepository;
    }

    public function all()
    {
        return $this->sendParcelRepository->all();
    }

    public function find($id)
    {
        return $this->sendParcelRepository->find($id);
    }

    public function create(array $data)
    {
        try {
            return $this->sendParcelRepository->create($data);
        } catch (\Throwable $th) {
            throw new \Exception("Error creating SendParcel: " . $th->getMessage()); // ✅ Fixed
        }
    }

    public function update($id, array $data)
    {
        return $this->sendParcelRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->sendParcelRepository->delete($id);
    }
    public function updateStatus($id, $status)
    {
        return $this->sendParcelRepository->updateStatus($id, $status);
    }
}
