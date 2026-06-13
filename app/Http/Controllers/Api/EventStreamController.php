<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReviewEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventStreamController extends Controller
{
    public function __construct(private ReviewEventService $reviewEvents) {}

    public function stream(Request $request): StreamedResponse
    {
        $lastId = (int) $request->query('last_id', 0);

        return response()->stream(function () use ($lastId) {
            $currentLastId = $lastId;
            $heartbeat = 0;

            while (! connection_aborted() && $heartbeat < 120) {
                $events = $this->reviewEvents->getEventsSince($currentLastId);

                foreach ($events as $event) {
                    echo 'event: review'."\n";
                    echo 'data: '.json_encode($event, JSON_UNESCAPED_UNICODE)."\n\n";
                    $currentLastId = max($currentLastId, (int) ($event['id'] ?? 0));
                }

                if (empty($events)) {
                    echo ": heartbeat\n\n";
                }

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                sleep(2);
                $heartbeat++;
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function triggerReview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->reviewEvents->runReview($data['file'] ?? null);

        return response()->json($result);
    }

    public function history(): JsonResponse
    {
        $events = \App\Models\ReviewEvent::orderByDesc('id')->limit(50)->get();

        return response()->json(['events' => $events]);
    }
}
