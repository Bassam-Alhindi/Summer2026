import { toast } from 'svelte-sonner';
import { getLocale, t } from './i18n.svelte';

/**
 * تحويل الكلام إلى نص، بسياسة إشعارات موحّدة بين الصفحات:
 *  - فشل (رفض إذن، صمت، خطأ تعرّف) -> توست خطأ بنفس ستايل التطبيق
 *  - نجاح -> بدون أي إشعار، نمرّر النص للمستدعي وبس
 *
 * سبب وجوده كملف مشترك: نفس المنطق مستخدم في الرئيسية وصفحة المساعد،
 * ولو انكتب مرتين بينحرفون عن بعض مع الوقت.
 */

type SpeechHandle = {
    stop: () => void;
};

type StartOptions = {
    /** يستقبل النص المفرّغ. لا يُستدعى إطلاقاً لو ما انلقط شي. */
    onResult: (transcript: string) => void;
    onStart?: () => void;
    onEnd?: () => void;
    /** افتراضياً يتبع لغة الواجهة. */
    lang?: string;
};

function getRecognitionCtor(): any {
    if (typeof window === 'undefined') return null;
    return (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition || null;
}

export function isSpeechSupported(): boolean {
    return getRecognitionCtor() !== null;
}

export function startSpeechToText(options: StartOptions): SpeechHandle | null {
    const Recognition = getRecognitionCtor();

    if (!Recognition) {
        toast.error(t('voice.not_supported', 'التعرف الصوتي غير مدعوم في هذا المتصفح'));
        return null;
    }

    const recognition = new Recognition();
    recognition.lang = options.lang ?? (getLocale() === 'ar' ? 'ar-SA' : 'en-US');
    recognition.interimResults = false;
    recognition.continuous = false;
    recognition.maxAlternatives = 1;

    let notified = false;
    let gotTranscript = false;
    let cancelled = false;

    // إشعار واحد بس لكل جلسة، عشان ما تتكدّس التوستات
    const notify = (key: string, fallbackAr: string) => {
        if (notified) return;
        notified = true;
        toast.error(t(key, fallbackAr));
    };

    const notifyNoSpeech = () => notify('voice.no_speech', 'لم يتم التقاط الصوت، حاول مرة أخرى');

    recognition.onstart = () => options.onStart?.();

    recognition.onresult = (event: any) => {
        const transcript = String(event?.results?.[0]?.[0]?.transcript ?? '').trim();
        if (!transcript) return; // onend راح ينبّه إنه ما انلقط صوت
        gotTranscript = true;
        options.onResult(transcript); // نجاح = صامت تماماً
    };

    recognition.onerror = (event: any) => {
        // 'aborted' طبيعي لما نوقف الجلسة بأنفسنا
        if (event?.error === 'aborted') return;

        const err = event?.error;
        if (err === 'no-speech' || err === 'audio-capture') {
            notifyNoSpeech();
        } else if (err === 'not-allowed' || err === 'service-not-allowed') {
            notify('voice.denied', 'تم رفض إذن الميكروفون، فعّله من إعدادات المتصفح');
        } else {
            notify('voice.error', 'لم نتمكن من معالجة الصوت، حاول مرة أخرى');
        }
    };

    recognition.onend = () => {
        options.onEnd?.();
        // انتهت بدون ولا كلمة، وما كان إيقاف يدوي -> ننبّه
        if (!gotTranscript && !cancelled) {
            notifyNoSpeech();
        }
    };

    try {
        recognition.start();
    } catch {
        notify('voice.error', 'لم نتمكن من معالجة الصوت، حاول مرة أخرى');
        options.onEnd?.();
        return null;
    }

    return {
        stop: () => {
            cancelled = true;
            try {
                recognition.stop();
            } catch {
                /* الجلسة انتهت أصلاً */
            }
        },
    };
}
