<script lang="ts">
    import { untrack } from 'svelte';
    import { Button } from '@/components/ui/button';
    import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { useForm } from '@inertiajs/svelte';
    import { t } from '@/lib/i18n.svelte';
    import { localToday } from '@/lib/utils';
    import transactions from '@/routes/transactions';
    import { toast } from 'svelte-sonner';

    type CategoryItem = {
        id: number;
        name: string;
        type?: string;
        color?: string;
    };

    type EditingTransaction = {
        id: number;
        amount: string;
        type: 'income' | 'expense';
        category_id: number;
        transaction_date: string;
        description: string | null;
    };

    let {
        open = $bindable(false),
        categories = [] as CategoryItem[],
        editing = null as EditingTransaction | null,
    } = $props();

    const isEditing = $derived(editing !== null);

    const form = useForm({
        amount: '',
        type: 'expense' as 'income' | 'expense',
        category_id: '',
        transaction_date: localToday(),
        description: '',
    });

    const availableCategories = $derived(
        categories.filter((c) => c.type === form.type)
    );

    // قاموس الألوان الموحد للفئات (لحل مشكلة التباين وإرهاق العين)
    const CATEGORY_COLORS: Record<string, string> = {
        // --- المصاريف ---
        food: '#EC4899',             // طعام ومشروبات (وردي)
        'food & drinks': '#EC4899',
        طعام: '#EC4899',
        'طعام ومشروبات': '#EC4899',

        housing: '#10B981',          // سكن (أخضر زمرّدي)
        سكن: '#10B981',

        entertainment: '#3B82F6',    // ترفيه (أزرق)
        ترفيه: '#3B82F6',

        health: '#A855F7',           // صحة (بنفسجي)
        صحة: '#A855F7',

        education: '#F59E0B',        // تعليم (برتقالي)
        تعليم: '#F59E0B',

        bills: '#EF4444',            // فواتير (أحمر)
        فواتير: '#EF4444',

        shopping: '#6366F1',         // تسوق (نيلي)
        تسوق: '#6366F1',

        transportation: '#06B6D4',   // مواصلات (سماوي)
        transport: '#06B6D4',
        مواصلات: '#06B6D4',

        other: '#6B7280',            // أخرى (رمادي)
        أخرى: '#6B7280',

        // --- الدخل ---
        salary: '#10B981',           // الراتب
        الراتب: '#10B981',
        freelance: '#06B6D4',        // عمل حر
        'عمل حر': '#06B6D4',
        investment: '#8B5CF6',       // استثمار
        استثمار: '#8B5CF6',
        gift: '#EC4899',             // هدية
        هدية: '#EC4899',
    };

    function getCategoryColor(name: string): string {
        if (!name) return '#3B82F6';
        const key = name.toLowerCase().trim();
        return CATEGORY_COLORS[key] ?? '#3B82F6';
    }

    // دالة الترجمة الشاملة للدخل والمصاريف
    function translateCategory(name: string): string {
        if (!name) return '';

        const currentExpenseLabel = t('quickadd.expense');
        const isAr = currentExpenseLabel !== 'Expense' && currentExpenseLabel !== 'expense';

        const cleanKey = name.toLowerCase().trim();

        const categoryMap: Record<string, { ar: string; en: string }> = {
            'salary': { ar: 'الراتب', en: 'Salary' },
            'الراتب': { ar: 'الراتب', en: 'Salary' },

            'freelance': { ar: 'عمل حر', en: 'Freelance' },
            'عمل حر': { ar: 'عمل حر', en: 'Freelance' },

            'investment': { ar: 'استثمار', en: 'Investment' },
            'استثمار': { ar: 'استثمار', en: 'Investment' },

            'gift': { ar: 'هدية', en: 'Gift' },
            'هدية': { ar: 'هدية', en: 'Gift' },

            'other income': { ar: 'دخل آخر', en: 'Other Income' },
            'other_income': { ar: 'دخل آخر', en: 'Other Income' },
            'دخل آخر': { ar: 'دخل آخر', en: 'Other Income' },

            'housing': { ar: 'سكن', en: 'Housing' },
            'سكن': { ar: 'سكن', en: 'Housing' },

            'entertainment': { ar: 'ترفيه', en: 'Entertainment' },
            'ترفيه': { ar: 'ترفيه', en: 'Entertainment' },

            'health': { ar: 'صحة', en: 'Health' },
            'صحة': { ar: 'صحة', en: 'Health' },

            'education': { ar: 'تعليم', en: 'Education' },
            'تعليم': { ar: 'تعليم', en: 'Education' },

            'bills': { ar: 'فواتير', en: 'Bills' },
            'فواتير': { ar: 'فواتير', en: 'Bills' },

            'shopping': { ar: 'تسوق', en: 'Shopping' },
            'تسوق': { ar: 'تسوق', en: 'Shopping' },

            'transportation': { ar: 'مواصلات', en: 'Transportation' },
            'transport': { ar: 'مواصلات', en: 'Transportation' },
            'مواصلات': { ar: 'مواصلات', en: 'Transportation' },

            'food & drinks': { ar: 'طعام ومشروبات', en: 'Food & Drinks' },
            'food': { ar: 'طعام ومشروبات', en: 'Food & Drinks' },
            'طعام ومشروبات': { ar: 'طعام ومشروبات', en: 'Food & Drinks' },
            'طعام': { ar: 'طعام ومشروبات', en: 'Food & Drinks' },

            'other': { ar: 'أخرى', en: 'Other' },
            'أخرى': { ar: 'أخرى', en: 'Other' },

            'other expense': { ar: 'مصروف آخر', en: 'Other Expense' },
            'other_expense': { ar: 'مصروف آخر', en: 'Other Expense' },
            'مصروف آخر': { ar: 'مصروف آخر', en: 'Other Expense' },

            'grocery': { ar: 'مقاضي', en: 'Groceries' },
            'groceries': { ar: 'مقاضي', en: 'Groceries' },
            'مقاضي': { ar: 'مقاضي', en: 'Groceries' },
        };

        if (categoryMap[cleanKey]) {
            return isAr ? categoryMap[cleanKey].ar : categoryMap[cleanKey].en;
        }

        const i18nKey = `category.${cleanKey}`;
        const translated = t(i18nKey as any);
        if (translated && translated !== i18nKey) return translated;

        return name;
    }

    function handleSubmit() {
        if (!form.category_id) {
            toast.error(t('quickadd.errors.selectCategory') || 'يرجى اختيار تصنيف أولاً');
            return;
        }

        const onSubmit = {
            preserveScroll: true,
            onSuccess: () => {
                // إلغاء toast.success اليدوي لمنع تكرار الإشعارات ولإعطاء الأولوية لتنبيه الميزانية من السيرفر
                open = false;
                form.reset();
                form.type = 'expense';
                form.transaction_date = localToday();
            },
            onError: () => {
                toast.error(t('quickadd.errorMessage') || 'يرجى التأكد من ملء جميع الحقول المطلوبة');
            }
        };

        if (isEditing && editing) {
            form.put(transactions.update.url(editing.id), onSubmit);
        } else {
            form.post(transactions.store.url(), onSubmit);
        }
    }

    $effect(() => {
        if (!open) {
            return;
        }

        const currentEditing = editing;

        untrack(() => {
            form.clearErrors();

            if (currentEditing) {
                form.amount = String(currentEditing.amount);
                form.type = currentEditing.type;
                form.category_id = String(currentEditing.category_id);
                form.transaction_date = currentEditing.transaction_date;
                form.description = currentEditing.description ?? '';
            } else {
                form.reset();
                form.type = 'expense';
                form.transaction_date = localToday();
            }
        });
    });
</script>

<Dialog bind:open>
    <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
            <DialogTitle>{isEditing ? t('common.edit') : t('transactions.addTransaction')}</DialogTitle>
        </DialogHeader>
        <form class="flex flex-col gap-4" onsubmit={(e) => {
            e.preventDefault(); 
            handleSubmit();
        }}>
            <!-- تبديل نوع المعاملة -->
            <div class="grid grid-cols-2 gap-1 rounded-lg bg-muted p-1 text-center">
                <button
                    type="button"
                    class="rounded-md py-1.5 text-xs font-semibold transition-all {form.type === 'expense' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground'}"
                    onclick={() => {
                        form.type = 'expense';
                        form.category_id = '';
                    }}
                >
                    {t('quickadd.expense')}
                </button>
                <button
                    type="button"
                    class="rounded-md py-1.5 text-xs font-semibold transition-all {form.type === 'income' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground'}"
                    onclick={() => {
                        form.type = 'income';
                        form.category_id = '';
                    }}
                >
                    {t('quickadd.income')}
                </button>
            </div>

            <!-- ادخال المبلغ -->
            <div class="flex flex-col gap-1.5">
                <Label for="qa-amount">{t('quickadd.amount')}</Label>
                <Input
                    id="qa-amount"
                    type="number"
                    placeholder="0.00"
                    bind:value={form.amount}
                    min="0"
                    step="0.01"
                    required
                    autofocus
                />
            </div>

            <!-- اختيار التصنيف بألوان ناعمة ومريحة للعين -->
            <div class="flex flex-col gap-1.5">
                <Label>{t('quickadd.category')}</Label>
                {#if availableCategories.length === 0}
                    <p class="text-xs text-muted-foreground italic">لا توجد تصنيفات متاحة لهذا النوع</p>
                {:else}
                    <div class="grid grid-cols-3 gap-2 max-h-40 overflow-y-auto p-0.5">
                        {#each availableCategories as cat (cat.id)}
                            {@const isSelected = form.category_id === String(cat.id)}
                            {@const catColor = cat.color || getCategoryColor(cat.name)}

                            <button
                                type="button"
                                class="flex items-center justify-center rounded-xl border p-2.5 text-xs font-semibold transition-all text-center truncate active:scale-95"
                                style="
                                    background-color: {isSelected ? catColor : catColor + '20'};
                                    border-color: {catColor}50;
                                    color: {isSelected ? '#ffffff' : catColor};
                                "
                                onclick={() => form.category_id = String(cat.id)}
                            >
                                {translateCategory(cat.name)}
                            </button>
                        {/each}
                    </div>
                {/if}
            </div>

            <!-- التاريخ والوصف -->
            <div class="grid grid-cols-2 gap-2">
                <div class="flex flex-col gap-1.5">
                    <Label for="qa-date">{t('quickadd.date')}</Label>
                    <Input id="qa-date" type="date" bind:value={form.transaction_date} required />
                </div>
                <div class="flex flex-col gap-1.5">
                    <Label for="qa-desc">{t('quickadd.description')}</Label>
                    <Input
                        id="qa-desc"
                        type="text"
                        placeholder={t('quickadd.descriptionPlaceholder')}
                        bind:value={form.description}
                    />
                </div>
            </div>

            <DialogFooter class="mt-2">
                <Button type="button" variant="outline" onclick={() => (open = false)}>{t('quickadd.cancel')}</Button>
                <Button type="submit" disabled={form.processing || !form.amount || !form.category_id}>
                    {form.processing ? '...' : (isEditing ? t('common.save') : t('common.add'))}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>