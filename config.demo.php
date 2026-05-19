<?php
/**
 * Loaded on the demo branch only (lms.dhanwanth.com).
 * Production main branch should not include this file.
 */
define('DEMO_MODE', true);

function demo_render_banner(): void
{
    if (!defined('DEMO_MODE') || !DEMO_MODE) {
        return;
    }
    include __DIR__ . '/demo/banner.php';
}
