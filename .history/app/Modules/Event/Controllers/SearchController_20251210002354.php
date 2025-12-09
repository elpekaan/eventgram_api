<?php

declare(strict_types=1);

namespace App\Modules\Event\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Event\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // 1. Query parametresini al (?q=tarkan)
        $query = $request->string('q', '')->toString();

        // 2. Scout ile Meilisearch üzerinden ara
        // paginate() metodu standart Eloquent gibi çalışır ama arkada Meilisearch konuşur.
        $events = Event::search($query)
            ->paginate(20);

        // 3. Sonucu dön
        return response()->json([
            'message' => 'Search results retrieved successfully',
            'data' => $events,
        ]);
    }
}
