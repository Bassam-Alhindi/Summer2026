<?php

namespace App\Http\Controllers;

use App\Ai\Agents\FinanceAssistant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssistantController extends Controller
{
    public function stream(Request $request): StreamedResponse
    {
        // أعلى بشوي من مهلة المزوّد (60ث) عشان نغطي عدة أدوات متسلسلة،
        // بس أقل بكثير من السابق حتى ما يعلق العامل خمس دقائق.
        set_time_limit(120);
        ini_set('max_execution_time', '120');
        ignore_user_abort(false);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:50',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string|max:4000',
        ]);

        $userMessage = $validated['message'];
        $history = $validated['history'] ?? [];

        /** @var User $user */
        $user = $request->user();

        // مفتاح المزوّد الناقص خطأ إعداد، مو خلل مؤقت، وإعادة المحاولة ما بتفيد.
        // بدون هالفحص يطلع 403 من جوقل ويوصل للمستخدم كـ"حاول مرة أخرى" للأبد.
        $unconfiguredProvider = $this->providerMissingKey();

        return new StreamedResponse(function () use ($userMessage, $history, $user, $unconfiguredProvider) {
            try {
                $this->sendHeartbeat();

                if ($unconfiguredProvider !== null) {
                    Log::warning('Assistant is not configured: the provider API key is empty.', [
                        'provider' => $unconfiguredProvider,
                    ]);

                    $this->sendEvent([
                        'type' => 'error',
                        'message' => __('messages.assistant_not_configured'),
                    ]);

                    return;
                }

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

                // التفاصيل تروح للّوق فقط. رسالة المزوّد ممكن تحتوي مسارات
                // داخلية أو مفاتيح داخل الروابط، فما نرجّعها للمتصفح.
                $this->sendEvent([
                    'type' => 'error',
                    'message' => __('messages.assistant_failed'),
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * اسم المزوّد إذا كان مفتاحه ناقص، وإلا null. المزوّد يُقرأ من خاصية
     * الـ Provider على الوكيل عشان يضل صحيح إذا تغير المزوّد مستقبلًا.
     */
    private function providerMissingKey(): ?string
    {
        $attributes = (new \ReflectionClass(FinanceAssistant::class))->getAttributes(Provider::class);

        $provider = $attributes === []
            ? config('ai.default')
            : $attributes[0]->newInstance()->value;

        return blank(config("ai.providers.{$provider}.key")) ? $provider : null;
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
