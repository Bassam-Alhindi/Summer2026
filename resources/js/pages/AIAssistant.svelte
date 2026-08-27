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
    import Send from 'lucide-svelte/icons/send';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import Square from 'lucide-svelte/icons/square';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import Loader2 from 'lucide-svelte/icons/loader-2';
    import Wrench from 'lucide-svelte/icons/wrench';
    import Check from 'lucide-svelte/icons/check';
    import X from 'lucide-svelte/icons/x';
    import MessageSquarePlus from 'lucide-svelte/icons/message-square-plus';
    import Bot from 'lucide-svelte/icons/bot';
    import User from 'lucide-svelte/icons/user';
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
            label: isArabic ? 'كم رصيدي الحالي؟' : 'What\'s my current balance?',
            prompt: isArabic ? 'كم رصيدي الحالي؟' : 'What\'s my current balance?',
        },
    ]);

    function getWelcomeMessage(): string {
        return isArabic
            ? 'مرحباً! أنا مساعدك المالي الذكي. يمكنني مساعدتك في تتبع مصاريفك، إضافة معاملات جديدة، أو تحليل عاداتك المالية. كيف أقدر أساعدك اليوم؟'
            : 'Hello! I\'m your smart financial assistant. I can help you track expenses, add new transactions, or analyze your spending habits. How can I help you today?';
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

    // نتابع القاع فقط لو المستخدم أصلاً قريب منه، عشان ما نقاطع قراءته
    // لو كان راجع فوق يقرأ رسالة قديمة أثناء البث.
    let stickToBottom = $state(true);

    function handleChatScroll() {
        if (!chatContainer) return;
        const distance = chatContainer.scrollHeight - chatContainer.scrollTop - chatContainer.clientHeight;
        stickToBottom = distance < 120;
    }

    function scrollToBottom(force = false) {
        if (!chatContainer) return;
        if (!force && !stickToBottom) return;
        requestAnimationFrame(() => {
            if (!chatContainer) return;
            chatContainer.scrollTo({ top: chatContainer.scrollHeight, behavior: 'smooth' });
        });
    }

    function autoResize() {
        if (textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 130) + 'px';
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
        // إرسال رسالة جديدة يرجّعنا للقاع دائماً
        stickToBottom = true;
        scrollToBottom(true);

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
                let serverMsg = '';
                try {
                    serverMsg = (await response.text()).trim();
                } catch {}
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
                    } catch {}
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

<div class="flex flex-1 flex-col gap-4 p-4 pb-24 sm:p-6 lg:pb-6 max-w-3xl mx-auto w-full h-[calc(100vh-4rem)]">
    <!-- هيدر الصفحة بتصميم متناسق مع بقية الفئات -->
    <div class="flex items-center justify-between gap-3 px-1">
        <div class="flex items-center gap-3 min-w-0">
            <div class="relative flex size-11 shrink-0 items-center justify-center rounded-full bg-zinc-950 text-cyan-300 border border-white/10 shadow-[0_0_18px_-4px_rgba(34,211,238,0.55)]">
                <span class="pointer-events-none absolute inset-0 rounded-full bg-gradient-to-br from-cyan-400/15 to-emerald-400/10"></span>
                <Bot class="relative size-5" />
            </div>
            <div class="flex flex-col min-w-0">
                <h1 class="text-xl font-bold tracking-tight sm:text-2xl truncate">
                    {t('ai.title')}
                </h1>
                <p class="text-xs text-muted-foreground mt-0.5 truncate">
                    {isArabic ? 'اسأل… وخذ قرارك بذكاء' : 'Personal assistant to manage and track your expenses'}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-1.5 shrink-0">
            {#if isStreaming}
                <Button
                    variant="ghost"
                    size="icon"
                    class="size-9 rounded-2xl text-rose-500 hover:bg-rose-500/10 transition-colors"
                    onclick={stopStreaming}
                >
                    <Square class="size-4" />
                </Button>
            {/if}
            <Button
                variant="ghost"
                size="icon"
                class="size-9 rounded-2xl text-muted-foreground hover:bg-muted/60 hover:text-foreground transition-colors"
                onclick={clearChat}
                title={isArabic ? 'مسح المحادثة' : 'Clear Chat'}
            >
                <Trash2 class="size-4" />
            </Button>
        </div>
    </div>

    <!-- كارت المحادثة الرئيسي -->
    <div class="relative flex flex-1 flex-col rounded-3xl border border-white/10 bg-white/[0.03] backdrop-blur-2xl shadow-[0_20px_60px_-25px_rgba(0,0,0,0.85)] overflow-hidden min-h-0">
        <span class="pointer-events-none absolute inset-x-10 top-0 h-px bg-gradient-to-r from-transparent via-cyan-300/45 to-transparent"></span>
        <span class="pointer-events-none absolute -top-16 -start-10 size-40 rounded-full bg-cyan-500/10 blur-3xl"></span>
        <span class="pointer-events-none absolute -bottom-16 -end-10 size-40 rounded-full bg-emerald-500/10 blur-3xl"></span>
        <!-- شريط المحادثات -->
        <div
            bind:this={chatContainer}
            onscroll={handleChatScroll}
            class="relative z-10 flex-1 overflow-y-auto p-4 sm:p-5 space-y-4 scroll-smooth"
            dir={isArabic ? 'rtl' : 'ltr'}
        >
            {#each messages as msg (msg.id)}
                {#if msg.role === 'user'}
                    <div class="flex justify-end items-end gap-2">
                        <div class="max-w-[85%] sm:max-w-[75%] rounded-3xl rounded-br-sm bg-primary text-primary-foreground px-4 py-3 text-xs sm:text-sm leading-relaxed shadow-sm font-medium">
                            <p class="whitespace-pre-wrap">{msg.content}</p>
                        </div>
                        <div class="size-7 rounded-full bg-primary/20 text-primary flex items-center justify-center shrink-0 mb-0.5">
                            <User class="size-3.5" />
                        </div>
                    </div>
                {:else}
                    <div class="flex items-start gap-3">
                        <div class="relative size-8 rounded-full bg-zinc-950 text-cyan-300 border border-white/10 flex items-center justify-center shrink-0 mt-1 shadow-[0_0_14px_-4px_rgba(34,211,238,0.6)]">
                            <span class="pointer-events-none absolute inset-0 rounded-full bg-gradient-to-br from-cyan-400/15 to-emerald-400/10"></span>
                            <Bot class="relative size-4" />
                        </div>
                        <div class="max-w-[88%] sm:max-w-[80%] space-y-2.5">
                            {#if msg.toolCalls && msg.toolCalls.length > 0}
                                <div class="space-y-2">
                                    {#each msg.toolCalls as tc (tc.id)}
                                        <div class="rounded-2xl border border-border/60 bg-muted/20 overflow-hidden shadow-xs">
                                            <button
                                                type="button"
                                                class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 text-xs font-semibold cursor-pointer hover:bg-muted/40 transition-colors"
                                                onclick={() => {
                                                    toolExpanded[tc.id] = !toolExpanded[tc.id];
                                                }}
                                            >
                                                <div class="flex items-center gap-2 min-w-0">
                                                    {#if tc.ok === undefined}
                                                        <Loader2 class="size-3.5 text-amber-500 animate-spin shrink-0" />
                                                    {:else if tc.ok}
                                                        <div class="size-4 rounded-full bg-emerald-500/15 text-emerald-500 flex items-center justify-center shrink-0">
                                                            <Check class="size-3 stroke-[3]" />
                                                        </div>
                                                    {:else}
                                                        <div class="size-4 rounded-full bg-rose-500/15 text-rose-500 flex items-center justify-center shrink-0">
                                                            <X class="size-3 stroke-[3]" />
                                                        </div>
                                                    {/if}
                                                    <Wrench class="size-3.5 text-muted-foreground shrink-0" />
                                                    <span class="text-foreground font-bold truncate">{getToolDisplayName(tc.name)}</span>
                                                    {#if tc.summary}
                                                        <span class="text-muted-foreground font-normal truncate opacity-80">- {tc.summary}</span>
                                                    {/if}
                                                </div>
                                                <ChevronDown class="size-3.5 text-muted-foreground transition-transform duration-200 shrink-0 {toolExpanded[tc.id] ? 'rotate-180' : ''}" />
                                            </button>
                                            {#if toolExpanded[tc.id]}
                                                <div class="border-t border-border/40 px-3.5 py-3 space-y-2 bg-background/50 text-[11px]">
                                                    <div>
                                                        <p class="font-bold text-muted-foreground uppercase text-[9px] tracking-wider mb-1">INPUT</p>
                                                        <pre class="font-mono text-foreground/80 bg-muted/60 rounded-xl p-2.5 overflow-x-auto whitespace-pre-wrap break-all border border-border/30">{JSON.stringify(tc.arguments, null, 2)}</pre>
                                                    </div>
                                                    {#if tc.result}
                                                        <div>
                                                            <p class="font-bold text-muted-foreground uppercase text-[9px] tracking-wider mb-1">OUTPUT</p>
                                                            <pre class="font-mono text-foreground/80 bg-muted/60 rounded-xl p-2.5 overflow-x-auto whitespace-pre-wrap break-all max-h-40 border border-border/30">{tc.result}</pre>
                                                        </div>
                                                    {/if}
                                                </div>
                                            {/if}
                                        </div>
                                    {/each}
                                </div>
                            {/if}

                            {#if msg.content}
                                <div class="rounded-3xl rounded-tl-sm bg-muted/40 border border-border/40 p-4 text-xs sm:text-sm leading-relaxed text-foreground prose-content shadow-xs" dir="auto">
                                    {@html renderMarkdown(msg.content)}
                                </div>
                            {:else if msg.isStreaming && (!msg.toolCalls || msg.toolCalls.length === 0)}
                                <div class="inline-flex items-center gap-1.5 px-4 py-3 rounded-2xl bg-muted/40 border border-border/40">
                                    <div class="size-2 rounded-full bg-primary/60 animate-bounce" style="animation-delay: 0ms;"></div>
                                    <div class="size-2 rounded-full bg-primary/60 animate-bounce" style="animation-delay: 150ms;"></div>
                                    <div class="size-2 rounded-full bg-primary/60 animate-bounce" style="animation-delay: 300ms;"></div>
                                </div>
                            {/if}
                        </div>
                    </div>
                {/if}
            {/each}
        </div>

        <!-- اقتراحات البدء السريع -->
        {#if messages.length <= 1}
            <div class="px-4 pb-3 pt-1 border-t border-border/30 bg-muted/10">
                <p class="text-[11px] font-bold text-muted-foreground mb-2 px-1">
                    {isArabic ? 'اقتراحات سريعة:' : 'Quick suggestions:'}
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {#each quickActions as action}
                        <button
                            type="button"
                            class="group text-start rounded-2xl border border-border/50 bg-card p-3 text-xs font-semibold text-foreground/80 hover:text-foreground hover:bg-muted/40 hover:border-primary/40 transition-all shadow-xs flex items-center justify-between"
                            onclick={() => sendMessage(action.prompt)}
                        >
                            <span class="truncate pr-2">{action.label}</span>
                            <MessageSquarePlus class="size-4 text-muted-foreground group-hover:text-primary transition-colors shrink-0" />
                        </button>
                    {/each}
                </div>
            </div>
        {/if}

        <!-- منطقة الإدخال السفلى -->
        <div class="relative z-10 p-3 border-t border-white/10 bg-white/[0.02] backdrop-blur-xl">
            <div class="group relative flex items-center rounded-2xl border border-white/10 bg-zinc-950/60 backdrop-blur-xl transition-all duration-300 focus-within:border-cyan-300/50 focus-within:shadow-[0_0_26px_-6px_rgba(34,211,238,0.5)] focus-within:-translate-y-0.5">
                <span class="pointer-events-none absolute inset-x-8 -top-px h-px bg-gradient-to-r from-transparent via-cyan-300/0 to-transparent transition-all duration-300 group-focus-within:via-cyan-300/60"></span>
                <textarea
                    bind:this={textarea}
                    bind:value={inputValue}
                    onkeydown={handleKeydown}
                    oninput={autoResize}
                    placeholder={isArabic ? 'اكتب رسالتك أو استفسارك المالي...' : 'Type your message or financial question...'}
                    rows="1"
                    disabled={isStreaming}
                    class="w-full resize-none bg-transparent px-4 py-3 text-base sm:text-sm text-foreground placeholder:text-muted-foreground/70 focus:outline-none disabled:opacity-50 max-h-32"
                ></textarea>

                <div class="pe-2 shrink-0">
                    {#if isStreaming}
                        <Button
                            size="icon"
                            variant="ghost"
                            class="size-9 rounded-xl text-rose-500 hover:bg-rose-500/10 transition-colors"
                            onclick={stopStreaming}
                        >
                            <Square class="size-4" />
                        </Button>
                    {:else}
                        <Button
                            size="icon"
                            class="size-9 rounded-xl transition-all duration-200 {inputValue.trim() ? 'bg-primary text-primary-foreground shadow-sm hover:scale-105' : 'bg-muted/60 text-muted-foreground opacity-60'}"
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
</div>

<style>
    .prose-content :global(p) {
        margin: 0.3em 0;
    }
    .prose-content :global(p:first-child) {
        margin-top: 0;
    }
    .prose-content :global(p:last-child) {
        margin-bottom: 0;
    }
    .prose-content :global(code) {
        background: rgba(var(--primary-rgb, 59, 130, 246), 0.1);
        color: var(--primary);
        padding: 0.15em 0.4em;
        border-radius: 0.375em;
        font-size: 0.85em;
        font-weight: 600;
    }
    .prose-content :global(pre) {
        background: rgba(0,0,0,0.05);
        padding: 0.75em 1em;
        border-radius: 0.75em;
        overflow-x: auto;
        margin: 0.5em 0;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .prose-content :global(pre code) {
        background: none;
        color: inherit;
        padding: 0;
    }
    .prose-content :global(ul), .prose-content :global(ol) {
        padding-inline-start: 1.25em;
        margin: 0.3em 0;
    }
    .prose-content :global(li) {
        margin: 0.15em 0;
    }
    .prose-content :global(strong) {
        font-weight: 700;
    }
    .prose-content :global(a) {
        color: var(--primary);
        text-decoration: underline;
        font-weight: 600;
    }
    .prose-content :global(blockquote) {
        border-inline-start: 3px solid var(--primary);
        padding-inline-start: 0.75em;
        margin: 0.5em 0;
        opacity: 0.9;
        font-style: italic;
    }
    .prose-content :global(table) {
        width: 100%;
        border-collapse: collapse;
        margin: 0.5em 0;
        font-size: 0.85em;
        border-radius: 0.5em;
        overflow: hidden;
    }
    .prose-content :global(th), .prose-content :global(td) {
        border: 1px solid var(--border);
        padding: 0.4em 0.6em;
        text-align: start;
    }
    .prose-content :global(th) {
        background: rgba(0,0,0,0.04);
        font-weight: 700;
    }
</style>