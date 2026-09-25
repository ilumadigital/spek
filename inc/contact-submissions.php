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
 * Shared visual shell for SPEK HTML emails.
 */
function spek_contact_email_shell(
    string $preheader,
    string $eyebrow,
    string $title,
    string $intro,
    string $content,
    string $lang = 'el'
): string {
    $logo_url = get_template_directory_uri() . '/assets/images/brand/spek_logo.png';
    $home_url = function_exists('spek_i18n_url')
        ? spek_i18n_url(home_url('/'), $lang === 'en' ? 'en' : 'el')
        : home_url('/');

    $footer_copy = $lang === 'en'
        ? 'SPEK · Plumbing & sanitary solutions · Since 1990'
        : 'SPEK · Λύσεις υδραυλικών & ειδών υγιεινής · Από το 1990';

    return '<!doctype html>
<html lang="' . esc_attr($lang === 'en' ? 'en' : 'el') . '">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . esc_html($title) . '</title>
</head>
<body style="margin:0;padding:0;background:#f3f6f9;font-family:Arial,Helvetica,sans-serif;color:#142331;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">' . esc_html($preheader) . '</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f3f6f9;margin:0;padding:0;">
<tr>
<td align="center" style="padding:32px 14px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:680px;background:#ffffff;border:1px solid #e3e9ee;border-radius:28px;overflow:hidden;box-shadow:0 16px 45px rgba(15,41,62,.08);">
<tr>
<td style="height:5px;font-size:0;line-height:0;background:linear-gradient(90deg,#174f8a 0%,#2e94dd 52%,#174f8a 100%);">&nbsp;</td>
</tr>
<tr>
<td style="padding:30px 34px 24px;background:#ffffff;border-bottom:1px solid #e8edf1;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td valign="middle">
<a href="' . esc_url($home_url) . '" style="text-decoration:none;">
<img src="' . esc_url($logo_url) . '" width="120" alt="SPEK" style="display:block;width:120px;max-width:120px;height:auto;border:0;">
</a>
</td>
<td align="right" valign="middle" style="font-size:11px;line-height:1.3;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#174f8a;">' . esc_html($eyebrow) . '</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="padding:38px 34px 10px;">
<div style="width:44px;height:2px;background:#174f8a;margin-bottom:20px;"></div>
<h1 style="margin:0 0 16px;font-size:32px;line-height:1.05;letter-spacing:-1.2px;color:#101f2c;">' . esc_html($title) . '</h1>
<p style="margin:0;color:#667681;font-size:16px;line-height:1.7;">' . esc_html($intro) . '</p>
</td>
</tr>
<tr>
<td style="padding:24px 34px 38px;">' . $content . '</td>
</tr>
<tr>
<td style="padding:22px 34px;background:#0f365b;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td style="color:#ffffff;font-size:12px;line-height:1.55;font-weight:700;">SPEK</td>
<td align="right" style="color:rgba(255,255,255,.72);font-size:11px;line-height:1.55;">' . esc_html($footer_copy) . '</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>';
}

/**
 * Render one detail row for the email templates.
 */
function spek_contact_email_detail_row(string $label, string $value, bool $last = false): string
{
    return '<tr>
<td width="34%" valign="top" style="padding:14px 14px 14px 0;border-bottom:' . ($last ? '0' : '1px solid #e8edf1') . ';color:#74838e;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;">' . esc_html($label) . '</td>
<td valign="top" style="padding:14px 0;border-bottom:' . ($last ? '0' : '1px solid #e8edf1') . ';color:#172735;font-size:14px;font-weight:700;line-height:1.55;">' . esc_html($value) . '</td>
</tr>';
}

/**
 * Internal SPEK notification email.
 */
function spek_contact_internal_email(array $data, int $submission_id): array
{
    $type_label = spek_contact_request_type_label((string) $data['request_type'], 'el');
    $reference = 'SPEK-' . str_pad((string) $submission_id, 6, '0', STR_PAD_LEFT);
    $admin_url = admin_url('post.php?post=' . $submission_id . '&action=edit');
    $submitted_at = get_the_date('d/m/Y H:i', $submission_id);

    $details = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border:1px solid #e4eaf0;border-radius:18px;background:#fbfcfd;padding:4px 18px;">'
        . spek_contact_email_detail_row('Κωδικός', $reference)
        . spek_contact_email_detail_row('Υποβλήθηκε', $submitted_at)
        . spek_contact_email_detail_row('Τύπος αιτήματος', $type_label)
        . spek_contact_email_detail_row('Όνομα', (string) $data['first_name'])
        . spek_contact_email_detail_row('Επώνυμο', (string) $data['last_name'])
        . spek_contact_email_detail_row('Email', (string) $data['email'])
        . spek_contact_email_detail_row('Τηλέφωνο', (string) $data['phone'])
        . spek_contact_email_detail_row('Θέμα', (string) $data['subject'])
        . spek_contact_email_detail_row('Γλώσσα φόρμας', strtoupper((string) $data['lang']), true)
        . '</table>';

    $message = '<div style="margin-top:22px;padding:20px 22px;border-left:3px solid #174f8a;border-radius:0 16px 16px 0;background:#f5f8fb;">
<div style="margin-bottom:9px;color:#174f8a;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">Μήνυμα</div>
<div style="white-space:pre-wrap;color:#253746;font-size:15px;line-height:1.7;">' . nl2br(esc_html((string) $data['message'])) . '</div>
</div>';

    $button = '<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:24px;"><tr><td style="border-radius:10px;background:#174f8a;">
<a href="' . esc_url($admin_url) . '" style="display:inline-block;padding:13px 20px;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;">Προβολή στο WP Admin →</a>
</td></tr></table>';

    $body = spek_contact_email_shell(
        'Νέο αίτημα επικοινωνίας από τη φόρμα SPEK.',
        'NEW CONTACT',
        'Νέα υποβολή φόρμας',
        'Ένα νέο αίτημα αποθηκεύτηκε στο website και είναι διαθέσιμο και στο WP Admin.',
        $details . $message . $button,
        'el'
    );

    $subject = sprintf('[SPEK Contact] %s · %s · %s', $type_label, $reference, (string) $data['subject']);

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        sprintf(
            'Reply-To: %s <%s>',
            trim((string) $data['first_name'] . ' ' . (string) $data['last_name']),
            (string) $data['email']
        ),
    ];

    return [$subject, $body, $headers];
}

/**
 * Customer receipt confirmation email.
 */
function spek_contact_customer_email(array $data, int $submission_id): array
{
    $lang = $data['lang'] === 'en' ? 'en' : 'el';
    $reference = 'SPEK-' . str_pad((string) $submission_id, 6, '0', STR_PAD_LEFT);
    $type_label = spek_contact_request_type_label((string) $data['request_type'], $lang);
    $contact_url = function_exists('spek_i18n_url')
        ? spek_i18n_url(home_url('/contact/'), $lang)
        : home_url('/contact/');

    if ($lang === 'en') {
        $eyebrow = 'MESSAGE RECEIVED';
        $title = 'We received your message.';
        $intro = sprintf(
            'Hello %s, thank you for contacting SPEK. Your enquiry has been received and will be reviewed by our team.',
            (string) $data['first_name']
        );
        $subject = sprintf('SPEK · We received your enquiry · %s', $reference);
        $labels = [
            'reference' => 'Reference',
            'type'      => 'Enquiry type',
            'subject'   => 'Subject',
        ];
        $note = 'Please keep this reference if you need to contact us about the same enquiry.';
        $button_label = 'Visit SPEK website →';
        $phone_label = 'For direct contact';
    } else {
        $eyebrow = 'ΠΑΡΑΛΑΒΗ ΜΗΝΥΜΑΤΟΣ';
        $title = 'Λάβαμε το μήνυμά σας.';
        $intro = sprintf(
            'Γεια σας %s, ευχαριστούμε που επικοινωνήσατε με τη SPEK. Το αίτημά σας έχει παραληφθεί και θα εξεταστεί από την ομάδα μας.',
            (string) $data['first_name']
        );
        $subject = sprintf('SPEK · Επιβεβαίωση παραλαβής · %s', $reference);
        $labels = [
            'reference' => 'Κωδικός αναφοράς',
            'type'      => 'Τύπος αιτήματος',
            'subject'   => 'Θέμα',
        ];
        $note = 'Κρατήστε τον κωδικό αναφοράς σε περίπτωση που χρειαστεί να επικοινωνήσετε μαζί μας για το ίδιο αίτημα.';
        $button_label = 'Επιστροφή στο SPEK →';
        $phone_label = 'Για άμεση επικοινωνία';
    }

    $details = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border:1px solid #e4eaf0;border-radius:18px;background:#fbfcfd;padding:4px 18px;">'
        . spek_contact_email_detail_row($labels['reference'], $reference)
        . spek_contact_email_detail_row($labels['type'], $type_label)
        . spek_contact_email_detail_row($labels['subject'], (string) $data['subject'], true)
        . '</table>';

    $note_box = '<div style="margin-top:20px;padding:17px 19px;border-radius:14px;background:#f4f7fa;color:#667681;font-size:13px;line-height:1.65;">'
        . esc_html($note)
        . '</div>';

    $action = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:24px;">
<tr>
<td valign="middle">
<table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr><td style="border-radius:10px;background:#174f8a;">
<a href="' . esc_url($contact_url) . '" style="display:inline-block;padding:13px 20px;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;">' . esc_html($button_label) . '</a>
</td></tr></table>
</td>
<td align="right" valign="middle" style="color:#6f7e89;font-size:11px;line-height:1.5;">'
        . esc_html($phone_label)
        . '<br><strong style="color:#172735;font-size:14px;">+30 22620 75000</strong></td>
</tr>
</table>';

    $body = spek_contact_email_shell(
        $title,
        $eyebrow,
        $title,
        $intro,
        $details . $note_box . $action,
        $lang
    );

    return [
        $subject,
        $body,
        ['Content-Type: text/html; charset=UTF-8'],
    ];
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
        '_spek_contact_ack_status'   => 'pending',
    ];

    foreach ($submission_meta as $meta_key => $meta_value) {
        update_post_meta((int) $submission_id, $meta_key, $meta_value);
    }

    $recipient = spek_contact_destination($request_type);

    $mail_data = [
        'request_type' => $request_type,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'email'        => $email,
        'phone'        => $phone,
        'subject'      => $subject,
        'message'      => $message,
        'lang'         => $lang,
    ];

    [$internal_subject, $internal_body, $internal_headers] = spek_contact_internal_email(
        $mail_data,
        (int) $submission_id
    );

    $internal_sent = $recipient !== ''
        ? wp_mail($recipient, $internal_subject, $internal_body, $internal_headers)
        : false;

    [$customer_subject, $customer_body, $customer_headers] = spek_contact_customer_email(
        $mail_data,
        (int) $submission_id
    );

    $customer_sent = wp_mail(
        $email,
        $customer_subject,
        $customer_body,
        $customer_headers
    );

    update_post_meta(
        (int) $submission_id,
        '_spek_contact_mail_status',
        $internal_sent ? 'sent' : 'failed'
    );

    update_post_meta(
        (int) $submission_id,
        '_spek_contact_ack_status',
        $customer_sent ? 'sent' : 'failed'
    );

    // The request is safely stored in Contacts even if a mail transport fails.
    wp_safe_redirect(spek_contact_redirect_url($lang, 'success'));
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
    $ack_status   = (string) get_post_meta($post->ID, '_spek_contact_ack_status', true);

    $rows = [
        'Τύπος αιτήματος' => spek_contact_request_type_label($request_type, 'el'),
        'Όνομα'           => $first_name,
        'Επώνυμο'         => $last_name,
        'Email'           => $email,
        'Τηλέφωνο'        => $phone,
        'Θέμα'            => $subject,
        'Γλώσσα'          => strtoupper($lang ?: 'el'),
        'Email προς SPEK' => $mail_status === 'sent' ? 'Sent' : ($mail_status === 'failed' ? 'Failed' : 'Pending'),
        'Επιβεβαίωση πελάτη' => $ack_status === 'sent' ? 'Sent' : ($ack_status === 'failed' ? 'Failed' : 'Pending'),
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
        'mail_status'  => 'Emails',
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
        $internal = (string) get_post_meta($post_id, '_spek_contact_mail_status', true);
        $customer = (string) get_post_meta($post_id, '_spek_contact_ack_status', true);

        $internal_label = $internal === 'sent' ? 'Sent' : ($internal === 'failed' ? 'Failed' : 'Pending');
        $customer_label = $customer === 'sent' ? 'Sent' : ($customer === 'failed' ? 'Failed' : 'Pending');

        echo '<strong>SPEK:</strong> ' . esc_html($internal_label);
        echo '<br><strong>Πελάτης:</strong> ' . esc_html($customer_label);
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
