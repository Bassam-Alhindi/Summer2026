import { translations, type Language, type TranslationKey } from './translations';

const STORAGE_KEY = 'locale';

const locale = $state<{ value: Language }>({ value: 'ar' });

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
  // Arabic is the default; English only applies when explicitly chosen.
  if (typeof window === 'undefined') {
    return 'ar';
  }

  const stored = localStorage.getItem(STORAGE_KEY);
  return stored === 'en' ? 'en' : 'ar';
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

export function t(key: TranslationKey, fallback?: string): string {
  const currentLang = locale.value;
  const keys = key.split('.');

  let result: any = translations[currentLang];
  for (const k of keys) {
    if (result && typeof result === 'object' && k in result) {
      result = result[k];
    } else {
      result = null;
      break;
    }
  }

  if (typeof result === 'string') {
    return result;
  }

  if (currentLang !== 'en') {
    let fallbackResult: any = translations.en;
    for (const k of keys) {
      if (fallbackResult && typeof fallbackResult === 'object' && k in fallbackResult) {
        fallbackResult = fallbackResult[k];
      } else {
        fallbackResult = null;
        break;
      }
    }
    if (typeof fallbackResult === 'string') {
      return fallbackResult;
    }
  }

  return fallback ?? key;
}

export function toggleLocale(): void {
  setLocale(locale.value === 'en' ? 'ar' : 'en');
}