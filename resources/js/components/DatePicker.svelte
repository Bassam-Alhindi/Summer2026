<script lang="ts">
    // منتقي تاريخ مضغوط بنفس روح المنتقي داخل نافذة "الإضافة السريعة":
    // زر صغير يعرض التاريخ، وتقويم مخصص بدل نافذة المتصفح العملاقة ذات
    // الثيم الفاتح التي يفتحها <input type="date">.
    import { Calendar, ChevronLeft, ChevronRight } from 'lucide-svelte';
    import { scale } from 'svelte/transition';
    import { getLocale } from '@/lib/i18n.svelte';
    import { cn, localDateString, localToday } from '@/lib/utils';

    let {
        value = $bindable(''),
        placeholder = '',
        align = 'end',
        accent = 'var(--primary)',
        accentForeground = 'var(--primary-foreground)',
        ariaLabel = '',
        clearable = false,
        class: className = '',
        triggerClass = '',
        onselect,
    }: {
        value?: string;
        placeholder?: string;
        align?: 'start' | 'end';
        accent?: string;
        accentForeground?: string;
        ariaLabel?: string;
        clearable?: boolean;
        class?: string;
        triggerClass?: string;
        onselect?: (value: string) => void;
    } = $props();

    const MONTHS_AR = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
    const MONTHS_SHORT_EN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const MONTHS_LONG_EN = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const WEEKDAYS_AR = ['ح', 'ن', 'ث', 'ر', 'خ', 'ج', 'س'];
    const WEEKDAYS_EN = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

    // ارتفاع تقريبي للتقويم، يُستخدم لتقرير اتجاه الفتح قبل أن يُرسم.
    const POPOVER_HEIGHT = 320;

    let isOpen = $state(false);
    let openUp = $state(false);
    let triggerEl = $state<HTMLButtonElement | null>(null);
    let pickerYear = $state(new Date().getFullYear());
    let pickerMonth = $state(new Date().getMonth());

    let isEnglish = $derived(getLocale() === 'en');

    // 'YYYY-MM-DD' يُبنى بمكوّنات محلية: new Date('2026-08-28') يُفسَّر كـ UTC
    // ويقفز يوماً للخلف في المناطق الزمنية السالبة.
    function parseLocalDate(input: string): Date | null {
        const parts = (input ?? '').split('-').map(Number);

        if (parts.length !== 3 || parts.some((n) => !Number.isFinite(n))) {
            return null;
        }

        const [year, month, day] = parts;
        const parsed = new Date(year, month - 1, day);

        return isNaN(parsed.getTime()) ? null : parsed;
    }

    function daysBetweenToday(target: Date): number {
        const now = new Date();
        const todayMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const targetMidnight = new Date(target.getFullYear(), target.getMonth(), target.getDate());

        return Math.round((todayMidnight.getTime() - targetMidnight.getTime()) / 86400000);
    }

    // النص المختصر داخل الزر: اليوم / أمس / غداً / ٢٨ أغسطس
    let triggerLabel = $derived.by(() => {
        const target = parseLocalDate(value);

        if (!target) {
            return placeholder || (isEnglish ? 'Pick a date' : 'اختر تاريخاً');
        }

        const diffDays = daysBetweenToday(target);

        if (diffDays === 0) {
            return isEnglish ? 'Today' : 'اليوم';
        }

        if (diffDays === 1) {
            return isEnglish ? 'Yesterday' : 'أمس';
        }

        if (diffDays === -1) {
            return isEnglish ? 'Tomorrow' : 'غداً';
        }

        const month = isEnglish ? MONTHS_SHORT_EN[target.getMonth()] : MONTHS_AR[target.getMonth()];

        return `${target.getDate()} ${month}`;
    });

    let monthLabel = $derived(
        `${isEnglish ? MONTHS_LONG_EN[pickerMonth] : MONTHS_AR[pickerMonth]} ${pickerYear}`
    );

    let weekdayLabels = $derived(isEnglish ? WEEKDAYS_EN : WEEKDAYS_AR);

    // خانات الشهر: فراغات قبل أول يوم ثم أيام الشهر
    let calendarCells = $derived.by(() => {
        const leading = new Date(pickerYear, pickerMonth, 1).getDay();
        const daysInMonth = new Date(pickerYear, pickerMonth + 1, 0).getDate();
        const cells: (number | null)[] = [];

        for (let i = 0; i < leading; i++) {
            cells.push(null);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            cells.push(day);
        }

        return cells;
    });

    function toggle() {
        if (isOpen) {
            isOpen = false;

            return;
        }

        const current = parseLocalDate(value) ?? new Date();
        pickerYear = current.getFullYear();
        pickerMonth = current.getMonth();
        // يفتح للأعلى إذا لم تتسع المساحة تحته، حتى لا يتجاوز حدود الشاشة.
        const rect = triggerEl?.getBoundingClientRect();
        openUp = !!rect && rect.bottom + POPOVER_HEIGHT > window.innerHeight && rect.top > POPOVER_HEIGHT;
        isOpen = true;
    }

    function shiftMonth(delta: number) {
        const shifted = new Date(pickerYear, pickerMonth + delta, 1);
        pickerYear = shifted.getFullYear();
        pickerMonth = shifted.getMonth();
    }

    function commit(next: string) {
        value = next;
        isOpen = false;
        onselect?.(next);
    }

    function handleKeydown(event: KeyboardEvent) {
        if (event.key === 'Escape' && isOpen) {
            event.stopPropagation();
            isOpen = false;
        }
    }
</script>

<svelte:window onkeydown={handleKeydown} />

<div class={cn('relative', className)}>
    <button
        bind:this={triggerEl}
        type="button"
        onclick={toggle}
        aria-haspopup="dialog"
        aria-expanded={isOpen}
        aria-label={ariaLabel || undefined}
        class={cn(
            'flex h-10 w-full cursor-pointer items-center justify-between gap-1.5 rounded-xl border bg-muted/40 px-2.5 text-xs font-bold text-foreground transition-all active:scale-[0.98] focus:outline-none',
            isOpen ? 'border-primary/40 ring-2 ring-primary/20' : 'border-border/50 hover:bg-muted/60',
            triggerClass
        )}
    >
        <span class={cn('truncate', !value && 'font-semibold text-muted-foreground')}>{triggerLabel}</span>
        <Calendar class="size-3.5 shrink-0 text-muted-foreground" />
    </button>

    {#if isOpen}
        <!-- طبقة شفافة للإغلاق عند النقر خارج التقويم -->
        <button
            type="button"
            tabindex="-1"
            aria-label={isEnglish ? 'Close' : 'إغلاق'}
            class="fixed inset-0 z-40 cursor-default"
            onclick={() => (isOpen = false)}
        ></button>

        <div
            in:scale={{ duration: 120, start: 0.95 }}
            out:scale={{ duration: 100, start: 0.95 }}
            role="dialog"
            aria-label={ariaLabel || (isEnglish ? 'Date' : 'التاريخ')}
            class={cn(
                'absolute z-50 w-[228px] rounded-2xl border border-border/60 bg-popover p-2.5 text-popover-foreground shadow-2xl shadow-black/40 dark:[color-scheme:dark]',
                openUp ? 'bottom-full mb-2' : 'top-full mt-2',
                align === 'end' ? 'end-0' : 'start-0'
            )}
        >
            <div class="mb-2 flex items-center justify-between">
                <button
                    type="button"
                    onclick={() => shiftMonth(-1)}
                    aria-label={isEnglish ? 'Previous' : 'السابق'}
                    class="grid size-6 cursor-pointer place-items-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    {#if isEnglish}
                        <ChevronLeft class="size-3.5" />
                    {:else}
                        <ChevronRight class="size-3.5" />
                    {/if}
                </button>
                <span class="text-[11px] font-bold">{monthLabel}</span>
                <button
                    type="button"
                    onclick={() => shiftMonth(1)}
                    aria-label={isEnglish ? 'Next' : 'التالي'}
                    class="grid size-6 cursor-pointer place-items-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    {#if isEnglish}
                        <ChevronRight class="size-3.5" />
                    {:else}
                        <ChevronLeft class="size-3.5" />
                    {/if}
                </button>
            </div>

            <div class="mb-1 grid grid-cols-7 gap-0.5">
                {#each weekdayLabels as weekday, i (i)}
                    <div class="grid h-6 place-items-center text-[9px] font-bold text-muted-foreground/60">{weekday}</div>
                {/each}
            </div>

            <div class="grid grid-cols-7 gap-0.5">
                {#each calendarCells as day, i (i)}
                    {#if day === null}
                        <div class="h-7"></div>
                    {:else}
                        {@const iso = localDateString(new Date(pickerYear, pickerMonth, day))}
                        {@const isSelected = iso === value}
                        {@const isToday = iso === localToday()}
                        <button
                            type="button"
                            onclick={() => commit(iso)}
                            aria-current={isSelected ? 'date' : undefined}
                            class={cn(
                                'grid h-7 cursor-pointer place-items-center rounded-lg text-[11px] font-semibold tabular-nums transition-all active:scale-90',
                                !isSelected && 'text-foreground/70 hover:bg-muted hover:text-foreground'
                            )}
                            style={isSelected
                                ? `background: ${accent}; color: ${accentForeground};`
                                : isToday
                                  ? `box-shadow: inset 0 0 0 1px color-mix(in srgb, ${accent} 55%, transparent);`
                                  : ''}
                        >
                            {day}
                        </button>
                    {/if}
                {/each}
            </div>

            <div class="mt-2 flex gap-1">
                <button
                    type="button"
                    onclick={() => commit(localToday())}
                    class="h-7 flex-1 cursor-pointer rounded-lg border border-border/60 text-[10px] font-bold text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    {isEnglish ? 'Today' : 'اليوم'}
                </button>

                {#if clearable && value}
                    <button
                        type="button"
                        onclick={() => commit('')}
                        class="h-7 flex-1 cursor-pointer rounded-lg border border-border/60 text-[10px] font-bold text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    >
                        {isEnglish ? 'Clear' : 'كل التواريخ'}
                    </button>
                {/if}
            </div>
        </div>
    {/if}
</div>
