<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaqRequest;
use App\Services\FaqService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    protected $faqService;

    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }

    public function userFaqs()
    {
        try {
            $faqs = $this->faqService->getByType('user');
            return ResponseHelper::success($faqs, "User FAQs loaded");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function riderFaqs()
    {
        try {
            $faqs = $this->faqService->getByType('rider');
            return ResponseHelper::success($faqs, "Rider FAQs loaded");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }


    public function store(FaqRequest $request)
    {
        try {
            $faq = $this->faqService->create($request->validated());
            return ResponseHelper::success($faq, 'FAQ created successfully');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function update(FaqRequest $request, $id)
    {
        try {
            $faq = $this->faqService->update($id, $request->validated());
            return ResponseHelper::success($faq, 'FAQ updated successfully');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->faqService->delete($id);
            return ResponseHelper::success([], 'FAQ deleted successfully');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }
}
