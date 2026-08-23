import Home from 'lucide-svelte/icons/home';
import Film from 'lucide-svelte/icons/film';
import Heart from 'lucide-svelte/icons/heart';
import GraduationCap from 'lucide-svelte/icons/graduation-cap';
import Receipt from 'lucide-svelte/icons/receipt';
import ShoppingBag from 'lucide-svelte/icons/shopping-bag';
import Car from 'lucide-svelte/icons/car';
import UtensilsCrossed from 'lucide-svelte/icons/utensils-crossed';
import MoreHorizontal from 'lucide-svelte/icons/more-horizontal';
import Briefcase from 'lucide-svelte/icons/briefcase';
import Banknote from 'lucide-svelte/icons/banknote';
import Gift from 'lucide-svelte/icons/gift';
import TrendingUp from 'lucide-svelte/icons/trending-up';

// ألوان الفئات الافتراضية - تم التوحيد بناءً على شاشة الفئات
export const defaultCategoryColors: Record<string, string> = {
    // الدخل (Income)
    'Salary': '#10b981', 'الراتب': '#10b981', 'راتب': '#10b981',
    'Freelance': '#06b6d4', 'عمل حر': '#06b6d4', 'العمل الحر': '#06b6d4',
    'Investment': '#3b82f6', 'استثمار': '#3b82f6', 'استثمار': '#3b82f6',
    'Investments': '#3b82f6', 'استثمارات': '#3b82f6',
    'Gift': '#ef4444', 'هدية': '#ef4444',
    'Gifts': '#ef4444', 'هدايا': '#ef4444', 'هدايا': '#ef4444',

    // المصروفات (Expenses)
    'Food': '#f97316', 'الطعام': '#f97316',
    'Food & Drinks': '#f97316', 'اكل وشرب': '#f97316', 'طعام ومشروبات': '#f97316',
    'Groceries': '#f97316', 'المقاضي': '#f97316', 'مقاضي': '#f97316',
    'Shopping': '#ec4899', 'تسوق': '#ec4899', 'تسوق': '#ec4899',
    'Transportation': '#06b6d4', 'مواصلات': '#06b6d4', 'مواصلات': '#06b6d4',
    'Car': '#06b6d4', 'السيارة': '#06b6d4', 'سيارة': '#06b6d4',
    'Bills': '#10b981', 'الفواتير': '#10b981', 'فواتير': '#10b981', 'فاتورة': '#10b981',
    'Housing': '#3b82f6', 'سكن': '#3b82f6', 'سكن': '#3b82f6',
    'Health': '#ef4444', 'الصحة': '#ef4444', 'صحة': '#ef4444',
    'Education': '#eab308', 'تعليم': '#eab308', 'تعليم': '#eab308',
    'Entertainment': '#8b5cf6', 'ترفيه': '#8b5cf6', 'ترفيه': '#8b5cf6',
    'Subscriptions': '#10b981', 'الاشتراكات': '#10b981', 'اشتراكات': '#10b981',
    'Personal': '#64748b', 'شخصي': '#64748b',
    'Family': '#84cc16', 'العائلة': '#84cc16', 'عائلة': '#84cc16',
    'Travel': '#0ea5e9', 'السفر': '#0ea5e9', 'سفر': '#0ea5e9',
    'Other': '#6b7280', 'أخرى': '#6b7280', 'دخل آخر': '#6b7280', 'اخري': '#6b7280'
};

// قاموس شامل للمفرد والجمع والتعريف بـ "الـ"
const dictionaryEnToAr: Record<string, string> = {
    'gift': 'هدية',
    'gifts': 'هدايا',
    'education': 'تعليم',
    'housing': 'سكن',
    'freelance': 'عمل حر',
    'bills': 'الفواتير',
    'bill': 'فاتورة',
    'shopping': 'تسوق',
    'health': 'الصحة',
    'transportation': 'مواصلات',
    'transport': 'مواصلات',
    'investment': 'استثمار',
    'investments': 'استثمار',
    'food': 'الطعام',
    'food & drinks': 'اكل وشرب',
    'food and drinks': 'اكل وشرب',
    'salary': 'الراتب',
    'entertainment': 'ترفيه',
    'personal': 'شخصي',
    'family': 'العائلة',
    'car': 'السيارة',
    'groceries': 'المقاضي',
    'grocery': 'المقاضي',
    'travel': 'السفر',
    'subscriptions': 'الاشتراكات',
    'subscription': 'اشتراك',
    'other': 'أخرى'
};

const dictionaryArToEn: Record<string, string> = {
    'هدية': 'Gift',
    'هدايا': 'Gifts',
    'هدايا': 'Gifts',
    'تعليم': 'Education',
    'تعليم': 'Education',
    'سكن': 'Housing',
    'سكن': 'Housing',
    'عمل حر': 'Freelance',
    'العمل الحر': 'Freelance',
    'الفواتير': 'Bills',
    'فواتير': 'Bills',
    'فاتورة': 'Bills',
    'تسوق': 'Shopping',
    'تسوق': 'Shopping',
    'الصحة': 'Health',
    'صحة': 'Health',
    'مواصلات': 'Transportation',
    'مواصلات': 'Transportation',
    'استثمار': 'Investments',
    'استثمار': 'Investments',
    'استثمارات': 'Investments',
    'الطعام': 'Food',
    'اكل وشرب': 'Food & Drinks',
    'طعام ومشروبات': 'Food & Drinks',
    'الراتب': 'Salary',
    'راتب': 'Salary',
    'ترفيه': 'Entertainment',
    'ترفيه': 'Entertainment',
    'شخصي': 'Personal',
    'العائلة': 'Family',
    'السيارة': 'Car',
    'المقاضي': 'Groceries',
    'السفر': 'Travel',
    'الاشتراكات': 'Subscriptions',
    'أخرى': 'Other',
    'اخري': 'Other',
    'دخل آخر': 'Other'
};

export function translateCategory(name: string, locale: string): string {
    if (!name) return name;
    const key = name.trim().toLowerCase();

    if (locale === 'ar') {
        return dictionaryEnToAr[key] || name;
    }
    return dictionaryArToEn[name.trim()] || name;
}

export function getCategoryColor(name: string, customColor?: string): string {
    if (customColor && customColor.trim() !== '') return customColor;
    if (!name) return '#6b7280';
    
    const rawName = name.trim();
    if (defaultCategoryColors[rawName]) return defaultCategoryColors[rawName];

    // مطابقة مرنة لتجاهل "الـ" التعريف والمسافات
    const cleanName = rawName.replace(/^ال/, '').toLowerCase();
    for (const [key, color] of Object.entries(defaultCategoryColors)) {
        if (key.replace(/^ال/, '').toLowerCase() === cleanName) {
            return color;
        }
    }

    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    const hue = Math.abs(hash) % 360;
    return `hsl(${hue}, 70%, 50%)`;
}

const iconMap: Record<string, typeof Home> = {
    home: Home, film: Film, heart: Heart, 'graduation-cap': GraduationCap,
    receipt: Receipt, 'shopping-bag': ShoppingBag, car: Car,
    'utensils-crossed': UtensilsCrossed, briefcase: Briefcase,
    banknote: Banknote, gift: Gift, gifts: Gift, 'trending-up': TrendingUp,
    'more-horizontal': MoreHorizontal,
};

export function getCategoryIcon(catName: string, iconName?: string) {
    if (iconName && iconMap[iconName.toLowerCase()]) return iconMap[iconName.toLowerCase()];

    const nameL = (catName || '').toLowerCase();
    if (nameL.includes('freelance') || nameL.includes('عمل')) return Briefcase;
    if (nameL.includes('invest') || nameL.includes('استثمار')) return TrendingUp;
    if (nameL.includes('health') || nameL.includes('صحة')) return Heart;
    if (nameL.includes('gift') || nameL.includes('هدية') || nameL.includes('هدايا')) return Gift;
    if (nameL.includes('education') || nameL.includes('تعليم')) return GraduationCap;
    if (nameL.includes('house') || nameL.includes('housing') || nameL.includes('سكن')) return Home;
    if (nameL.includes('bill') || nameL.includes('فاتورة') || nameL.includes('فواتير')) return Receipt;
    if (nameL.includes('shop') || nameL.includes('تسوق')) return ShoppingBag;
    if (nameL.includes('food') || nameL.includes('drink') || nameL.includes('طعام') || nameL.includes('مشروب') || nameL.includes('مقاضي') || nameL.includes('groceries')) return UtensilsCrossed;
    if (nameL.includes('transport') || nameL.includes('مواصلات') || nameL.includes('car') || nameL.includes('سيارة')) return Car;
    if (nameL.includes('entertainment') || nameL.includes('ترفيه') || nameL.includes('film') || nameL.includes('سينما')) return Film;
    if (nameL.includes('salary') || nameL.includes('راتب') || nameL.includes('دخل')) return Banknote;
    if (nameL.includes('subscription') || nameL.includes('اشتراك') || nameL.includes('اشتراكات')) return Receipt;

    return MoreHorizontal;
}

export function resolveCategoryMeta(categoryName: string, customColor?: string, customIcon?: string) {
    const name = categoryName || '';
    return {
        ar: translateCategory(name, 'ar'),
        en: translateCategory(name, 'en'),
        color: getCategoryColor(name, customColor),
        icon: getCategoryIcon(name, customIcon),
    };
}