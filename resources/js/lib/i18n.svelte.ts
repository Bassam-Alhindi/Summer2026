import { translations, type Language, type TranslationKey } from './translations';

const STORAGE_KEY = 'locale';

const locale = $state<{ value: Language }>({ value: 'en' });

const setCookie = (name: string, value: string, days = 365): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const applyDirection = (lang: Language): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const dir = lang === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.dir = dir;
    document.documentElement.lang = lang;
};

const getStoredLocale = (): Language => {
    if (typeof window === 'undefined') {
        return 'en';
    }

    const stored = localStorage.getItem(STORAGE_KEY);
    return stored === 'ar' ? 'ar' : 'en';
};

export function initializeLocale(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const lang = getStoredLocale();
    locale.value = lang;
    applyDirection(lang);
}

export function setLocale(lang: Language): void {
    locale.value = lang;

    if (typeof window !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, lang);
    }

    setCookie(STORAGE_KEY, lang);
    applyDirection(lang);
}

export function getLocale(): Language {
    return locale.value;
}

export function isRTL(): boolean {
    return locale.value === 'ar';
}

// دالة ترجمة مرنة للتعامل مع نصوص قاعدة البيانات والتصنيفات تلقائياً
export function t(key: string | TranslationKey): string {
    if (!key) return '';

    const currentLang = locale.value;
    const currentDict = (translations[currentLang] || {}) as Record<string, string>;
    const enDict = (translations.en || {}) as Record<string, string>;

    // 1. بحث عن المطابقة المباشرة
    if (currentDict[key]) return currentDict[key];

    // 2. بحث بحالة الأحرف الصغيرة أو بادئة التصنيفات categories.
    const cleanKey = key.toString().trim();
    const lowerKey = cleanKey.toLowerCase();
    const categoryKey = `categories.${lowerKey}`;

    if (currentDict[categoryKey]) return currentDict[categoryKey];
    if (currentDict[lowerKey]) return currentDict[lowerKey];

    // 3. محاولة البحث في القاموس الإنجليزي كـ Fallback
    if (enDict[categoryKey]) return enDict[categoryKey];
    if (enDict[lowerKey]) return enDict[lowerKey];

    // 4. ترجمة الاحتياط المباشرة للتصنيفات في حال عدم وجودها بملف Translations
    if (currentLang === 'ar') {
        const arabicCategories: Record<string, string> = {
            food: 'طعام',
            grocery: 'مقاضي',
            groceries: 'مقاضي',
            shopping: 'تسوق',
            transport: 'مواصلات',
            transportation: 'مواصلات',
            car: 'سيارة',
            bills: 'فواتير',
            salary: 'راتب',
            entertainment: 'ترفيه',
            health: 'صحة',
            housing: 'سكن',
            utilities: 'مرافق',
            other: 'أخرى',
        };

        if (arabicCategories[lowerKey]) {
            return arabicCategories[lowerKey];
        }
    }

    return currentDict[key] ?? enDict[key] ?? key;
}

export function toggleLocale(): void {
    setLocale(locale.value === 'en' ? 'ar' : 'en');
}