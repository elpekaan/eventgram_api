<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CheckIn\Requests\CheckInRequest;
use App\Modules\CheckIn\Services\CheckInService;
use Illuminate\Http\JsonResponse;

class CheckInController extends Controller
{
    public function __construct(
        protected CheckInService $checkInService
    ) {}

    public function checkIn(CheckInRequest $request): JsonResponse
    {
        /** @var \App\Modules\User\Models\User $staff */
        $staff = $request->user();

        // Servisi çağır (Hata varsa exception fırlatır)
        $ticket = $this->checkInService->verifyAndProcess(
            $request->validated('code'),
            $staff
        );

        return response()->json([
            'success' => true,
            'message' => 'Giriş onaylandı! İyi eğlenceler.',
            'data' => [
                'attendee' => $ticket->user->name,
                'ticket_type' => $ticket->ticketType->name,
                'event' => $ticket->event->name,
                'check_in_time' => $ticket->used_at,
            ],
        ]);
    }
}
