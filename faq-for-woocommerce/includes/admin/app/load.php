<?php
/**
 * Happy FAQs admin app: header, shared UI and React pages.
 *
 * Loaded on every request so the pages' REST routes exist.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/class-faq-woocommerce-admin-app.php';
require_once __DIR__ . '/class-ffw-app-settings.php';
require_once __DIR__ . '/class-ffw-app-dashboard.php';
require_once __DIR__ . '/class-ffw-app-preview.php';
require_once __DIR__ . '/class-ffw-app-shortcodes.php';
require_once __DIR__ . '/class-ffw-app-ai.php';
require_once __DIR__ . '/class-ffw-app-analytics.php';
require_once __DIR__ . '/class-ffw-app-help-center.php';
require_once __DIR__ . '/class-ffw-app-patterns.php';
require_once __DIR__ . '/class-ffw-app-questions.php';
require_once __DIR__ . '/class-ffw-app-setup.php';
