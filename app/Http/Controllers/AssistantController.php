<?php

namespace App\Http\Controllers;

use App\Ai\Agents\FinanceAssistant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssistantController extends Controller
{
    public function stream(Request $request): StreamedResponse
    {
        set_time_limit(300);
        ini_set('max_execution_time', '300');
        ignore_user_abort(false);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:50',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string',
        ]);

        $userMessage = $validated['message'];
        $history = $validated['history'] ?? [];

        /** @var User $user */
        $user = $request->user();

        return new StreamedResponse(function () use ($userMessage, $history, $user) {
            try {
                $this->sendHeartbeat();

                $agent = FinanceAssistant::make(user: $user, history: $history);
                $stream = $agent->stream($userMessage);

                foreach ($stream as $event) {
                    if ($event instanceof TextDelta) {
                        $this->sendEvent([
                            'type' => 'text',
                            'delta' => $event->delta,
                        ]);
                    } elseif ($event instanceof ToolCall) {
                        $this->sendEvent([
                            'type' => 'tool_call',
                            'id' => $event->toolCall->id,
                            'name' => $event->toolCall->name,
                            'arguments' => $event->toolCall->arguments ?? [],
                        ]);
                    } elseif ($event instanceof ToolResult) {
                        $result = $event->toolResult->result ?? '';
                        $summary = is_string($result) ? mb_substr($result, 0, 200) : '';

                        $this->sendEvent([
                            'type' => 'tool_result',
                            'id' => $event->toolResult->id,
                            'name' => $event->toolResult->name,
                            'ok' => $event->successful,
                            'summary' => $summary,
                        ]);
                    }

                    if (connection_aborted()) {
                        break;
                    }
                }

                $this->sendEvent(['type' => 'done']);
            } catch (\Throwable $e) {
                Log::error('Assistant stream error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $this->sendEvent([
                    'type' => 'error',
                    'message' => $e->getMessage() ?: 'An error occurred while processing your request. Please try again.',
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function sendEvent(array $data): void
    {
        echo 'data: '.json_encode($data)."\n\n";
        $this->flushOutput();
    }

    private function sendHeartbeat(): void
    {
        echo ": heartbeat\n\n";
        $this->flushOutput();
    }

    private function flushOutput(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
