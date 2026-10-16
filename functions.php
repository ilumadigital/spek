<?php
/**
 * SPEK Theme functions and definitions.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SPEK_THEME_VERSION', '1.0.0');
define('SPEK_THEME_DIR', get_template_directory());
define('SPEK_THEME_URI', get_template_directory_uri());

$required_files = [
    '/inc/multilingual.php',
    '/inc/i18n-admin.php',
    '/inc/multilingual-importer.php',
    '/inc/setup.php',
    '/inc/enqueue.php',
    '/inc/custom-post-types.php',
    '/inc/contact-submissions.php',
    '/inc/career-applications.php',
    '/inc/taxonomies.php',
    '/inc/product-metaboxes.php',
    '/inc/product-admin-list.php',
    '/inc/catalogue-metaboxes.php',
    '/inc/product-image-processing.php',
    '/inc/product-importer.php',
    '/inc/product-taxonomy-corrections.php',
    '/inc/product-sku-five-digits.php',
    '/inc/product-image-importer.php',
    '/inc/product-image-package-importer.php',
    '/inc/partner-metaboxes.php',
    '/inc/google-map-settings.php',
    '/inc/partner-importer.php',
    '/inc/partner-review.php',
    '/inc/partner-coordinate-repair.php',
    '/inc/product-query.php',
    '/inc/product-export.php',
    '/inc/helpers.php',
    '/inc/ajax.php',
    '/inc/seo-schema.php',
    '/inc/security.php',
    '/inc/theme-options.php',
    '/inc/acf-fields.php',
];

foreach ($required_files as $file) {
    $filepath = SPEK_THEME_DIR . $file;

    if (file_exists($filepath)) {
        require_once $filepath;
    }
}
