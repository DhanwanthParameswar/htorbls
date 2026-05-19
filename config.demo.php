<?php
/**
 * Loaded on the demo branch only (lms.dhanwanth.com).
 * Production main branch should not include this file.
 */
define('DEMO_MODE', true);
define('SITE_TITLE', 'Demo Library System');

function demo_page_title(string $suffix = ''): string
{
    return $suffix === '' ? SITE_TITLE : SITE_TITLE . ' - ' . $suffix;
}

function demo_render_banner(): void
{
    // Top banner removed; login page shows sandbox notice instead.
}
