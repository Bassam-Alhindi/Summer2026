import { marked } from 'marked';
import DOMPurify from 'dompurify';

marked.setOptions({
    breaks: true,
    gfm: true,
});

export function renderMarkdown(content: string): string {
    const raw = marked.parse(content) as string;
    return DOMPurify.sanitize(raw, {
        ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'code', 'pre', 'ul', 'ol', 'li', 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'table', 'thead', 'tbody', 'tr', 'th', 'td'],
        ALLOWED_ATTR: ['href', 'target', 'rel', 'class'],
    });
}
