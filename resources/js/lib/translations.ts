export type Language = 'en' | 'ar';

export interface TranslationDictionary {
  [key: string]: string | TranslationDictionary;
}

export type TranslationKey = string;

export const translations: Record<Language, TranslationDictionary> = {
  en: {
    // 1. General & Common Terms
    common: {
      appName: "Financial App",
      dashboard: "Dashboard",
      reports: "Reports",
      transactions: "Transactions",
      categories: "Categories",
      aiAssistant: "AI Assistant",
      language: "Language",
      english: "English",
      arabic: "Arabic",
      currency: "⃁",
      save: "Save",
      cancel: "Cancel",
      close: "Close",
      delete: "Delete",
      edit: "Edit",
      add: "Add",
      search: "Search...",
      loading: "Loading...",
      noData: "No data available",
      confirm: "Confirm"
    },

    // 2. Bottom Navigation Bar
    nav: {
      dashboard: "My Wallet",
      transactions: "Transactions",
      reports: "Reports",
      categories: "Categories",
      ai: "AI"
    },

    // 3. Dashboard Page
    dashboard: {
      title: "My Wallet",
      welcome: "Welcome back",
      totalBalance: "Total Balance",
      monthlyIncome: "Monthly Income",
      monthlyExpenses: "Monthly Expenses",
      recentTransactions: "Recent Transactions",
      viewAll: "View All",
      quickAdd: "Quick Add"
    },

    // 4. Transactions Page & Actions
    transactions: {
      title: "Transactions",
      subtitle: "Track your income and expenses",
      all: "All",
      expense: "Expenses",
      income: "Income",
      selectCategory: "Select Category",
      addTransaction: "Add Transaction",
      amount: "Amount",
      date: "Date",
      note: "Note / Description",
      type: "Type",
      emptyState: "No transactions found"
    },

    // 5. Reports & Analytics Page
    reports: {
      title: "Reports",
      subtitle: "Period expenses and details",
      selectPeriod: "Select Time Period",
      thisMonth: "This Month",
      thisWeek: "This Week",
      thisYear: "This Year",
      distribution: "Expense Distribution",
      expenseDistribution: "Expense Distribution",
      totalExpenses: "Total Expenses",
      categories: "Categories",
      categoriesTitle: "Categories",
      ofTotal: "of total",
      showAll: "Show All",
      noData: "No Data Available",
      noDataHint: "No expenses recorded for this period",
      from: "From:",
      to: "To:",
      filterBtn: "Apply",
      periods: {
        today: "Today",
        yesterday: "Yesterday",
        thisWeek: "This Week",
        thisMonth: "This Month",
        lastMonth: "Last Month",
        custom: "Custom Date..."
      },
      timePeriods: {
        daily: "Daily",
        weekly: "Weekly",
        monthly: "Monthly",
        yearly: "Yearly"
      },
      metrics: {
        totalExpense: "Total Expenses",
        totalIncome: "Total Income",
        netBalance: "Net Balance"
      }
    },

    // 6. Categories Management
    categories: {
      title: "Categories",
      addCategory: "Add Category",
      editCategory: "Edit Category",
      categoryName: "Category Name",
      type: "Type",
      icon: "Icon",
      color: "Color"
    },

    // 7. Individual Category Names (Singular, Plural & Full Names)
    category: {
      // General & Common
      food: "Food",
      foodAndDrinks: "Food & Drinks",
      groceries: "Groceries",
      transportation: "Transportation",
      transport: "Transport",
      car: "Car",
      health: "Health",
      housing: "Housing",
      utilities: "Utilities",
      bills: "Bills",
      investment: "Investment",
      investments: "Investments",
      freelance: "Freelance",
      gift: "Gift",
      gifts: "Gifts",
      education: "Education",
      entertainment: "Entertainment",
      shopping: "Shopping",
      salary: "Salary",
      personal: "Personal",
      family: "Family",
      travel: "Travel",
      subscriptions: "Subscriptions",
      other: "Other"
    },

    // 8. AI Assistant Page
    ai: {
      title: "AI Assistant",
      subtitle: "Ask anything about your financial status",
      placeholder: "Type a message or ask for analysis...",
      send: "Send",
      thinking: "Thinking..."
    }
  },

  ar: {
    // 1. General & Common Terms
    common: {
      appName: "التطبيق المالي",
      dashboard: "محفظتي",
      reports: "التقارير",
      transactions: "المعاملات",
      categories: "الفئات",
      aiAssistant: "المساعد الذكي",
      language: "اللغة",
      english: "الإنجليزية",
      arabic: "العربية",
      currency: "⃁",
      save: "حفظ",
      cancel: "إلغاء",
      close: "إغلاق",
      delete: "حذف",
      edit: "تعديل",
      add: "إضافة",
      search: "بحث...",
      loading: "جاري التحميل...",
      noData: "لا توجد بيانات متاحة",
      confirm: "تأكيد"
    },

    // 2. Bottom Navigation Bar
    nav: {
      dashboard: "الرئيسية",
      transactions: "المعاملات",
      reports: "التقارير",
      categories: "الفئات",
      ai: "المساعد"
    },

    // 3. Dashboard Page
    dashboard: {
      title: "محفظتي",
      welcome: "مرحباً بك مجدداً",
      totalBalance: "الرصيد الإجمالي",
      monthlyIncome: "الدخل الشهري",
      monthlyExpenses: "المصروفات الشهرية",
      recentTransactions: "أحدث المعاملات",
      viewAll: "عرض الكل",
      quickAdd: "إضافة سريعة"
    },

    // 4. Transactions Page & Actions
    transactions: {
      title: "المعاملات",
      subtitle: "تتبع مصاريفك ودخلك بدقة",
      all: "الكل",
      expense: "المصروفات",
      income: "الدخل",
      selectCategory: "اختر الفئة",
      addTransaction: "إضافة معاملة",
      amount: "المبلغ",
      date: "التاريخ",
      note: "ملاحظة / الوصف",
      type: "النوع",
      emptyState: "لا توجد معاملات مسجلة"
    },

    // 5. Reports & Analytics Page
    reports: {
      title: "التقارير",
      subtitle: "اعرف كل ريال فين انصرف",
      selectPeriod: "تحديد الفترة الزمنية",
      thisMonth: "هذا الشهر",
      thisWeek: "هذا الأسبوع",
      thisYear: "هذه السنة",
      distribution: "توزيع المصاريف",
      expenseDistribution: "توزيع المصاريف",
      totalExpenses: "إجمالي المصاريف",
      categories: "الفئات",
      categoriesTitle: "الفئات",
      ofTotal: "من الإجمالي",
      showAll: "عرض الكل",
      noData: "لا توجد بيانات",
      noDataHint: "لا توجد مصاريف مسجلة في هذه الفترة",
      from: "من:",
      to: "إلى:",
      filterBtn: "عرض",
      periods: {
        today: "اليوم",
        yesterday: "أمس",
        thisWeek: "هذا الأسبوع",
        thisMonth: "هذا الشهر",
        lastMonth: "الشهر الماضي",
        custom: "تاريخ مخصص..."
      },
      timePeriods: {
        daily: "يومي",
        weekly: "أسبوعي",
        monthly: "شهري",
        yearly: "سنوي"
      },
      metrics: {
        totalExpense: "إجمالي المصروفات",
        totalIncome: "إجمالي الدخل",
        netBalance: "صافي الرصيد"
      }
    },

    // 6. Categories Management
    categories: {
      title: "الفئات",
      addCategory: "إضافة فئة",
      editCategory: "تعديل الفئة",
      categoryName: "اسم الفئة",
      type: "النوع",
      icon: "الأيقونة",
      color: "اللون"
    },

    // 7. Individual Category Names (Singular, Plural & Full Names)
    category: {
      // General & Common
      food: "الطعام",
      foodAndDrinks: "اكل وشرب",
      groceries: "المقاضي",
      transportation: "مواصلات",
      transport: "مواصلات",
      car: "السيارة",
      health: "الصحة",
      housing: "سكن",
      utilities: "الخدمات والفواتير",
      bills: "الفواتير",
      investment: "استثمار",
      investments: "استثمارات",
      freelance: "عمل حر",
      gift: "هدية",
      gifts: "هدايا",
      education: "تعليم",
      entertainment: "ترفيه",
      shopping: "تسوق",
      salary: "الراتب",
      personal: "شخصي",
      family: "العائلة",
      travel: "السفر",
      subscriptions: "الاشتراكات",
      other: "أخرى"
    },

    // 8. AI Assistant Page
    ai: {
      title: "المساعد الذكي",
      subtitle: "اسأل أي سؤال عن وضعك المالي",
      placeholder: "اكتب رسالة أو اطلب تحليلاً مالياً...",
      send: "إرسال",
      thinking: "جاري التفكير..."
    }
  }
};