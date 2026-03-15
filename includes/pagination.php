<?php

declare(strict_types=1);

/**
 * Build pagination metadata from a total record count.
 *
 * @param int $total    Total number of records in the result set
 * @param int $page     Current page number (1-based; clamped automatically)
 * @param int $perPage  Number of records per page
 * @return array{total:int, per_page:int, current:int, total_pages:int, offset:int, has_prev:bool, has_next:bool, prev:int, next:int}
 */
function paginate(int $total, int $page, int $perPage = 15): array
{
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = max(1, min($page, $totalPages));

    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $page,
        'total_pages' => $totalPages,
        'offset'      => ($page - 1) * $perPage,
        'has_prev'    => $page > 1,
        'has_next'    => $page < $totalPages,
        'prev'        => $page - 1,
        'next'        => $page + 1,
    ];
}

/**
 * Render an accessible pagination control as an HTML string.
 *
 * Preserves any extra query-string parameters (e.g. search filters) when
 * building the prev/next/page links.
 *
 * @param array  $pagination  Result of paginate()
 * @param string $baseUrl     Script URL without query string (e.g. "products.php")
 * @param array  $extraParams Additional GET params to keep (e.g. ['date_from' => '2025-01-01'])
 * @return string  HTML string (empty string when only one page exists)
 */
function renderPagination(array $pagination, string $baseUrl, array $extraParams = []): string
{
    if ($pagination['total_pages'] <= 1) {
        return '';
    }

    $buildUrl = static function (int $page) use ($baseUrl, $extraParams): string {
        $params = array_filter(
            array_merge($extraParams, ['page' => $page]),
            static fn ($v) => $v !== null && $v !== ''
        );
        return htmlspecialchars($baseUrl . '?' . http_build_query($params), ENT_QUOTES, 'UTF-8');
    };

    $current = $pagination['current'];
    $total   = $pagination['total_pages'];

    // Build the window of page numbers to display (current ±2, always show first & last).
    $pages = [];
    for ($i = max(1, $current - 2); $i <= min($total, $current + 2); $i++) {
        $pages[] = $i;
    }
    if (!in_array(1, $pages, true))     { array_unshift($pages, 1); }
    if (!in_array($total, $pages, true)) { $pages[] = $total; }

    $btnBase     = 'inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold transition';
    $btnActive   = 'bg-slate-900 text-white';
    $btnInactive = 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50';
    $btnDisabled = 'border border-slate-100 bg-slate-50 text-slate-400 cursor-not-allowed';

    $html  = '<div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-sm">';
    $html .= '<p class="text-slate-500">Page ' . $current . ' of ' . $total
           . ' &mdash; ' . number_format($pagination['total']) . ' records</p>';
    $html .= '<nav class="flex flex-wrap items-center gap-1" aria-label="Pagination">';

    // ← Prev
    if ($pagination['has_prev']) {
        $html .= '<a href="' . $buildUrl($pagination['prev']) . '" class="' . $btnBase . ' ' . $btnInactive . '">&larr; Prev</a>';
    } else {
        $html .= '<span class="' . $btnBase . ' ' . $btnDisabled . '">&larr; Prev</span>';
    }

    // Page numbers with ellipsis
    $prev = null;
    foreach ($pages as $p) {
        if ($prev !== null && $p - $prev > 1) {
            $html .= '<span class="px-2 text-slate-400">&hellip;</span>';
        }
        if ($p === $current) {
            $html .= '<span class="' . $btnBase . ' ' . $btnActive . '">' . $p . '</span>';
        } else {
            $html .= '<a href="' . $buildUrl($p) . '" class="' . $btnBase . ' ' . $btnInactive . '">' . $p . '</a>';
        }
        $prev = $p;
    }

    // Next →
    if ($pagination['has_next']) {
        $html .= '<a href="' . $buildUrl($pagination['next']) . '" class="' . $btnBase . ' ' . $btnInactive . '">Next &rarr;</a>';
    } else {
        $html .= '<span class="' . $btnBase . ' ' . $btnDisabled . '">Next &rarr;</span>';
    }

    $html .= '</nav>';
    $html .= '</div>';

    return $html;
}
