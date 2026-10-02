<?php

namespace App\Support;

/**
 * Ícones sólidos do sistema visual (viewBox 0 0 24 24, extraídos do design aprovado — antes em
 * src/constants/icons.js). Cada entrada é o markup interno do <svg>; a cor vem de currentColor.
 * Renderizados por <x-icon name="..." />.
 */
class Icons
{
    public const PATHS = [
        'servers' => '<rect x="4" y="3.5" width="16" height="7" rx="2"/><rect x="4" y="13.5" width="16" height="7" rx="2"/><circle cx="8" cy="7" r="1.25" fill="#fff"/><circle cx="8" cy="17" r="1.25" fill="#fff"/><path d="M12.5 7h4M12.5 17h4" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>',
        'network' => '<circle cx="5" cy="6" r="2.5"/><circle cx="19" cy="6" r="2.5"/><circle cx="12" cy="18" r="2.5"/><path d="M6.6 7.6l4 8M17.4 7.6l-4 8M7 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'shield-check' => '<path d="M12 2.5l7 2.7v5.6c0 4.6-3 8-7 9.2-4-1.2-7-4.6-7-9.2V5.2z"/><path d="M8.7 12.2l2.2 2.2 4.3-4.6" stroke="#fff" stroke-width="1.9" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
        'cloud-backup' => '<path d="M7 19a4.5 4.5 0 0 1-.5-8.97 5.5 5.5 0 0 1 10.6-1.2A4 4 0 0 1 17.5 19z"/><path d="M12 16v-6m0 0L9.8 12.2M12 10l2.2 2.2" stroke="#fff" stroke-width="1.7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
        'monitor-chart' => '<rect x="2.5" y="4" width="19" height="13" rx="2.5"/><path d="M6 11l2.4-3 2 4 2.2-5 1.8 4H18" stroke="#fff" stroke-width="1.7" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 20h6M12 17v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'lock' => '<rect x="4.5" y="10" width="15" height="10.5" rx="2.4"/><path d="M8 10V7.7a4 4 0 0 1 8 0V10" fill="none" stroke="currentColor" stroke-width="2.1"/><circle cx="12" cy="15.2" r="1.5" fill="#fff"/>',
        'link' => '<rect x="3" y="9" width="8" height="6" rx="3"/><rect x="13" y="9" width="8" height="6" rx="3"/><path d="M9 12h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/>',
        'clipboard-check' => '<rect x="5" y="4" width="14" height="17" rx="2.5"/><rect x="9" y="2.6" width="6" height="3.4" rx="1.5" fill="#fff"/><path d="M8.6 12.5l2 2 4-4.2" stroke="#fff" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
        'login' => '<path d="M11 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6v-2h6V5h-6z"/><path d="M4 12h9m0 0l-3-3m3 3l-3 3" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
        'doc-check' => '<path d="M6 2h8l4 4v16H6z"/><path d="M9 13l2 2 4-4" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
        'chat' => '<path d="M20 3H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h3v3.6L11.8 17H20a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z" fill="#fff"/><circle cx="8.5" cy="10" r="1.35" fill="#159B86"/><circle cx="12" cy="10" r="1.35" fill="#159B86"/><circle cx="15.5" cy="10" r="1.35" fill="#159B86"/>',
        'chevron-down' => '<path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'close' => '<path d="M6.5 6.5l11 11M17.5 6.5l-11 11" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>',
        'check' => '<path d="M5 12.5l4.3 4.3L19 7.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>',
        'whatsapp' => '<path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.39-1.47-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.44-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.87 1.21 3.07c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.7.63.71.22 1.36.19 1.87.11.57-.08 1.76-.72 2-1.41.25-.7.25-1.29.18-1.42-.08-.12-.28-.2-.57-.34zM12.05 21.79h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.22-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.89-9.88a9.82 9.82 0 0 1 9.88 9.89c0 5.45-4.43 9.89-9.88 9.89zm8.41-18.3A11.82 11.82 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89 0-3.18-1.24-6.16-3.48-8.41z"/>',
    ];
}
