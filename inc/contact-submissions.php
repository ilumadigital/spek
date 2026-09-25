<?php
/**
 * Contact form submissions, mail routing and admin management.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the private Contact submissions post type.
 */
function spek_register_contact_submissions_post_type(): void
{
    register_post_type('spek_contact', [
        'labels' => [
            'name'               => 'Contacts',
            'singular_name'      => 'Contact',
            'menu_name'          => 'Contacts',
            'all_items'          => 'Contacts',
            'edit_item'          => 'Contact Submission',
            'search_items'       => 'Search Contacts',
            'not_found'          => 'No contact submissions found',
            'not_found_in_trash' => 'No contact submissions found in Trash',
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => false,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'has_archive'         => false,
        'rewrite'             => false,
        'menu_icon'           => 'dashicons-email-alt2',
        'menu_position'       => 26,
        'supports'            => [],
        'map_meta_cap'        => true,
        'capabilities'        => [
            'create_posts' => 'do_not_allow',
        ],
    ]);
}
add_action('init', 'spek_register_contact_submissions_post_type', 5);

/**
 * Server-side destinations. These addresses are never exposed in frontend markup.
 */
function spek_contact_destination(string $request_type): string
{
    $destinations = [
        'general' => 'spek@spek.gr',
        'partner' => 'info@spek.gr',
    ];

    return $destinations[$request_type] ?? '';
}

/**
 * Human-readable request type.
 */
function spek_contact_request_type_label(string $request_type, string $lang = 'el'): string
{
    if ($request_type === 'partner') {
        return $lang === 'en' ? 'I am a partner' : 'Είμαι συνεργάτης';
    }

    return $lang === 'en' ? 'General Enquiry' : 'Γενικό Αίτημα';
}

/**
 * Build the public contact URL for a language and status.
 */
function spek_contact_redirect_url(string $lang, string $status): string
{
    $lang = $lang === 'en' ? 'en' : 'el';
    $base = home_url('/contact/');

    if (function_exists('spek_i18n_url')) {
        $base = spek_i18n_url($base, $lang);
    }

    return add_query_arg('contact_status', sanitize_key($status), $base) . '#contact-form';
}

/**
 * Handle public contact form submissions.
 */
function spek_handle_contact_submission(): void
{
    $lang = isset($_POST['contact_lang'])
        ? sanitize_key(wp_unslash((string) $_POST['contact_lang']))
        : 'el';
    $lang = $lang === 'en' ? 'en' : 'el';

    $nonce = isset($_POST['spek_contact_nonce'])
        ? sanitize_text_field(wp_unslash((string) $_POST['spek_contact_nonce']))
        : '';

    if (!$nonce || !wp_verify_nonce($nonce, 'spek_contact_submit')) {
        wp_safe_redirect(spek_contact_redirect_url($lang, 'invalid'));
        exit;
    }

    // Honeypot: return a success response without storing or sending anything.
    $website = isset($_POST['website'])
        ? trim((string) wp_unslash($_POST['website']))
        : '';

    if ($website !== '') {
        wp_safe_redirect(spek_contact_redirect_url($lang, 'success'));
        exit;
    }

    $request_type = isset($_POST['request_type'])
        ? sanitize_key(wp_unslash((string) $_POST['request_type']))
        : '';

    $first_name = isset($_POST['first_name'])
        ? sanitize_text_field(wp_unslash((string) $_POST['first_name']))
        : '';

    $last_name = isset($_POST['last_name'])
        ? sanitize_text_field(wp_unslash((string) $_POST['last_name']))
        : '';

    $email = isset($_POST['email'])
        ? sanitize_email(wp_unslash((string) $_POST['email']))
        : '';

    $phone = isset($_POST['phone'])
        ? sanitize_text_field(wp_unslash((string) $_POST['phone']))
        : '';

    $subject = isset($_POST['subject'])
        ? sanitize_text_field(wp_unslash((string) $_POST['subject']))
        : '';

    $message = isset($_POST['message'])
        ? sanitize_textarea_field(wp_unslash((string) $_POST['message']))
        : '';

    $valid_types = ['general', 'partner'];
    $message_length = function_exists('mb_strlen')
        ? mb_strlen($message, 'UTF-8')
        : strlen($message);

    $is_valid = in_array($request_type, $valid_types, true)
        && $first_name !== ''
        && $last_name !== ''
        && is_email($email)
        && $phone !== ''
        && $subject !== ''
        && $message !== ''
        && $message_length <= 3000;

    if (!$is_valid) {
        wp_safe_redirect(spek_contact_redirect_url($lang, 'invalid'));
        exit;
    }

    $title_subject = function_exists('mb_substr')
        ? mb_substr($subject, 0, 120, 'UTF-8')
        : substr($subject, 0, 120);

    $submission_id = wp_insert_post([
        'post_type'   => 'spek_contact',
        'post_status' => 'publish',
        'post_title'  => sprintf('%s %s — %s', $first_name, $last_name, $title_subject),
    ], true);

    if (is_wp_error($submission_id) || !$submission_id) {
        wp_safe_redirect(spek_contact_redirect_url($lang, 'error'));
        exit;
    }

    $submission_meta = [
        '_spek_contact_request_type' => $request_type,
        '_spek_contact_first_name'   => $first_name,
        '_spek_contact_last_name'    => $last_name,
        '_spek_contact_email'        => $email,
        '_spek_contact_phone'        => $phone,
        '_spek_contact_subject'      => $subject,
        '_spek_contact_message'      => $message,
        '_spek_contact_lang'         => $lang,
        '_spek_contact_mail_status'  => 'pending',
    ];

    foreach ($submission_meta as $meta_key => $meta_value) {
        update_post_meta((int) $submission_id, $meta_key, $meta_value);
    }

    $recipient = spek_contact_destination($request_type);
    $type_label = spek_contact_request_type_label($request_type, $lang);

    if ($lang === 'en') {
        $mail_body = implode("\n", [
            'New SPEK contact submission',
            '',
            'Enquiry type: ' . $type_label,
            'First name: ' . $first_name,
            'Last name: ' . $last_name,
            'Email: ' . $email,
            'Phone: ' . $phone,
            'Subject: ' . $subject,
            '',
            'Message:',
            $message,
        ]);
    } else {
        $mail_body = implode("\n", [
            'Νέα υποβολή φόρμας επικοινωνίας SPEK',
            '',
            'Τύπος αιτήματος: ' . $type_label,
            'Όνομα: ' . $first_name,
            'Επώνυμο: ' . $last_name,
            'Email: ' . $email,
            'Τηλέφωνο: ' . $phone,
            'Θέμα: ' . $subject,
            '',
            'Μήνυμα:',
            $message,
        ]);
    }

    $mail_subject = sprintf('[SPEK Contact] %s — %s', $type_label, $title_subject);
    $reply_name = trim($first_name . ' ' . $last_name);
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        sprintf('Reply-To: %s <%s>', $reply_name, $email),
    ];

    $mail_sent = $recipient !== ''
        ? wp_mail($recipient, $mail_subject, $mail_body, $headers)
        : false;

    update_post_meta(
        (int) $submission_id,
        '_spek_contact_mail_status',
        $mail_sent ? 'sent' : 'failed'
    );

    wp_safe_redirect(spek_contact_redirect_url($lang, $mail_sent ? 'success' : 'error'));
    exit;
}
add_action('admin_post_nopriv_spek_contact_submit', 'spek_handle_contact_submission');
add_action('admin_post_spek_contact_submit', 'spek_handle_contact_submission');

/**
 * Read-only submission details in wp-admin.
 */
function spek_contact_add_meta_boxes(): void
{
    remove_meta_box('submitdiv', 'spek_contact', 'side');

    add_meta_box(
        'spek_contact_submission_details',
        'Contact Submission',
        'spek_contact_render_submission_meta_box',
        'spek_contact',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes_spek_contact', 'spek_contact_add_meta_boxes');

function spek_contact_render_submission_meta_box(WP_Post $post): void
{
    $request_type = (string) get_post_meta($post->ID, '_spek_contact_request_type', true);
    $first_name   = (string) get_post_meta($post->ID, '_spek_contact_first_name', true);
    $last_name    = (string) get_post_meta($post->ID, '_spek_contact_last_name', true);
    $email        = (string) get_post_meta($post->ID, '_spek_contact_email', true);
    $phone        = (string) get_post_meta($post->ID, '_spek_contact_phone', true);
    $subject      = (string) get_post_meta($post->ID, '_spek_contact_subject', true);
    $message      = (string) get_post_meta($post->ID, '_spek_contact_message', true);
    $lang         = (string) get_post_meta($post->ID, '_spek_contact_lang', true);
    $mail_status  = (string) get_post_meta($post->ID, '_spek_contact_mail_status', true);

    $rows = [
        'Τύπος αιτήματος' => spek_contact_request_type_label($request_type, 'el'),
        'Όνομα'           => $first_name,
        'Επώνυμο'         => $last_name,
        'Email'           => $email,
        'Τηλέφωνο'        => $phone,
        'Θέμα'            => $subject,
        'Γλώσσα'          => strtoupper($lang ?: 'el'),
        'Email status'    => $mail_status === 'sent' ? 'Sent' : ($mail_status === 'failed' ? 'Failed' : 'Pending'),
    ];

    echo '<table class="widefat striped" style="border:0">';
    foreach ($rows as $label => $value) {
        echo '<tr>';
        echo '<th style="width:190px;padding:12px 14px">' . esc_html($label) . '</th>';
        echo '<td style="padding:12px 14px">' . esc_html($value) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '<div style="margin-top:20px;padding:18px 20px;border:1px solid #dcdcde;background:#fff">';
    echo '<strong style="display:block;margin-bottom:10px">Μήνυμα</strong>';
    echo '<div style="white-space:pre-wrap;line-height:1.6">' . esc_html($message) . '</div>';
    echo '</div>';
}

/**
 * Contacts list columns.
 */
function spek_contact_admin_columns(array $columns): array
{
    return [
        'cb'           => $columns['cb'] ?? '<input type="checkbox" />',
        'contact_name' => 'Όνομα',
        'request_type' => 'Τύπος',
        'email'        => 'Email',
        'phone'        => 'Τηλέφωνο',
        'subject'      => 'Θέμα',
        'lang'         => 'Γλώσσα',
        'mail_status'  => 'Κατάσταση',
        'date'         => 'Ημερομηνία',
    ];
}
add_filter('manage_spek_contact_posts_columns', 'spek_contact_admin_columns');

function spek_contact_admin_column_content(string $column, int $post_id): void
{
    if ($column === 'contact_name') {
        $first = (string) get_post_meta($post_id, '_spek_contact_first_name', true);
        $last  = (string) get_post_meta($post_id, '_spek_contact_last_name', true);
        $name  = trim($first . ' ' . $last);
        $url   = get_edit_post_link($post_id);

        if ($url) {
            echo '<strong><a href="' . esc_url($url) . '">' . esc_html($name) . '</a></strong>';
        } else {
            echo esc_html($name);
        }
        return;
    }

    if ($column === 'request_type') {
        $type = (string) get_post_meta($post_id, '_spek_contact_request_type', true);
        echo esc_html(spek_contact_request_type_label($type, 'el'));
        return;
    }

    if ($column === 'email') {
        echo esc_html((string) get_post_meta($post_id, '_spek_contact_email', true));
        return;
    }

    if ($column === 'phone') {
        echo esc_html((string) get_post_meta($post_id, '_spek_contact_phone', true));
        return;
    }

    if ($column === 'subject') {
        echo esc_html((string) get_post_meta($post_id, '_spek_contact_subject', true));
        return;
    }

    if ($column === 'lang') {
        echo esc_html(strtoupper((string) get_post_meta($post_id, '_spek_contact_lang', true)));
        return;
    }

    if ($column === 'mail_status') {
        $status = (string) get_post_meta($post_id, '_spek_contact_mail_status', true);
        $label = $status === 'sent' ? 'Sent' : ($status === 'failed' ? 'Failed' : 'Pending');
        echo esc_html($label);
    }
}
add_action('manage_spek_contact_posts_custom_column', 'spek_contact_admin_column_content', 10, 2);

/**
 * Keep submission rows read-only apart from opening or trashing them.
 */
function spek_contact_row_actions(array $actions, WP_Post $post): array
{
    if ($post->post_type !== 'spek_contact') {
        return $actions;
    }

    unset($actions['inline hide-if-no-js'], $actions['view']);
    return $actions;
}
add_filter('post_row_actions', 'spek_contact_row_actions', 10, 2);
