<script module lang="ts">
    import { aiAssistant } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'AI Assistant',
                href: aiAssistant(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Send from 'lucide-svelte/icons/send';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import Square from 'lucide-svelte/icons/square';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import Loader2 from 'lucide-svelte/icons/loader-2';
    import Wrench from 'lucide-svelte/icons/wrench';
    import Check from 'lucide-svelte/icons/check';
    import X from 'lucide-svelte/icons/x';
    import MessageCircle from 'lucide-svelte/icons/message-circle';
    import { t, getLocale, isRTL } from '@/lib/i18n.svelte';
    import { renderMarkdown } from '@/lib/markdown';

    type ToolCall = {
        id: string;
        name: string;
        arguments: Record<string, any>;
        result?: string;
        ok?: boolean;
        summary?: string;
    };

    type Message = {
        id: number;
        role: 'user' | 'assistant';
        content: string;
        toolCalls?: ToolCall[];
        isStreaming?: boolean;
    };

    type QuickAction = {
        label: string;
        prompt: string;
    };

    let messages: Message[] = $state([]);
    let inputValue = $state('');
    let isStreaming = $state(false);
    let abortController = $state<AbortController | null>(null);
    let nextId = $state(1);
    let chatContainer: HTMLDivElement | null = $state(null);
    let textarea: HTMLTextAreaElement | null = $state(null);

    let currentLang = $derived(getLocale());
    let isArabic = $derived(currentLang === 'ar');

    function getCsrfToken(): string {
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    }

    const quickActions: QuickAction[] = $derived([
        {
            label: isArabic ? 'كم صرفت على الطعام هذا الشهر؟' : 'How much did I spend on food this month?',
            prompt: isArabic ? 'كم صرفت على الطعام هذا الشهر؟' : 'How much did I spend on food this month?',
        },
        {
            label: isArabic ? 'أضف مصروف 50 ⃁ قهوة اليوم' : 'Add 50 SAR coffee expense today',
            prompt: isArabic ? 'أضف مصروف 50 ⃁ قهوة اليوم' : 'Add 50 SAR coffee expense today',
        },
        {
            label: isArabic ? 'ملخص مصاريف هذا الأسبوع' : 'Summary of this week\'s expenses',
            prompt: isArabic ? 'أعطني ملخص مصاريف هذا الأسبوع' : 'Give me a summary of this week\'s expenses',
        },
        {
            label: isArabic ? 'كم رصيدى الحالي؟' : 'What\'s my current balance?',
            prompt: isArabic ? 'كم رصيدى الحالي؟' : 'What\'s my current balance?',
        },
    ]);

    function getWelcomeMessage(): string {
        return isArabic
            ? 'مرحباً! أنا مساعدك المالي. يمكنني مساعدتك في تتبع مصاريفك، إضافة معاملات جديدة، أو تحليل عاداتك المالية. كيف أقدر أساعدك اليوم؟'
            : 'Hello! I\'m your financial assistant. I can help you track expenses, add new transactions, or analyze your spending habits. How can I help you today?';
    }

    $effect(() => {
        if (messages.length === 0) {
            messages = [{
                id: nextId++,
                role: 'assistant',
                content: getWelcomeMessage(),
            }];
        }
    });

    function scrollToBottom() {
        if (chatContainer) {
            requestAnimationFrame(() => {
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            });
        }
    }

    function autoResize() {
        if (textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }
    }

    function handleKeydown(e: KeyboardEvent) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    }

    function clearChat() {
        if (abortController) {
            abortController.abort();
            abortController = null;
        }
        messages = [{
            id: nextId++,
            role: 'assistant',
            content: getWelcomeMessage(),
        }];
        isStreaming = false;
    }

    async function sendMessage(text?: string) {
        const content = (text || inputValue).trim();
        if (!content || isStreaming) return;

        inputValue = '';
        if (textarea) {
            textarea.style.height = 'auto';
        }

        const userMsg: Message = {
            id: nextId++,
            role: 'user',
            content,
        };
        messages = [...messages, userMsg];

        const assistantMsg: Message = {
            id: nextId++,
            role: 'assistant',
            content: '',
            toolCalls: [],
            isStreaming: true,
        };
        messages = [...messages, assistantMsg];
        scrollToBottom();

        isStreaming = true;
        abortController = new AbortController();

        const history = messages
            .filter((m) => m.id !== assistantMsg.id && !m.isStreaming)
            .slice(-20)
            .map((m) => ({ role: m.role, content: m.content }));

        try {
            const response = await fetch('/assistant/stream', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ message: content, history }),
                signal: abortController.signal,
            });

            if (!response.ok) {
                // Read and surface the server's actual error message (e.g. a 401
                // from the AI provider or a validation message) instead of a bare "HTTP 401".
                let serverMsg = '';
                try {
                    serverMsg = (await response.text()).trim();
                } catch {
                }
                const err: any = new Error(serverMsg || `HTTP ${response.status}`);
                err.status = response.status;
                throw err;
            }

            const reader = response.body?.getReader();
            if (!reader) throw new Error('No response body');

            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop() || '';

                for (const line of lines) {
                    if (!line.startsWith('data: ')) continue;

                    const data = line.slice(6).trim();
                    if (!data) continue;

                    try {
                        const event = JSON.parse(data);

                        if (event.type === 'text') {
                            messages = messages.map((m) =>
                                m.id === assistantMsg.id
                                    ? { ...m, content: m.content + event.delta }
                                    : m
                            );
                            scrollToBottom();
                        } else if (event.type === 'tool_call') {
                            messages = messages.map((m) =>
                                m.id === assistantMsg.id
                                    ? {
                                          ...m,
                                          toolCalls: [
                                              ...(m.toolCalls || []),
                                              {
                                                  id: event.id,
                                                  name: event.name,
                                                  arguments: event.arguments,
                                              },
                                          ],
                                      }
                                    : m
                            );
                            scrollToBottom();
                        } else if (event.type === 'tool_result') {
                            messages = messages.map((m) =>
                                m.id === assistantMsg.id
                                    ? {
                                          ...m,
                                          toolCalls: (m.toolCalls || []).map((tc) =>
                                              tc.id === event.id
                                                  ? { ...tc, result: event.summary, ok: event.ok, summary: event.summary }
                                                  : tc
                                          ),
                                      }
                                    : m
                            );
                            scrollToBottom();
                        } else if (event.type === 'error') {
                            messages = messages.map((m) =>
                                m.id === assistantMsg.id
                                    ? { ...m, content: m.content + `\n\n❌ ${event.message}`, isStreaming: false }
                                    : m
                            );
                        }
                    } catch {
                    }
                }
            }
        } catch (err: any) {
            if (err.name !== 'AbortError') {
                const serverMsg = err?.message && err.status ? err.message : '';
                messages = messages.map((m) =>
                    m.id === assistantMsg.id
                        ? {
                              ...m,
                              content: m.content || serverMsg || (isArabic
                                  ? 'عذراً، حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.'
                                  : 'Sorry, a connection error occurred. Please try again.'),
                              isStreaming: false,
                          }
                        : m
                );
            }
        } finally {
            messages = messages.map((m) =>
                m.id === assistantMsg.id ? { ...m, isStreaming: false } : m
            );
            isStreaming = false;
            abortController = null;
            scrollToBottom();
        }
    }

    function stopStreaming() {
        if (abortController) {
            abortController.abort();
        }
    }

    function getToolDisplayName(name: string): string {
        const map: Record<string, { ar: string; en: string }> = {
            'ListTransactions': { ar: 'البحث في المعاملات', en: 'Search Transactions' },
            'CreateTransactions': { ar: 'إضافة معاملات', en: 'Create Transactions' },
            'UpdateTransactions': { ar: 'تعديل معاملات', en: 'Update Transactions' },
            'DeleteTransactions': { ar: 'حذف معاملات', en: 'Delete Transactions' },
        };
        const entry = map[name];
        if (!entry) return name;
        return isArabic ? entry.ar : entry.en;
    }

    let toolExpanded = $state<Record<string, boolean>>({});
</script>

<AppHead title={t('ai.title')} />

<div class="flex h-full flex-1 flex-col p-4 pb-24 sm:p-6 lg:pb-6 max-w-4xl mx-auto w-full">
    <div class="flex items-center justify-between gap-2 mb-4">
        <div class="flex items-center gap-2">
            <Sparkles class="size-5 text-primary" />
            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{t('ai.title')}</h1>
        </div>
        <div class="flex items-center gap-2">
            {#if isStreaming}
                <Button
                    variant="ghost"
                    size="icon"
                    class="size-9 rounded-xl text-rose-500 hover:bg-rose-500/10 cursor-pointer"
                    onclick={stopStreaming}
                >
                    <Square class="size-4" />
                </Button>
            {/if}
            <Button
                variant="ghost"
                size="icon"
                class="size-9 rounded-xl text-muted-foreground hover:text-foreground cursor-pointer"
                onclick={clearChat}
            >
                <Trash2 class="size-4" />
            </Button>
        </div>
    </div>

    <div class="flex flex-1 flex-col rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden">
        <div
            bind:this={chatContainer}
            class="flex-1 overflow-y-auto p-4 space-y-4"
            dir={isArabic ? 'rtl' : 'ltr'}
        >
            {#each messages as msg (msg.id)}
                {#if msg.role === 'user'}
                    <div class="flex justify-end">
                        <div class="max-w-[85%] rounded-2xl rounded-br-md bg-primary text-primary-foreground px-4 py-2.5 text-sm leading-relaxed">
                            <p class="whitespace-pre-wrap">{msg.content}</p>
                        </div>
                    </div>
                {:else}
                    {#if msg.toolCalls && msg.toolCalls.length > 0}
                        <div class="flex justify-start">
                            <div class="max-w-[90%] space-y-2">
                                {#each msg.toolCalls as tc (tc.id)}
                                    <div class="rounded-xl border border-border/50 bg-muted/30 overflow-hidden">
                                        <button
                                            type="button"
                                            class="w-full flex items-center justify-between gap-2 px-3 py-2 text-xs cursor-pointer hover:bg-muted/50 transition-colors"
                                            onclick={() => {
                                                toolExpanded[tc.id] = !toolExpanded[tc.id];
                                                toolExpanded = toolExpanded;
                                            }}
                                        >
                                            <div class="flex items-center gap-2 min-w-0">
                                                {#if tc.ok === undefined}
                                                    <Loader2 class="size-3.5 text-amber-500 animate-spin shrink-0" />
                                                {:else if tc.ok}
                                                    <Check class="size-3.5 text-emerald-500 shrink-0" />
                                                {:else}
                                                    <X class="size-3.5 text-rose-500 shrink-0" />
                                                {/if}
                                                <Wrench class="size-3.5 text-muted-foreground shrink-0" />
                                                <span class="font-semibold text-foreground truncate">{getToolDisplayName(tc.name)}</span>
                                                {#if tc.summary}
                                                    <span class="text-muted-foreground truncate">{tc.summary}</span>
                                                {/if}
                                            </div>
                                            <ChevronDown class="size-3.5 text-muted-foreground transition-transform shrink-0 {toolExpanded[tc.id] ? 'rotate-180' : ''}" />
                                        </button>
                                        {#if toolExpanded[tc.id]}
                                            <div class="border-t border-border/30 px-3 py-2 space-y-2">
                                                <div>
                                                    <p class="text-[10px] font-semibold text-muted-foreground mb-1">INPUT</p>
                                                    <pre class="text-[11px] text-foreground/80 bg-muted/50 rounded-lg p-2 overflow-x-auto whitespace-pre-wrap break-all">{JSON.stringify(tc.arguments, null, 2)}</pre>
                                                </div>
                                                {#if tc.result}
                                                    <div>
                                                        <p class="text-[10px] font-semibold text-muted-foreground mb-1">OUTPUT</p>
                                                        <pre class="text-[11px] text-foreground/80 bg-muted/50 rounded-lg p-2 overflow-x-auto whitespace-pre-wrap break-all max-h-40">{tc.result}</pre>
                                                    </div>
                                                {/if}
                                            </div>
                                        {/if}
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {/if}

                    {#if msg.content}
                        <div class="flex justify-start">
                            <div class="max-w-[85%] rounded-2xl rounded-bl-md bg-muted/70 px-4 py-2.5 text-sm leading-relaxed prose-content" dir="auto">
                                {@html renderMarkdown(msg.content)}
                            </div>
                        </div>
                    {:else if msg.isStreaming && (!msg.toolCalls || msg.toolCalls.length === 0)}
                        <div class="flex justify-start">
                            <div class="flex items-center gap-1.5 px-4 py-3">
                                <div class="size-2 rounded-full bg-muted-foreground/40 animate-bounce" style="animation-delay: 0ms;"></div>
                                <div class="size-2 rounded-full bg-muted-foreground/40 animate-bounce" style="animation-delay: 150ms;"></div>
                                <div class="size-2 rounded-full bg-muted-foreground/40 animate-bounce" style="animation-delay: 300ms;"></div>
                            </div>
                        </div>
                    {/if}
                {/if}
            {/each}
        </div>

        {#if messages.length <= 1}
            <div class="px-4 pb-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {#each quickActions as action}
                        <button
                            type="button"
                            class="text-start rounded-xl border border-border/50 bg-muted/20 px-3 py-2.5 text-xs font-medium text-muted-foreground hover:bg-muted/50 hover:text-foreground transition-colors cursor-pointer"
                            onclick={() => sendMessage(action.prompt)}
                        >
                            <MessageCircle class="size-3.5 inline-block me-1.5 opacity-60" />
                            {action.label}
                        </button>
                    {/each}
                </div>
            </div>
        {/if}

        <div class="border-t border-border/50 p-3">
            <div class="flex items-end gap-2">
                <textarea
                    bind:this={textarea}
                    bind:value={inputValue}
                    onkeydown={handleKeydown}
                    oninput={autoResize}
                    placeholder={isArabic ? 'اكتب رسالتك هنا...' : 'Type your message...'}
                    rows="1"
                    disabled={isStreaming}
                    class="flex-1 resize-none rounded-xl border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-ring/30 disabled:opacity-50 transition-all"
                ></textarea>

                {#if isStreaming}
                    <Button
                        size="icon"
                        variant="ghost"
                        class="size-10 rounded-xl text-rose-500 hover:bg-rose-500/10 cursor-pointer shrink-0"
                        onclick={stopStreaming}
                    >
                        <Square class="size-4" />
                    </Button>
                {:else}
                    <Button
                        size="icon"
                        class="size-10 rounded-xl shrink-0 cursor-pointer"
                        onclick={() => sendMessage()}
                        disabled={!inputValue.trim()}
                    >
                        <Send class="size-4 {isRTL() ? 'rotate-180' : ''}" />
                    </Button>
                {/if}
            </div>
        </div>
    </div>
</div>

<style>
    .prose-content :global(p) {
        margin: 0.25em 0;
    }
    .prose-content :global(p:first-child) {
        margin-top: 0;
    }
    .prose-content :global(p:last-child) {
        margin-bottom: 0;
    }
    .prose-content :global(code) {
        background: rgba(0,0,0,0.08);
        padding: 0.1em 0.35em;
        border-radius: 0.25em;
        font-size: 0.875em;
    }
    .prose-content :global(pre) {
        background: rgba(0,0,0,0.06);
        padding: 0.75em 1em;
        border-radius: 0.5em;
        overflow-x: auto;
        margin: 0.5em 0;
    }
    .prose-content :global(pre code) {
        background: none;
        padding: 0;
    }
    .prose-content :global(ul), .prose-content :global(ol) {
        padding-inline-start: 1.5em;
        margin: 0.25em 0;
    }
    .prose-content :global(li) {
        margin: 0.1em 0;
    }
    .prose-content :global(strong) {
        font-weight: 700;
    }
    .prose-content :global(a) {
        color: var(--primary);
        text-decoration: underline;
    }
    .prose-content :global(blockquote) {
        border-inline-start: 3px solid var(--border);
        padding-inline-start: 1em;
        margin: 0.5em 0;
        opacity: 0.85;
    }
    .prose-content :global(table) {
        width: 100%;
        border-collapse: collapse;
        margin: 0.5em 0;
        font-size: 0.85em;
    }
    .prose-content :global(th), .prose-content :global(td) {
        border: 1px solid var(--border);
        padding: 0.35em 0.6em;
        text-align: start;
    }
    .prose-content :global(th) {
        background: rgba(0,0,0,0.05);
        font-weight: 600;
    }
    .prose-content :global(h3), .prose-content :global(h4) {
        margin: 0.5em 0 0.25em;
        font-weight: 700;
    }
</style>
