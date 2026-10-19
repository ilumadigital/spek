<?php
/**
 * Independent local newsletter interest registry.
 *
 * No mailing-list provider or promotional email sending is configured.
 * An explicit contact-form opt-in creates a pending record. The email owner
 * must confirm via a single-use link before the record becomes exportable.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

const SPEK_NEWSLETTER_CONSENT_VERSION = '2026-10-19-v1';

function spek_newsletter_consent_text(string $lang = 'el'): string
{
    return $lang === 'en'
        ? 'Optional: I would like to receive SPEK company and product updates by email. My name and email will be stored in SPEK’s WordPress subscriber list until I unsubscribe. No newsletter service has been selected yet, and no marketing emails will be sent for now. I can withdraw my consent at any time.'
        : 'Προαιρετικά επιθυμώ να λαμβάνω ενημερώσεις της SPEK για την εταιρεία και τα προϊόντα της μέσω email. Το ονοματεπώνυμο και το email μου θα αποθηκεύονται σε ξεχωριστή λίστα του WordPress έως την απεγγραφή μου. Δεν έχει ακόμη επιλεγεί πλατφόρμα newsletter και προς το παρόν δεν αποστέλλονται προωθητικά emails. Μπορώ να ανακαλέσω τη συγκατάθεσή μου ανά πάσα στιγμή.';
}

function spek_newsletter_register_post_type(): void
{
    register_post_type('spek_newsletter', [
        'labels' => [
            'name' => 'Newsletter',
            'singular_name' => 'Newsletter Subscriber',
            'menu_name' => 'Newsletter',
            'all_items' => 'Newsletter Subscribers',
            'edit_item' => 'Subscriber Details',
            'not_found' => 'No newsletter subscribers found',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=spek_contact',
        'show_in_rest' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'has_archive' => false,
        'rewrite' => false,
        'supports' => [],
        'map_meta_cap' => false,
        'capabilities' => [
            'edit_post' => 'manage_options',
            'read_post' => 'manage_options',
            'delete_post' => 'manage_options',
            'edit_posts' => 'manage_options',
            'edit_others_posts' => 'manage_options',
            'delete_posts' => 'manage_options',
            'delete_private_posts' => 'manage_options',
            'delete_published_posts' => 'manage_options',
            'delete_others_posts' => 'manage_options',
            'edit_private_posts' => 'manage_options',
            'edit_published_posts' => 'manage_options',
            'read_private_posts' => 'manage_options',
            'publish_posts' => 'do_not_allow',
            'create_posts' => 'do_not_allow',
        ],
    ]);
}
add_action('init', 'spek_newsletter_register_post_type', 6);

/** Only store necessary information, independent of the 12-month contact record. */
function spek_newsletter_register_interest(string $email, string $first_name, string $last_name, string $lang): bool
{
    $email = strtolower(sanitize_email($email));
    $first_name = sanitize_text_field($first_name);
    $last_name = sanitize_text_field($last_name);
    $lang = $lang === 'en' ? 'en' : 'el';

    if (!is_email($email)) {
        return false;
    }

    $existing = get_posts([
        'post_type' => 'spek_newsletter',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_key' => '_spek_newsletter_email',
        'meta_value' => $email,
        'fields' => 'ids',
        'suppress_filters' => true,
    ]);

    $id = $existing ? (int) $existing[0] : 0;
    if ($id > 0 && get_post_meta($id, '_spek_newsletter_status', true) === 'confirmed') {
        // Do not reveal or re-confirm existing subscribers.
        return true;
    }

    $throttle_key = 'spek_newsletter_pending_' . md5($email);
    if (get_transient($throttle_key)) {
        return true;
    }

    if (!$id) {
        $id = wp_insert_post([
            'post_type' => 'spek_newsletter',
            'post_status' => 'publish',
            'post_title' => $email,
        ], true);
        if (is_wp_error($id) || !$id) {
            return false;
        }
    }

    $confirm_token = wp_generate_password(48, false, false);
    $withdraw_token = wp_generate_password(48, false, false);

    $metadata = [
        '_spek_newsletter_email' => $email,
        '_spek_newsletter_first_name' => $first_name,
        '_spek_newsletter_last_name' => $last_name,
        '_spek_newsletter_status' => 'pending',
        '_spek_newsletter_source' => 'contact_form',
        '_spek_newsletter_lang' => $lang,
        '_spek_newsletter_opted_at_utc' => current_time('mysql', true),
        '_spek_newsletter_consent_version' => SPEK_NEWSLETTER_CONSENT_VERSION,
        '_spek_newsletter_consent_sha256' => hash('sha256', spek_newsletter_consent_text($lang)),
        '_spek_newsletter_confirm_token' => hash('sha256', $confirm_token),
        '_spek_newsletter_confirm_expires' => (string) (time() + 7 * DAY_IN_SECONDS),
        '_spek_newsletter_withdraw_token' => hash('sha256', $withdraw_token),
    ];
    foreach ($metadata as $key => $value) {
        update_post_meta((int) $id, $key, $value);
    }

    $base = home_url('/contact/');
    if (function_exists('spek_i18n_url')) {
        $base = spek_i18n_url($base, $lang);
    }

    $confirm_url = add_query_arg([
        'spek_newsletter_action' => 'confirm',
        'spek_newsletter_id' => (int) $id,
        'spek_newsletter_token' => $confirm_token,
    ], $base);
    $withdraw_url = add_query_arg([
        'spek_newsletter_action' => 'withdraw',
        'spek_newsletter_id' => (int) $id,
        'spek_newsletter_token' => $withdraw_token,
    ], $base);

    $subject = $lang === 'en'
        ? 'SPEK — Confirm newsletter interest'
        : 'SPEK — Επιβεβαίωση ενδιαφέροντος για ενημερώσεις';
    $message = $lang === 'en'
        ? "You requested SPEK company and product updates. We are only preparing a subscriber list and are not sending newsletters yet. Confirm your email within 7 days:\n\n" . $confirm_url . "\n\nIf you did not request this, ignore this message. To withdraw your request or unsubscribe later, use:\n" . $withdraw_url
        : "Ζητήσατε να ενημερώνεστε για νέα της εταιρείας και των προϊόντων SPEK. Προς το παρόν δημιουργούμε μόνο λίστα συνδρομητών και δεν στέλνουμε newsletters. Επιβεβαιώστε το email σας εντός 7 ημερών:\n\n" . $confirm_url . "\n\nΑν δεν το ζητήσατε, αγνοήστε το μήνυμα. Για ανάκληση της επιλογής τώρα ή στο μέλλον:\n" . $withdraw_url;

    $sent = wp_mail($email, $subject, $message, ['Content-Type: text/plain; charset=UTF-8']);
    if ($sent) {
        set_transient($throttle_key, '1', 15 * MINUTE_IN_SECONDS);
    }

    return $sent;
}

/** Token validation prevents arbitrary email-confirmation and third-party unsubscribe. */
function spek_newsletter_handle_action(): void
{
    if (is_admin() || !isset($_GET['spek_newsletter_action'])) {
        return;
    }

    $action = sanitize_key(wp_unslash((string) $_GET['spek_newsletter_action']));
    if (!in_array($action, ['confirm', 'withdraw'], true)) {
        return;
    }

    $id = isset($_GET['spek_newsletter_id']) ? absint($_GET['spek_newsletter_id']) : 0;
    $token = isset($_GET['spek_newsletter_token'])
        ? sanitize_text_field(wp_unslash((string) $_GET['spek_newsletter_token']))
        : '';

    $lang = function_exists('spek_current_language') ? spek_current_language() : 'el';
    $status = 'invalid';
    if ($id && get_post_type($id) === 'spek_newsletter' && strlen($token) === 48) {
        $key = $action === 'confirm'
            ? '_spek_newsletter_confirm_token'
            : '_spek_newsletter_withdraw_token';
        $expected = (string) get_post_meta($id, $key, true);

        if ($expected !== '' && hash_equals($expected, hash('sha256', $token))) {
            if ($action === 'withdraw') {
                wp_delete_post($id, true);
                $status = 'unsubscribed';
            } elseif ((int) get_post_meta($id, '_spek_newsletter_confirm_expires', true) >= time()
                && get_post_meta($id, '_spek_newsletter_status', true) === 'pending') {
                update_post_meta($id, '_spek_newsletter_status', 'confirmed');
                update_post_meta($id, '_spek_newsletter_confirmed_at_utc', current_time('mysql', true));
                delete_post_meta($id, '_spek_newsletter_confirm_token');
                delete_post_meta($id, '_spek_newsletter_confirm_expires');
                $status = 'confirmed';
            } else {
                $status = 'expired';
            }
        }
    }

    $target = home_url('/contact/');
    if (function_exists('spek_i18n_url')) {
        $target = spek_i18n_url($target, $lang);
    }
    wp_safe_redirect(add_query_arg('newsletter_status', $status, $target) . '#contact-form');
    exit;
}
add_action('template_redirect', 'spek_newsletter_handle_action', 1);

function spek_newsletter_admin_columns(array $columns): array
{
    return [
        'cb' => $columns['cb'] ?? '<input type="checkbox" />',
        'title' => 'Email',
        'subscriber_name' => 'Name',
        'status' => 'Status',
        'opted_at' => 'Opt-in recorded (UTC)',
        'confirmed_at' => 'Confirmed (UTC)',
        'date' => 'Record created',
    ];
}
add_filter('manage_spek_newsletter_posts_columns', 'spek_newsletter_admin_columns');

add_action('manage_spek_newsletter_posts_custom_column', static function (string $column, int $id): void {
    switch ($column) {
        case 'subscriber_name':
            echo esc_html(trim((string) get_post_meta($id, '_spek_newsletter_first_name', true) . ' ' . (string) get_post_meta($id, '_spek_newsletter_last_name', true)));
            break;
        case 'status':
            echo esc_html((string) get_post_meta($id, '_spek_newsletter_status', true));
            break;
        case 'opted_at':
            echo esc_html((string) get_post_meta($id, '_spek_newsletter_opted_at_utc', true));
            break;
        case 'confirmed_at':
            echo esc_html((string) get_post_meta($id, '_spek_newsletter_confirmed_at_utc', true));
            break;
    }
}, 10, 2);

/** Export only email-confirmed, non-withdrawn subscriptions for a future platform. */
function spek_newsletter_export_csv(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('Not authorised.');
    }
    check_admin_referer('spek_newsletter_export');

    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="spek-newsletter-confirmed-' . gmdate('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    if (!$out) {
        exit;
    }
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Email', 'First name', 'Last name', 'Confirmed at UTC', 'Consent version', 'Source']);

    for ($page = 1; ; $page++) {
        $ids = get_posts([
            'post_type' => 'spek_newsletter',
            'post_status' => 'publish',
            'posts_per_page' => 200,
            'paged' => $page,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
            'suppress_filters' => true,
            'meta_query' => [
                ['key' => '_spek_newsletter_status', 'value' => 'confirmed'],
            ],
        ]);

        if (!$ids) {
            break;
        }

        foreach ($ids as $id) {
            $data = [
                get_post_meta($id, '_spek_newsletter_email', true),
                get_post_meta($id, '_spek_newsletter_first_name', true),
                get_post_meta($id, '_spek_newsletter_last_name', true),
                get_post_meta($id, '_spek_newsletter_confirmed_at_utc', true),
                get_post_meta($id, '_spek_newsletter_consent_version', true),
                get_post_meta($id, '_spek_newsletter_source', true),
            ];
            $safe = array_map(static function ($value): string {
                $value = (string) $value;
                return preg_match('/^[=+@\-\t\r]/u', $value) ? "'" . $value : $value;
            }, $data);
            fputcsv($out, $safe);
        }
        if (count($ids) < 200) {
            break;
        }
    }
    fclose($out);
    exit;
}
add_action('admin_post_spek_newsletter_export', 'spek_newsletter_export_csv');

add_action('restrict_manage_posts', static function ($post_type): void {
    if ($post_type !== 'spek_newsletter' || !current_user_can('manage_options')) {
        return;
    }
    $url = wp_nonce_url(admin_url('admin-post.php?action=spek_newsletter_export'), 'spek_newsletter_export');
    echo '<a class="button" style="margin-left:10px" href="' . esc_url($url) . '">' . esc_html__('Export confirmed subscribers (CSV)', 'spek-theme') . '</a>';
});


/**
 * Remove unconfirmed newsletter interest after 14 days. This avoids keeping
 * unverified emails indefinitely while no provider is connected.
 */
function spek_newsletter_schedule_pending_cleanup(): void
{
    if (!wp_next_scheduled('spek_newsletter_prune_pending')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'spek_newsletter_prune_pending');
    }
}
add_action('init', 'spek_newsletter_schedule_pending_cleanup', 45);

add_action('spek_newsletter_prune_pending', static function (): void {
    $expired = get_posts([
        'post_type' => 'spek_newsletter',
        'post_status' => 'publish',
        'posts_per_page' => 100,
        'fields' => 'ids',
        'suppress_filters' => true,
        'meta_query' => [
            'relation' => 'AND',
            ['key' => '_spek_newsletter_status', 'value' => 'pending'],
            ['key' => '_spek_newsletter_opted_at_utc', 'value' => gmdate('Y-m-d H:i:s', strtotime('-14 days')), 'compare' => '<', 'type' => 'DATETIME'],
        ],
    ]);

    foreach ($expired as $id) {
        if (get_post_type((int) $id) === 'spek_newsletter') {
            wp_delete_post((int) $id, true);
        }
    }
});
