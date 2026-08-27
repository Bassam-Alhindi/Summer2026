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
      confirm: "Confirm",
      saving: "Saving...",
      viewAll: "View All"
    },

    // 2. Bottom Navigation Bar
    nav: {
      home: "Home",
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
      quickAdd: "Quick Add",
      subtitle: "Manage smartly and keep track of your budget",
      net_balance: "Net Balance",
      daily_budget: "Suggested Daily Budget",
      welcome_back: "Welcome back,",
      total_income: "Total Income",
      total_expenses: "Total Expenses",
      view_all: "View All",
      recent_transactions: "Recent Transactions",
      no_recent_transactions: "No recent transactions recorded",
      add_transaction: "Add Transaction"
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
      empty: "No transactions yet",
      emptyState: "No transactions found",
      allCategories: "All Categories",
      today: "Today",
      yesterday: "Yesterday",
      deleteConfirm: "Are you sure you want to delete this transaction?",
      deleteError: "An error occurred while deleting the transaction",
      emptyHint: "No recorded transactions match the selected filters.",
      deleteTitle: "Delete transaction"
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
      color: "Color",
      subtitle: "Give every unit of your money the identity it deserves",
      deleteConfirm: "Are you sure you want to delete this category?",
      systemDefaultHint: "System default category",
      categoryType: "Category Type",
      categoryIcon: "Icon",
      budgetLimit: "Monthly budget limit",
      budgetHint: "You will be alerted when 80% is reached",
      save: "Save",
      cancel: "Cancel",
      deleteCategory: "Delete Category"
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

    // 7.5 Transaction Form / Quick Add (singular)
    transaction: {
      add_quick_transaction: "Add Quick Transaction",
      expense: "Expense",
      income: "Income",
      amount: "Amount",
      category: "Category",
      description_optional: "Description (Optional)",
      description_placeholder: "What is this for?",
      error_required: "Please select amount and category"
    },

    // 7.6 Dashboard Period Selector
    period: {
      week: "This Week",
      month: "This Month",
      year: "This Year"
    },

    // 7.7 Voice Add
    voice: {
      title: "Voice Add",
      error: "Could not process audio, try again",
      no_speech: "No speech detected, please try again",
      denied: "Microphone permission denied, enable it in your browser settings",
      not_supported: "Voice recognition not supported"
    },

    // 7.8 Quick Add Modal (reusable)
    quickadd: {
      title: "Quick Add",
      expense: "Expense",
      income: "Income",
      amount: "Amount",
      category: "Category",
      date: "Date",
      description: "Description",
      descriptionPlaceholder: "What is this for?",
      cancel: "Cancel",
      save: "Save",
      errorMessage: "Please make sure to fill all required fields",
      errors: {
        selectCategory: "Please select a category first"
      }
    },

    // 7.9 AI Insights card
    insights: {
      title: "AI Insights",
      empty: "Your AI insights will appear here once you have transactions."
    },

    // 8. AI Assistant Page
    ai: {
      title: "AI Assistant",
      subtitle: "Ask anything about your financial status",
      placeholder: "Type a message or ask for analysis...",
      send: "Send",
      thinking: "Thinking..."
    },

    // 9. Welcome / Landing Page
    welcome: {
      badge: "Smart money management",
      tagline: "Every coin has a destination... Give your money the identity it deserves",
      subtitle: "Smart automated daily budgets and effortless expense tracking — without the complexity.",
      ctaStart: "Get Started",
      ctaDashboard: "Go to Dashboard",
      ctaPreview: "See how it works",
      ctaLogin: "Log In",
      featureDailyBudget: "Daily Budget",
      featureDailyBudgetDesc: "A smart daily budget calculated automatically to keep you on track.",
      featureTracking: "Transaction Tracking",
      featureTrackingDesc: "Log expenses and income in seconds, effortlessly.",
      featureReports: "Reports & Analytics",
      featureReportsDesc: "Clear visual insights into exactly where your money goes.",
      featurePrivacy: "Full Privacy & Secure Data",
      featurePrivacyDesc: "Your data is encrypted, securely stored, and private to you."
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
      confirm: "تأكيد",
      saving: "جاري الحفظ...",
      viewAll: "عرض الكل"
    },

    // 2. Bottom Navigation Bar
    nav: {
      home: "الرئيسية",
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
      quickAdd: "إضافة سريعة",
      subtitle: "أموالك. رؤيتك. مستقبلك.",
      net_balance: "صافي الرصيد",
      daily_budget: "الميزانية اليومية المقترحة",
      welcome_back: "أهلاً بعودتك،",
      total_income: "إجمالي الدخل",
      total_expenses: "إجمالي المصاريف",
      view_all: "عرض الكل",
      recent_transactions: "المعاملات الأخيرة",
      no_recent_transactions: "لا توجد معاملات مسجلة مؤخراً",
      add_transaction: "إضافة معاملة"
    },

    // 4. Transactions Page & Actions
    transactions: {
      title: "المعاملات",
      subtitle: "كل حركة مالية في متناولك ",
      all: "الكل",
      expense: "المصروفات",
      income: "الدخل",
      selectCategory: "اختر الفئة",
      addTransaction: "إضافة معاملة",
      amount: "المبلغ",
      date: "التاريخ",
      note: "ملاحظة / الوصف",
      type: "النوع",
      empty: "لا توجد معاملات بعد",
      emptyState: "لا توجد معاملات مسجلة",
      allCategories: "جميع الفئات",
      today: "اليوم",
      yesterday: "أمس",
      deleteConfirm: "هل أنت متأكد من حذف هذه المعاملة؟",
      deleteError: "حدث خطأ أثناء حذف المعاملة",
      emptyHint: "لم تقم بتسجيل أي معاملات تطابق الخيارات المختارة.",
      deleteTitle: "حذف المعاملة"
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
      color: "اللون",
      subtitle: "خل كل ريال ينفق في مكانه, لأن القيمة تبدأ من حسن الاختيار",
      deleteConfirm: "هل أنت متأكد من حذف هذه الفئة؟",
      systemDefaultHint: "فئة افتراضية بالنظام",
      categoryType: "نوع الفئة",
      categoryIcon: "الأيقونة",
      budgetLimit: "حد الميزانية الشهري",
      budgetHint: "سيتم تنبيهك عند تجاوز 80%",
      save: "حفظ",
      cancel: "إلغاء",
      deleteCategory: "حذف الفئة"
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

    // 7.5 Transaction Form / Quick Add (singular)
    transaction: {
      add_quick_transaction: "إضافة معاملة سريعة",
      expense: "مصروف",
      income: "دخل",
      amount: "المبلغ",
      category: "الفئة",
      description_optional: "الوصف (اختياري)",
      description_placeholder: "عن ماذا كانت هذه المعاملة؟",
      error_required: "يرجى تحديد المبلغ والفئة أولاً"
    },

    // 7.6 Dashboard Period Selector
    period: {
      week: "هذا الأسبوع",
      month: "هذا الشهر",
      year: "هذه السنة"
    },

    // 7.7 Voice Add
    voice: {
      title: "إضافة صوتية",
      error: "لم نتمكن من معالجة الصوت، حاول مرة أخرى",
      no_speech: "لم يتم التقاط الصوت، حاول مرة أخرى",
      denied: "تم رفض إذن الميكروفون، فعّله من إعدادات المتصفح",
      not_supported: "التعرف الصوتي غير مدعوم في هذا المتصفح"
    },

    // 7.8 Quick Add Modal (reusable)
    quickadd: {
      title: "إضافة سريعة",
      expense: "مصروف",
      income: "دخل",
      amount: "المبلغ",
      category: "الفئة",
      date: "التاريخ",
      description: "الوصف",
      descriptionPlaceholder: "عن ماذا كانت هذه المعاملة؟",
      cancel: "إلغاء",
      save: "حفظ",
      errorMessage: "يرجى التأكد من ملء جميع الحقول المطلوبة",
      errors: {
        selectCategory: "يرجى اختيار تصنيف أولاً"
      }
    },

    // 7.9 AI Insights card
    insights: {
      title: "رؤى الذكاء الاصطناعي",
      empty: "ستظهر رؤى الذكاء الاصطناعي هنا بمجرد وجود معاملات لديك."
    },

    // 8. AI Assistant Page
    ai: {
      title: "المساعد الذكي",
      subtitle: "اسأل أي سؤال عن وضعك المالي",
      placeholder: "اكتب رسالة أو اطلب تحليلاً مالياً...",
      send: "إرسال",
      thinking: "جاري التفكير..."
    },

    // 9. Welcome / Landing Page
    welcome: {
      badge: "إدارة مالية ذكية",
      tagline: "لكل مال وجهة... أعط أموالك الهوية التي تستحقها",
      subtitle: "ميزانيات يومية ذكية تُحسب تلقائياً، وتتبع نفقاتك بلا أي تعقيد.",
      ctaStart: "ابدأ الآن",
      ctaDashboard: "الانتقال للوحة التحكم",
      ctaPreview: "شاهد كيف يعمل",
      ctaLogin: "تسجيل الدخول",
      featureDailyBudget: "الميزانية اليومية",
      featureDailyBudgetDesc: "ميزانية يومية ذكية تُحسب تلقائياً لتُبقيك على المسار الصحيح.",
      featureTracking: "تتبع المعاملات",
      featureTrackingDesc: "سجّل مصاريفك ودخلك في ثوانٍ وبكل سهولة.",
      featureReports: "تقارير وتحليلات",
      featureReportsDesc: "رؤى بصرية واضحة تُظهر لك أين تذهب أموالك بالضبط.",
      featurePrivacy: "أمان وخصوصية تامة",
      featurePrivacyDesc: "بياناتك مشفّرة ومحفوظة بأمان ولا يطّلع عليها أحد."
    }
  }
};