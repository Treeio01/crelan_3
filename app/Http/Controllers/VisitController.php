<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TrackAnonymousVisitRequest;
use App\Services\VisitNotificationService;
use Illuminate\Http\JsonResponse;

class VisitController extends Controller
{
    public function __construct(
        private readonly VisitNotificationService $visitNotificationService,
    ) {}

    public function __invoke(TrackAnonymousVisitRequest $request): JsonResponse
    {
        $this->visitNotificationService->notifyAnonymousVisit(
            request: $request,
            eventType: $request->eventType(),
            locale: $request->normalizedLocale(),
        );

        return response()->json(['success' => true]);
    }
}
