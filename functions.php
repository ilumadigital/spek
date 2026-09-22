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
    '/inc/taxonomies.php',
    '/inc/product-metaboxes.php',
    '/inc/catalogue-metaboxes.php',
    '/inc/product-importer.php',
    '/inc/product-image-importer.php',
    '/inc/partner-metaboxes.php',
    '/inc/product-query.php',
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

/**
 * Handle the public SPEK contact form.
 */
function spek_handle_contact_form(): void
{
    $fallback_redirect = home_url('/contact/');
    $redirect_to = isset($_POST['redirect_to'])
        ? wp_validate_redirect(esc_url_raw(wp_unslash($_POST['redirect_to'])), $fallback_redirect)
        : $fallback_redirect;

    $redirect = static function (string $status) use ($redirect_to): void {
        wp_safe_redirect(add_query_arg('contact', $status, $redirect_to));
        exit;
    };

    if (
        !isset($_POST['spek_contact_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['spek_contact_nonce'])), 'spek_contact_submit')
    ) {
        $redirect('error');
    }

    if (!empty($_POST['website'])) {
        $redirect('success');
    }

    $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $company = isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '';
    $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone   = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $topic   = isset($_POST['topic']) ? sanitize_key(wp_unslash($_POST['topic'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $privacy = !empty($_POST['privacy']);

    $topics = [
        'product'    => __('Πληροφορίες προϊόντων', 'spek-theme'),
        'technical'  => __('Τεχνική πληροφόρηση', 'spek-theme'),
        'commercial' => __('Εμπορική συνεργασία', 'spek-theme'),
        'partners'   => __('Δίκτυο συνεργατών / σημεία πώλησης', 'spek-theme'),
        'other'      => __('Άλλο αίτημα', 'spek-theme'),
    ];

    if (
        $name === '' ||
        !is_email($email) ||
        $message === '' ||
        !$privacy ||
        !isset($topics[$topic])
    ) {
        $redirect('error');
    }

    $recipient = sanitize_email((string) apply_filters('spek_contact_recipient', 'info@spek.gr'));

    if (!$recipient) {
        $redirect('error');
    }

    $subject = sprintf('[SPEK Website] %s - %s', $topics[$topic], $name);

    $body = implode("\n", [
        'Νέο αίτημα από το spek.gr',
        '',
        'Ονοματεπώνυμο: ' . $name,
        'Εταιρεία / Οργανισμός: ' . ($company !== '' ? $company : '-'),
        'Email: ' . $email,
        'Τηλέφωνο: ' . ($phone !== '' ? $phone : '-'),
        'Θέμα: ' . $topics[$topic],
        '',
        'Μήνυμα:',
        $message,
    ]);

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        sprintf('Reply-To: %s <%s>', $name, $email),
    ];

    $sent = wp_mail($recipient, $subject, $body, $headers);

    $redirect($sent ? 'success' : 'error');
}
add_action('admin_post_nopriv_spek_contact_submit', 'spek_handle_contact_form');
add_action('admin_post_spek_contact_submit', 'spek_handle_contact_form');
