<?php

declare(strict_types=1);

namespace App\Modules\Event\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Event\Models\Event;
use App\Modules\Event\Resources\EventResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->string('q', '')->toString();
        
        $events = Event::search($query)
            ->where('status', 'published')
            ->paginate(20);

        return response()->json([
            'message' => 'Search results retrieved successfully',
            'data' => EventResource::collection($events)->resolve(),
            'meta' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
            ],
        ], 200);
    }
}
