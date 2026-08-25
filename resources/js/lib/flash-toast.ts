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

        // إلغاء إشعارات الحذف لجعل العملية صامتة (Silent Deletion)
        const isDeleteMessage = data.message.includes('حذف') || data.message.toLowerCase().includes('delete');
        if (isDeleteMessage) {
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