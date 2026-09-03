---
paths:
  - 'resources/js/**'
---

# Js

## App navigation: 4-tab architecture
The app has 4 main sections: Dashboard (/dashboard), Transactions (/transactions), Reports (/reports), AI Assistant (/ai-assistant). Desktop uses sidebar nav (AppSidebar.svelte), mobile uses fixed BottomNav.svelte. AI Insights are on the AIAssistant page, NOT on Dashboard. Dashboard shows compact summary + recent transactions preview with "View All" link. 9 supported categories: Housing, Entertainment, Health, Education, Bills, Shopping, Transportation, Food & Drinks, Other.
