<?php

namespace App\Services;

use App\Models\ReviewEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ReviewEventService
{
    private const CHANNEL = 'hanviet:review:events';

    public function broadcast(string $eventType, array $payload, string $source = 'review-code'): ReviewEvent
    {
        $event = ReviewEvent::create([
            'event_type' => $eventType,
            'source' => $source,
            'payload' => $payload,
            'processed_at' => now(),
        ]);

        $this->pushToStream([
            'id' => $event->id,
            'type' => $eventType,
            'source' => $source,
            'payload' => $payload,
            'at' => now()->toIso8601String(),
        ]);

        return $event;
    }

    public function pushToStream(array $data): void
    {
        $events = Cache::get(self::CHANNEL, []);
        $events[] = $data;

        if (count($events) > 500) {
            $events = array_slice($events, -500);
        }

        Cache::put(self::CHANNEL, $events, now()->addHours(2));
        Cache::put(self::CHANNEL.':last_id', $data['id'] ?? 0, now()->addHours(2));
    }

    public function getEventsSince(int $lastId): array
    {
        $events = Cache::get(self::CHANNEL, []);

        return array_values(array_filter($events, fn ($e) => ($e['id'] ?? 0) > $lastId));
    }

    public function runReview(?string $targetFile = null): array
    {
        $projectRoot = base_path();
        $reviewDir = $projectRoot.'/review-code';

        $this->broadcast('review.started', [
            'target' => $targetFile,
            'project' => basename($projectRoot),
        ]);

        if (! is_dir($reviewDir)) {
            $result = ['error' => 'review-code directory not found', 'findings' => []];
            $this->broadcast('review.failed', $result);

            return $result;
        }

        $cmd = ['npm', 'run', 'review'];
        if ($targetFile) {
            $cmd[] = '--';
            $cmd[] = $targetFile;
        }

        $process = new Process($cmd, $reviewDir, null, null, 120);
        $process->run();

        $output = $process->getOutput().$process->getErrorOutput();
        $exitCode = $process->getExitCode();

        $findings = $this->parseReviewOutput($output);

        $result = [
            'exit_code' => $exitCode,
            'passed' => $exitCode === 0,
            'findings_count' => count($findings),
            'findings' => $findings,
            'raw_output' => Str::limit($output, 4000),
        ];

        $this->broadcast($exitCode === 0 ? 'review.passed' : 'review.failed', $result);

        return $result;
    }

    private function parseReviewOutput(string $output): array
    {
        $findings = [];
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            if (preg_match('/\[(critical|error|warning|info)\]\s+(\S+)(?::(\d+))?\s+—\s+(.+)/', $line, $m)) {
                $findings[] = [
                    'severity' => $m[1],
                    'file' => $m[2],
                    'line' => isset($m[3]) ? (int) $m[3] : null,
                    'message' => trim($m[4]),
                ];
            }
        }

        return $findings;
    }
}
