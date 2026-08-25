<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function revenue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'group_by' => ['nullable', 'in:day,week,month'],
        ]);

        $to = $validated['to'] ?? now()->toDateString();
        $from = $validated['from'] ?? now()->subDays(29)->toDateString();
        $groupBy = $validated['group_by'] ?? 'day';

        return $this->success($this->reportService->revenue($from, $to, $groupBy));
    }
}
