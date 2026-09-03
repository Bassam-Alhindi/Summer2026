import { router } from '@inertiajs/svelte';
import { toast } from 'svelte-sonner';
import type { FlashToast } from '@/types/ui';

export function initializeFlashToast(): void {
    router.on('success', (event) => {
        const flash = event.detail.page.props.flash as { toast?: FlashToast } | undefined;
        const data = flash?.toast;

        if (!data) {
            return;
        }

        // الحذف عملية صامتة. الفلتر كان يفحص كلمة "حذف"/"delete" داخل النص،
        // فيبلع أي إشعار يذكر الحذف بأي لغة. صار السيرفر يعلّمها صراحة.
        if (data.silent) {
            return;
        }

        const toastType = data.type in toast ? (data.type as keyof typeof toast) : 'info';

        if (typeof toast[toastType] === 'function') {
            (toast[toastType] as Function)(data.message, {
                duration: 5000,
            });
        }
    });
}