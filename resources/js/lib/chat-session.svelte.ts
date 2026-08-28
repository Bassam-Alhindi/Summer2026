/**
 * حالة محادثة المساعد الذكي، محفوظة على مستوى الموديول لا داخل المكوّن.
 *
 * الموديول يبقى حيّاً طول ما حزمة الجافاسكربت حيّة، يعني التنقّل بين
 * الصفحات عبر Inertia ما يمسح المحادثة (المكوّن يُفكّك لكن الموديول لا).
 * وأي إعادة تحميل كاملة (F5) أو إغلاق التبويب تبني الحزمة من جديد فتُمسح
 * المحادثة تلقائياً. لذلك ما نكتب شيئاً في localStorage ولا في قاعدة
 * البيانات - الشرط إن المحادثة تروح مع إعادة التحميل.
 */

export type ChatToolCall = {
    id: string;
    name: string;
    arguments: Record<string, any>;
    result?: string;
    ok?: boolean;
    summary?: string;
};

export type ChatMessage = {
    id: number;
    role: 'user' | 'assistant';
    content: string;
    toolCalls?: ChatToolCall[];
    isStreaming?: boolean;
};

export const chat = $state<{ messages: ChatMessage[]; nextId: number }>({
    messages: [],
    nextId: 1,
});

export function nextMessageId(): number {
    return chat.nextId++;
}

export function resetChat(): void {
    chat.messages = [];
    chat.nextId = 1;
}

/**
 * لو المستخدم طلع من الصفحة والبث شغّال، الرسالة تبقى معلّمة كأنها تبث
 * وهي ما تبث. ننظّفها عند العودة عشان ما تعلق نقاط الانتظار للأبد.
 */
export function settleStreamingMessages(): void {
    if (!chat.messages.some((m) => m.isStreaming)) {
        return;
    }

    chat.messages = chat.messages.map((m) =>
        m.isStreaming ? { ...m, isStreaming: false } : m,
    );
}
