<?php
/**
 * Career application submissions, private file storage and WP admin management.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

function spek_register_career_applications_post_type(): void
{
    register_post_type('spek_job_app', [
        'labels' => [
            'name'               => __('Αιτήσεις Καριέρας', 'spek-theme'),
            'singular_name'      => __('Αίτηση Καριέρας', 'spek-theme'),
            'menu_name'          => __('Αιτήσεις Καριέρας', 'spek-theme'),
            'all_items'          => __('Όλες οι αιτήσεις', 'spek-theme'),
            'edit_item'          => __('Αίτηση υποψηφίου', 'spek-theme'),
            'search_items'       => __('Αναζήτηση αιτήσεων', 'spek-theme'),
            'not_found'          => __('Δεν βρέθηκαν αιτήσεις.', 'spek-theme'),
            'not_found_in_trash' => __('Δεν βρέθηκαν αιτήσεις στον κάδο.', 'spek-theme'),
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => false,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'has_archive'         => false,
        'rewrite'             => false,
        'menu_icon'           => 'dashicons-id-alt',
        'menu_position'       => 27,
        'supports'            => [],
        'map_meta_cap'        => true,
        'capabilities'        => [
            'create_posts' => 'do_not_allow',
        ],
    ]);
}
add_action('init', 'spek_register_career_applications_post_type', 6);

function spek_career_application_redirect_url(string $lang, string $status): string
{
    $lang = $lang === 'en' ? 'en' : 'el';
    $base = home_url('/careers/');

    if (function_exists('spek_i18n_url')) {
        $base = spek_i18n_url($base, $lang);
    }

    return add_query_arg('career_status', sanitize_key($status), $base) . '#career-application';
}

function spek_career_application_storage_dir(): string
{
    $candidates = [
        trailingslashit(dirname(rtrim(ABSPATH, '/\\'))) . 'spek-private-career-files',
        trailingslashit(WP_CONTENT_DIR) . 'spek-private-career-files',
    ];

    foreach ($candidates as $dir) {
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }

        if (!is_dir($dir) || !is_writable($dir)) {
            continue;
        }

        $index = trailingslashit($dir) . 'index.php';
        if (!file_exists($index)) {
            @file_put_contents($index, "<?php\n// Silence is golden.\n");
        }

        $htaccess = trailingslashit($dir) . '.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Require all denied\nDeny from all\n");
        }

        $webconfig = trailingslashit($dir) . 'web.config';
        if (!file_exists($webconfig)) {
            @file_put_contents($webconfig, '<?xml version="1.0" encoding="UTF-8"?><configuration><system.webServer><authorization><remove users="*" roles="" verbs=""/><add accessType="Deny" users="*"/></authorization></system.webServer></configuration>');
        }

        return $dir;
    }

    return '';
}

function spek_career_allowed_upload_extensions(): array
{
    return ['pdf', 'doc', 'docx'];
}

function spek_career_normalize_upload(array $files, int $index): array
{
    return [
        'name'     => isset($files['name'][$index]) ? (string) $files['name'][$index] : '',
        'type'     => isset($files['type'][$index]) ? (string) $files['type'][$index] : '',
        'tmp_name' => isset($files['tmp_name'][$index]) ? (string) $files['tmp_name'][$index] : '',
        'error'    => isset($files['error'][$index]) ? (int) $files['error'][$index] : UPLOAD_ERR_NO_FILE,
        'size'     => isset($files['size'][$index]) ? (int) $files['size'][$index] : 0,
    ];
}

function spek_career_store_private_upload(array $file, int $max_bytes)
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_error', __('Το αρχείο δεν ανέβηκε σωστά.', 'spek-theme'));
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > $max_bytes) {
        return new WP_Error('upload_size', __('Το αρχείο υπερβαίνει το επιτρεπόμενο μέγεθος.', 'spek-theme'));
    }

    $tmp_name = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
        return new WP_Error('upload_invalid', __('Μη έγκυρο αρχείο μεταφόρτωσης.', 'spek-theme'));
    }

    $original_name = sanitize_file_name((string) ($file['name'] ?? ''));
    $extension = strtolower((string) pathinfo($original_name, PATHINFO_EXTENSION));

    if (!in_array($extension, spek_career_allowed_upload_extensions(), true)) {
        return new WP_Error('upload_type', __('Επιτρέπονται μόνο αρχεία PDF, DOC και DOCX.', 'spek-theme'));
    }

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detected = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            $mime = is_string($detected) ? $detected : '';
        }
    }

    $allowed_mimes = [
        'pdf'  => ['application/pdf', 'application/x-pdf', 'application/octet-stream'],
        'doc'  => ['application/msword', 'application/CDFV2', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
    ];

    if ($mime !== '' && !in_array($mime, $allowed_mimes[$extension], true)) {
        return new WP_Error('upload_mime', __('Ο τύπος του αρχείου δεν είναι έγκυρος.', 'spek-theme'));
    }

    $dir = spek_career_application_storage_dir();
    if ($dir === '') {
        return new WP_Error('upload_storage', __('Δεν ήταν δυνατή η ασφαλής αποθήκευση του αρχείου.', 'spek-theme'));
    }

    $safe_name = wp_unique_filename(
        $dir,
        wp_generate_password(16, false, false) . '-' . ($original_name !== '' ? $original_name : ('document.' . $extension))
    );
    $destination = trailingslashit($dir) . $safe_name;

    if (!@move_uploaded_file($tmp_name, $destination)) {
        return new WP_Error('upload_move', __('Δεν ήταν δυνατή η αποθήκευση του αρχείου.', 'spek-theme'));
    }

    @chmod($destination, 0640);

    return [
        'path' => $destination,
        'name' => $original_name,
        'mime' => $mime,
        'size' => $size,
    ];
}

function spek_career_delete_file_record($record): void
{
    if (!is_array($record) || empty($record['path'])) {
        return;
    }

    $path = (string) $record['path'];
    $dir = spek_career_application_storage_dir();

    if ($dir === '') {
        return;
    }

    $real_dir = realpath($dir);
    $real_file = realpath($path);

    if ($real_dir && $real_file && strpos($real_file, $real_dir . DIRECTORY_SEPARATOR) === 0 && is_file($real_file)) {
        @unlink($real_file);
    }
}

function spek_career_application_position_label(int $position_id): string
{
    if ($position_id > 0 && get_post_type($position_id) === 'spek_career' && get_post_status($position_id) === 'publish') {
        return wp_strip_all_tags(get_the_title($position_id));
    }

    return __('Γενική αίτηση', 'spek-theme');
}

function spek_handle_career_application(): void
{
    $lang = isset($_POST['career_lang']) ? sanitize_key(wp_unslash((string) $_POST['career_lang'])) : 'el';
    $lang = $lang === 'en' ? 'en' : 'el';

    $nonce = isset($_POST['spek_career_nonce']) ? sanitize_text_field(wp_unslash((string) $_POST['spek_career_nonce'])) : '';
    if (!$nonce || !wp_verify_nonce($nonce, 'spek_career_apply')) {
        wp_safe_redirect(spek_career_application_redirect_url($lang, 'invalid'));
        exit;
    }

    $website = isset($_POST['website']) ? trim((string) wp_unslash($_POST['website'])) : '';
    if ($website !== '') {
        wp_safe_redirect(spek_career_application_redirect_url($lang, 'success'));
        exit;
    }

    $first_name = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash((string) $_POST['first_name'])) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash((string) $_POST['last_name'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash((string) $_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash((string) $_POST['phone'])) : '';
    $city = isset($_POST['city']) ? sanitize_text_field(wp_unslash((string) $_POST['city'])) : '';
    $employment_type = isset($_POST['employment_type']) ? sanitize_key(wp_unslash((string) $_POST['employment_type'])) : '';
    $department = isset($_POST['department']) ? sanitize_key(wp_unslash((string) $_POST['department'])) : '';
    $position_id = isset($_POST['position_id']) ? absint($_POST['position_id']) : 0;
    $experience_years = isset($_POST['experience_years']) ? max(0, min(60, absint($_POST['experience_years']))) : 0;
    $available_from = isset($_POST['available_from']) ? sanitize_text_field(wp_unslash((string) $_POST['available_from'])) : '';
    $linkedin = isset($_POST['linkedin']) ? esc_url_raw(wp_unslash((string) $_POST['linkedin'])) : '';
    $portfolio = isset($_POST['portfolio']) ? esc_url_raw(wp_unslash((string) $_POST['portfolio'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash((string) $_POST['message'])) : '';
    $privacy_ack = !empty($_POST['privacy_ack']);

    $valid_employment = ['full_time', 'part_time'];
    $valid_departments = ['', 'production', 'warehouse_logistics', 'technical', 'quality', 'sales', 'administration', 'other'];
    $message_length = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);

    $resume = isset($_FILES['resume']) && is_array($_FILES['resume']) ? $_FILES['resume'] : [];
    $resume_present = !empty($resume) && (($resume['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);

    $is_valid = $first_name !== ''
        && $last_name !== ''
        && is_email($email)
        && $phone !== ''
        && in_array($employment_type, $valid_employment, true)
        && in_array($department, $valid_departments, true)
        && $privacy_ack
        && $resume_present
        && $message_length <= 3000;

    if (!$is_valid) {
        wp_safe_redirect(spek_career_application_redirect_url($lang, 'invalid'));
        exit;
    }

    if ($position_id > 0 && (get_post_type($position_id) !== 'spek_career' || get_post_status($position_id) !== 'publish')) {
        $position_id = 0;
    }

    $position_label = spek_career_application_position_label($position_id);

    $application_id = wp_insert_post([
        'post_type'   => 'spek_job_app',
        'post_status' => 'publish',
        'post_title'  => sprintf('%s %s — %s', $first_name, $last_name, $position_label),
    ], true);

    if (is_wp_error($application_id) || !$application_id) {
        wp_safe_redirect(spek_career_application_redirect_url($lang, 'error'));
        exit;
    }

    $stored_resume = spek_career_store_private_upload($resume, 8 * MB_IN_BYTES);
    if (is_wp_error($stored_resume)) {
        wp_delete_post((int) $application_id, true);
        wp_safe_redirect(spek_career_application_redirect_url($lang, 'upload_error'));
        exit;
    }

    $stored_references = [];
    if (isset($_FILES['references']) && is_array($_FILES['references']) && isset($_FILES['references']['name']) && is_array($_FILES['references']['name'])) {
        $non_empty = [];
        foreach ($_FILES['references']['name'] as $index => $name) {
            if (trim((string) $name) !== '') {
                $non_empty[] = (int) $index;
            }
        }

        if (count($non_empty) > 5) {
            spek_career_delete_file_record($stored_resume);
            wp_delete_post((int) $application_id, true);
            wp_safe_redirect(spek_career_application_redirect_url($lang, 'too_many_files'));
            exit;
        }

        foreach ($non_empty as $index) {
            $file = spek_career_normalize_upload($_FILES['references'], $index);
            $stored = spek_career_store_private_upload($file, 5 * MB_IN_BYTES);

            if (is_wp_error($stored)) {
                spek_career_delete_file_record($stored_resume);
                foreach ($stored_references as $record) {
                    spek_career_delete_file_record($record);
                }
                wp_delete_post((int) $application_id, true);
                wp_safe_redirect(spek_career_application_redirect_url($lang, 'upload_error'));
                exit;
            }

            $stored_references[] = $stored;
        }
    }

    $meta = [
        '_spek_job_first_name'        => $first_name,
        '_spek_job_last_name'         => $last_name,
        '_spek_job_email'             => $email,
        '_spek_job_phone'             => $phone,
        '_spek_job_city'              => $city,
        '_spek_job_employment_type'   => $employment_type,
        '_spek_job_department'        => $department,
        '_spek_job_position_id'       => $position_id,
        '_spek_job_position_label'    => $position_label,
        '_spek_job_experience_years'  => $experience_years,
        '_spek_job_available_from'    => $available_from,
        '_spek_job_linkedin'          => $linkedin,
        '_spek_job_portfolio'         => $portfolio,
        '_spek_job_message'           => $message,
        '_spek_job_resume'            => $stored_resume,
        '_spek_job_references'        => $stored_references,
        '_spek_job_lang'              => $lang,
        '_spek_job_privacy_ack'       => '1',
    ];

    foreach ($meta as $key => $value) {
        update_post_meta((int) $application_id, $key, $value);
    }

    $employment_label = $employment_type === 'part_time'
        ? __('Μερική απασχόληση', 'spek-theme')
        : __('Πλήρης απασχόληση', 'spek-theme');

    $department_labels = [
        'production'          => __('Παραγωγή', 'spek-theme'),
        'warehouse_logistics' => __('Αποθήκη & Logistics', 'spek-theme'),
        'technical'           => __('Τεχνικό / Μηχανικό', 'spek-theme'),
        'quality'             => __('Ποιότητα', 'spek-theme'),
        'sales'               => __('Πωλήσεις / Εξυπηρέτηση', 'spek-theme'),
        'administration'      => __('Διοίκηση / Οικονομικά', 'spek-theme'),
        'other'               => __('Άλλο', 'spek-theme'),
        ''                    => __('Δεν δηλώθηκε', 'spek-theme'),
    ];

    $recipient = (string) apply_filters('spek_career_application_recipient', get_option('admin_email'), (int) $application_id);
    $admin_url = admin_url('post.php?post=' . (int) $application_id . '&action=edit');
    $subject = sprintf('[SPEK Careers] %s %s — %s', $first_name, $last_name, $position_label);
    $body = '<p><strong>Νέα αίτηση καριέρας</strong></p>'
        . '<p><strong>Υποψήφιος:</strong> ' . esc_html($first_name . ' ' . $last_name) . '<br>'
        . '<strong>Email:</strong> ' . esc_html($email) . '<br>'
        . '<strong>Τηλέφωνο:</strong> ' . esc_html($phone) . '<br>'
        . '<strong>Θέση:</strong> ' . esc_html($position_label) . '<br>'
        . '<strong>Απασχόληση:</strong> ' . esc_html($employment_label) . '<br>'
        . '<strong>Τομέας:</strong> ' . esc_html($department_labels[$department] ?? $department_labels['']) . '</p>'
        . ($message !== '' ? '<p><strong>Σημείωμα:</strong><br>' . nl2br(esc_html($message)) . '</p>' : '')
        . '<p><a href="' . esc_url($admin_url) . '">Προβολή αίτησης στο WP Admin →</a></p>';

    $attachments = [(string) $stored_resume['path']];
    foreach ($stored_references as $record) {
        if (!empty($record['path'])) {
            $attachments[] = (string) $record['path'];
        }
    }

    if ($recipient !== '' && is_email($recipient)) {
        wp_mail(
            $recipient,
            $subject,
            $body,
            [
                'Content-Type: text/html; charset=UTF-8',
                sprintf('Reply-To: %s %s <%s>', $first_name, $last_name, $email),
            ],
            $attachments
        );
    }

    $ack_subject = $lang === 'en' ? 'SPEK · Application received' : 'SPEK · Παραλάβαμε την αίτησή σας';
    $ack_body = $lang === 'en'
        ? sprintf('Hello %s, we received your application for %s. Thank you for your interest in SPEK.', $first_name, $position_label)
        : sprintf('Γεια σας %s, παραλάβαμε την αίτησή σας για %s. Ευχαριστούμε για το ενδιαφέρον σας για τη SPEK.', $first_name, $position_label);

    wp_mail($email, $ack_subject, $ack_body, ['Content-Type: text/plain; charset=UTF-8']);

    wp_safe_redirect(spek_career_application_redirect_url($lang, 'success'));
    exit;
}
add_action('admin_post_spek_career_apply', 'spek_handle_career_application');
add_action('admin_post_nopriv_spek_career_apply', 'spek_handle_career_application');

function spek_career_download_url(int $application_id, string $kind, int $index = 0): string
{
    return wp_nonce_url(
        add_query_arg([
            'action' => 'spek_career_download',
            'application_id' => $application_id,
            'kind' => $kind,
            'index' => $index,
        ], admin_url('admin-post.php')),
        'spek_career_download_' . $application_id
    );
}

function spek_handle_career_download(): void
{
    if (!current_user_can('edit_posts')) {
        wp_die(esc_html__('Δεν έχετε δικαίωμα πρόσβασης σε αυτό το αρχείο.', 'spek-theme'), 403);
    }

    $application_id = isset($_GET['application_id']) ? absint($_GET['application_id']) : 0;
    check_admin_referer('spek_career_download_' . $application_id);

    if ($application_id <= 0 || get_post_type($application_id) !== 'spek_job_app') {
        wp_die(esc_html__('Η αίτηση δεν βρέθηκε.', 'spek-theme'), 404);
    }

    $kind = isset($_GET['kind']) ? sanitize_key(wp_unslash((string) $_GET['kind'])) : '';
    $index = isset($_GET['index']) ? absint($_GET['index']) : 0;
    $record = null;

    if ($kind === 'resume') {
        $record = get_post_meta($application_id, '_spek_job_resume', true);
    } elseif ($kind === 'reference') {
        $references = get_post_meta($application_id, '_spek_job_references', true);
        if (is_array($references) && isset($references[$index])) {
            $record = $references[$index];
        }
    }

    if (!is_array($record) || empty($record['path']) || !is_file((string) $record['path'])) {
        wp_die(esc_html__('Το αρχείο δεν βρέθηκε.', 'spek-theme'), 404);
    }

    $dir = realpath(spek_career_application_storage_dir());
    $file = realpath((string) $record['path']);
    if (!$dir || !$file || strpos($file, $dir . DIRECTORY_SEPARATOR) !== 0) {
        wp_die(esc_html__('Μη έγκυρη διαδρομή αρχείου.', 'spek-theme'), 403);
    }

    $download_name = sanitize_file_name((string) ($record['name'] ?? basename($file)));
    $mime = !empty($record['mime']) ? (string) $record['mime'] : 'application/octet-stream';

    nocache_headers();
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . rawurlencode($download_name) . '"');
    header('Content-Length: ' . (string) filesize($file));
    readfile($file);
    exit;
}
add_action('admin_post_spek_career_download', 'spek_handle_career_download');

function spek_career_application_details_box(WP_Post $post): void
{
    $fields = [
        __('Όνομα', 'spek-theme') => trim((string) get_post_meta($post->ID, '_spek_job_first_name', true) . ' ' . (string) get_post_meta($post->ID, '_spek_job_last_name', true)),
        __('Email', 'spek-theme') => (string) get_post_meta($post->ID, '_spek_job_email', true),
        __('Τηλέφωνο', 'spek-theme') => (string) get_post_meta($post->ID, '_spek_job_phone', true),
        __('Πόλη / Περιοχή', 'spek-theme') => (string) get_post_meta($post->ID, '_spek_job_city', true),
        __('Θέση ενδιαφέροντος', 'spek-theme') => (string) get_post_meta($post->ID, '_spek_job_position_label', true),
        __('Τύπος απασχόλησης', 'spek-theme') => (string) get_post_meta($post->ID, '_spek_job_employment_type', true),
        __('Τομέας', 'spek-theme') => (string) get_post_meta($post->ID, '_spek_job_department', true),
        __('Έτη εμπειρίας', 'spek-theme') => (string) get_post_meta($post->ID, '_spek_job_experience_years', true),
        __('Διαθεσιμότητα από', 'spek-theme') => (string) get_post_meta($post->ID, '_spek_job_available_from', true),
        'LinkedIn' => (string) get_post_meta($post->ID, '_spek_job_linkedin', true),
        __('Portfolio', 'spek-theme') => (string) get_post_meta($post->ID, '_spek_job_portfolio', true),
    ];

    echo '<table class="widefat striped" style="margin-bottom:16px"><tbody>';
    foreach ($fields as $label => $value) {
        if ($value === '') {
            continue;
        }
        echo '<tr><th style="width:220px">' . esc_html($label) . '</th><td>' . esc_html($value) . '</td></tr>';
    }
    echo '</tbody></table>';

    $message = (string) get_post_meta($post->ID, '_spek_job_message', true);
    if ($message !== '') {
        echo '<h4>' . esc_html__('Συνοδευτικό σημείωμα', 'spek-theme') . '</h4><p style="white-space:pre-wrap">' . esc_html($message) . '</p>';
    }

    $resume = get_post_meta($post->ID, '_spek_job_resume', true);
    if (is_array($resume) && !empty($resume['name'])) {
        echo '<p><strong>' . esc_html__('Βιογραφικό:', 'spek-theme') . '</strong> <a class="button" href="' . esc_url(spek_career_download_url($post->ID, 'resume')) . '">' . esc_html($resume['name']) . '</a></p>';
    }

    $references = get_post_meta($post->ID, '_spek_job_references', true);
    if (is_array($references) && $references) {
        echo '<p><strong>' . esc_html__('Συστατικές επιστολές:', 'spek-theme') . '</strong></p><p>';
        foreach ($references as $index => $record) {
            if (!is_array($record) || empty($record['name'])) {
                continue;
            }
            echo '<a class="button" style="margin:0 8px 8px 0" href="' . esc_url(spek_career_download_url($post->ID, 'reference', (int) $index)) . '">' . esc_html((string) $record['name']) . '</a>';
        }
        echo '</p>';
    }
}

add_action('add_meta_boxes_spek_job_app', static function (): void {
    add_meta_box(
        'spek-job-application-details',
        __('Στοιχεία αίτησης', 'spek-theme'),
        'spek_career_application_details_box',
        'spek_job_app',
        'normal',
        'high'
    );
});

add_filter('manage_spek_job_app_posts_columns', static function (array $columns): array {
    return [
        'cb' => $columns['cb'] ?? '<input type="checkbox" />',
        'title' => __('Υποψήφιος', 'spek-theme'),
        'position' => __('Θέση', 'spek-theme'),
        'employment' => __('Απασχόληση', 'spek-theme'),
        'email' => __('Email', 'spek-theme'),
        'date' => __('Ημερομηνία', 'spek-theme'),
    ];
});

add_action('manage_spek_job_app_posts_custom_column', static function (string $column, int $post_id): void {
    if ($column === 'position') {
        echo esc_html((string) get_post_meta($post_id, '_spek_job_position_label', true));
    } elseif ($column === 'employment') {
        $type = (string) get_post_meta($post_id, '_spek_job_employment_type', true);
        echo esc_html($type === 'part_time' ? __('Μερική απασχόληση', 'spek-theme') : __('Πλήρης απασχόληση', 'spek-theme'));
    } elseif ($column === 'email') {
        echo esc_html((string) get_post_meta($post_id, '_spek_job_email', true));
    }
}, 10, 2);

add_action('before_delete_post', static function (int $post_id): void {
    if (get_post_type($post_id) !== 'spek_job_app') {
        return;
    }

    spek_career_delete_file_record(get_post_meta($post_id, '_spek_job_resume', true));
    $references = get_post_meta($post_id, '_spek_job_references', true);
    if (is_array($references)) {
        foreach ($references as $record) {
            spek_career_delete_file_record($record);
        }
    }
});
