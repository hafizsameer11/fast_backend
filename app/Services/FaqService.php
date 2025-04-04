<?php

namespace App\Services;

use App\Repositories\FaqRepository;

class FaqService
{
    protected $faqRepository;

    public function __construct(FaqRepository $faqRepository)
    {
        $this->faqRepository = $faqRepository;
    }

    public function all()
    {
        return $this->faqRepository->all();
    }

    public function find($id)
    {
        return $this->faqRepository->find($id);
    }

    public function getByType($type)
    {
        return $this->faqRepository->getByType($type);
    }

    public function create(array $data)
    {
        return $this->faqRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->faqRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->faqRepository->delete($id);
    }
}