<?php
/**
 * Partner review inbox: audit imported partners and unresolved spreadsheet rows.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

const SPEK_PARTNER_REVIEW_QUEUE_OPTION = 'spek_partner_review_queue';

/**
 * Persist only importer rows that could not safely be attached to a partner.
 * Dry runs must never write review items.
 */
function spek_partner_review_queue_issue(array $row, string $kind, string $message): void
{
    $source_row = (int) ($row['_source_row'] ?? 0);
    $key = hash('sha256', $kind . '|' . spek_partner_import_key($row) . '|' . $source_row);
    $queue = get_option(SPEK_PARTNER_REVIEW_QUEUE_OPTION, []);
    if (!is_array($queue)) {
        $queue = [];
    }
    if (isset($queue[$key]) && in_array((string) ($queue[$key]['status'] ?? ''), ['rejected', 'linked'], true)) {
        return;
    }
    $fields = ['name', 'address', 'area', 'postcode', 'phone', 'region', 'email', 'maps_url', 'website', 'contact_person'];
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = sanitize_text_field((string) ($row[$field] ?? ''));
    }
    $data['maps_url'] = esc_url_raw((string) ($row['maps_url'] ?? ''));
    $data['email'] = sanitize_email((string) ($row['email'] ?? ''));
    $data['website'] = esc_url_raw((string) ($row['website'] ?? ''));
    $queue[$key] = [
        'status' => 'pending',
        'type' => sanitize_key($kind),
        'source_row' => $source_row,
        'data' => $data,
        'message' => sanitize_text_field($message),
        'created' => current_time('mysql'),
    ];
    update_option(SPEK_PARTNER_REVIEW_QUEUE_OPTION, $queue, false);
}

function spek_partner_review_queue(): array
{
    $queue = get_option(SPEK_PARTNER_REVIEW_QUEUE_OPTION, []);
    return is_array($queue) ? $queue : [];
}

function spek_partner_review_has_coordinates(int $post_id): bool
{
    $lat = get_post_meta($post_id, 'partner_latitude', true);
    $lng = get_post_meta($post_id, 'partner_longitude', true);
    if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
        return false;
    }
    if (!((float) $lat >= -90 && (float) $lat <= 90
        && (float) $lng >= -180 && (float) $lng <= 180)) {
        return false;
    }

    if (function_exists('spek_partner_import_coords_plausible')) {
        return spek_partner_import_coords_plausible(
            ['postcode' => (string) get_post_meta($post_id, 'partner_postcode', true)],
            ['latitude' => $lat, 'longitude' => $lng]
        );
    }
    return true;
}

/** Status for the review UI; confirmation does not hide missing map pins. */
function spek_partner_review_status(int $post_id): string
{
    $decision = (string) get_post_meta($post_id, '_spek_partner_review_resolution', true);
    $suggestion = (string) get_post_meta($post_id, '_spek_partner_suggested_maps_url', true);
    $match = (string) get_post_meta($post_id, '_spek_partner_google_match_status', true);

    if (($suggestion !== '' || $decision === 'pending')
        && $decision !== 'rejected' && $decision !== 'approved'
        && spek_partner_review_has_coordinates($post_id)) {
        return 'pending';
    }
    if ($suggestion !== '' && $decision !== 'rejected' && $decision !== 'approved') {
        return 'pending';
    }
    if ($decision === 'rejected') {
        return 'rejected';
    }
    if (!spek_partner_review_has_coordinates($post_id)) {
        return 'missing';
    }
    if ($decision === 'approved') {
        return 'approved';
    }
    if (in_array($match, ['review', 'error', 'no_key'], true)) {
        return 'pending';
    }
    return 'ok';
}

function spek_partner_review_label(string $status): string
{
    $labels = [
        'pending' => 'Για έγκριση',
        'missing' => 'Χωρίς ακριβές pin',
        'approved' => 'Εγκρίθηκε',
        'rejected' => 'Απορρίφθηκε',
        'ok' => 'Χωρίς εκκρεμότητα',
    ];
    return $labels[$status] ?? $status;
}

function spek_partner_review_admin_menu(): void
{
    add_submenu_page(
        'edit.php?post_type=spek_partner',
        __('Έλεγχος συνεργατών', 'spek-theme'),
        __('Έλεγχος συνεργατών', 'spek-theme'),
        'manage_options',
        'spek-partner-review',
        'spek_partner_review_render_page'
    );
}
add_action('admin_menu', 'spek_partner_review_admin_menu');

function spek_partner_review_admin_styles(string $hook): void
{
    if (strpos($hook, 'spek-partner-review') === false) {
        return;
    }
    wp_enqueue_style(
        'spek-partner-review',
        SPEK_THEME_URI . '/assets/css/partner-review-admin.css',
        [],
        filemtime(SPEK_THEME_DIR . '/assets/css/partner-review-admin.css')
    );
    wp_enqueue_script(
        'spek-partner-review-bulk',
        SPEK_THEME_URI . '/assets/js/partner-review-bulk.js',
        [],
        filemtime(SPEK_THEME_DIR . '/assets/js/partner-review-bulk.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'spek_partner_review_admin_styles');

function spek_partner_review_back_url(): string
{
    $default = admin_url('edit.php?post_type=spek_partner&page=spek-partner-review');
    $candidate = isset($_POST['return_url'])
        ? esc_url_raw(wp_unslash((string) $_POST['return_url']))
        : $default;
    return wp_validate_redirect($candidate, $default);
}

/** Approve/edit/reject existing partner, or create a draft from an orphan row. */
function spek_partner_review_handle_action(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Δεν επιτρέπεται αυτή η ενέργεια.', 'spek-theme'), '', ['response' => 403]);
    }
    $action = isset($_POST['review_action'])
        ? sanitize_key(wp_unslash((string) $_POST['review_action'])) : '';
    $return_url = spek_partner_review_back_url();

    if (in_array($action, ['save', 'approve', 'reject'], true)) {
        $id = absint($_POST['partner_id'] ?? 0);
        check_admin_referer('spek_partner_review_' . $id);
        if (!$id || get_post_type($id) !== 'spek_partner' || !current_user_can('edit_post', $id)) {
            wp_die(esc_html__('Μη έγκυρος συνεργάτης.', 'spek-theme'), '', ['response' => 400]);
        }

        if ($action !== 'reject') {
            // Validate everything before touching the live partner record.
            $name = trim(sanitize_text_field(wp_unslash((string) ($_POST['partner_name'] ?? ''))));
            if ($name === '') {
                spek_partner_review_redirect($return_url, 'empty_name');
            }

            $values = [];
            $fields = [
                'partner_address' => 'text', 'partner_area' => 'text',
                'partner_postcode' => 'text', 'partner_phone' => 'text',
                'partner_email' => 'email', 'partner_latitude' => 'lat',
                'partner_longitude' => 'lng',
            ];
            foreach ($fields as $field => $kind) {
                $value = trim((string) wp_unslash($_POST[$field] ?? ''));
                if ($kind === 'email') {
                    $value = sanitize_email($value);
                } elseif ($kind === 'lat' || $kind === 'lng') {
                    if ($value !== '' && (!is_numeric($value)
                        || ($kind === 'lat' && ((float) $value < -90 || (float) $value > 90))
                        || ($kind === 'lng' && ((float) $value < -180 || (float) $value > 180)))) {
                        spek_partner_review_redirect($return_url, 'invalid_coords');
                    }
                } else {
                    $value = sanitize_text_field($value);
                }
                $values[$field] = $value;
            }
            if (($values['partner_latitude'] === '') !== ($values['partner_longitude'] === '')) {
                spek_partner_review_redirect($return_url, 'invalid_coords');
            }

            $maps_url = trim((string) wp_unslash($_POST['partner_maps_url'] ?? ''));
            if ($maps_url !== '') {
                $maps_url = function_exists('spek_partner_import_normalize_maps_url')
                    ? spek_partner_import_normalize_maps_url($maps_url)
                    : esc_url_raw($maps_url);
                if ($maps_url === '') {
                    spek_partner_review_redirect($return_url, 'invalid_url');
                }
            }
            if ($action === 'approve'
                && (string) get_post_meta($id, '_spek_partner_suggested_maps_url', true) !== ''
                && $maps_url === '') {
                spek_partner_review_redirect($return_url, 'missing_url');
            }

            if ($name !== get_the_title($id)) {
                $updated = wp_update_post(wp_slash(['ID' => $id, 'post_title' => $name]), true);
                if (is_wp_error($updated)) {
                    spek_partner_review_redirect($return_url, 'failed');
                }
            }
            foreach ($values as $field => $value) {
                update_post_meta($id, $field, $value);
            }
            update_post_meta($id, 'partner_maps_url', $maps_url);
            // A corrected set of coordinates can resolve an old suspect-pin warning.
            if (spek_partner_review_has_coordinates($id)) {
                delete_post_meta($id, '_spek_partner_pin_needs_review');
            }

            if ($action === 'approve') {
                if ($maps_url !== '') {
                    $query = (string) wp_parse_url($maps_url, PHP_URL_QUERY);
                    parse_str($query, $params);
                    if (!empty($params['query_place_id'])) {
                        update_post_meta($id, '_spek_partner_google_place_id', sanitize_text_field((string) $params['query_place_id']));
                    } elseif (!empty($params['destination_place_id'])) {
                        update_post_meta($id, '_spek_partner_google_place_id', sanitize_text_field((string) $params['destination_place_id']));
                    } else {
                        update_post_meta($id, '_spek_partner_google_place_id', '');
                    }
                    update_post_meta($id, '_spek_partner_review_accepted_url', $maps_url);
                }
                delete_post_meta($id, '_spek_partner_suggested_maps_url');
                update_post_meta($id, '_spek_partner_google_match_status', 'approved');
                update_post_meta($id, '_spek_partner_review_resolution', 'approved');
            } else {
                update_post_meta($id, '_spek_partner_review_resolution', 'pending');
            }
        } else {
            // Reject only the proposed Google match. Published details stay intact.
            $proposal = (string) get_post_meta($id, '_spek_partner_suggested_maps_url', true);
            if ($proposal !== '') {
                update_post_meta($id, '_spek_partner_review_rejected_url', $proposal);
            }
            delete_post_meta($id, '_spek_partner_suggested_maps_url');
            update_post_meta($id, '_spek_partner_review_resolution', 'rejected');
        }
        update_post_meta($id, '_spek_partner_reviewed_by', get_current_user_id());
        update_post_meta($id, '_spek_partner_reviewed_at', current_time('mysql'));
        spek_partner_review_redirect($return_url, $action === 'save' ? 'saved' : ($action === 'approve' ? 'approved' : 'rejected'));
    }

    if (in_array($action, ['create_draft', 'dismiss'], true)) {
        $key = isset($_POST['queue_key']) ? sanitize_text_field(wp_unslash((string) $_POST['queue_key'])) : '';
        check_admin_referer('spek_partner_queue_' . $key);
        $queue = spek_partner_review_queue();
        if (!isset($queue[$key]) || ($queue[$key]['status'] ?? '') !== 'pending') {
            spek_partner_review_redirect($return_url, 'missing_queue');
        }
        if ($action === 'dismiss') {
            $queue[$key]['status'] = 'rejected';
            $queue[$key]['reviewed_by'] = get_current_user_id();
            update_option(SPEK_PARTNER_REVIEW_QUEUE_OPTION, $queue, false);
            spek_partner_review_redirect($return_url, 'rejected');
        }

        $row = (array) ($queue[$key]['data'] ?? []);
        $name = trim(sanitize_text_field(wp_unslash((string) ($_POST['partner_name'] ?? ($row['name'] ?? '')))));
        if ($name === '') {
            spek_partner_review_redirect($return_url, 'empty_name');
        }
        // Never publish a potentially duplicated spreadsheet row automatically.
        $id = wp_insert_post(wp_slash([
            'post_type' => 'spek_partner',
            'post_title' => $name,
            'post_status' => 'draft',
        ]), true);
        if (is_wp_error($id) || !$id) {
            spek_partner_review_redirect($return_url, 'failed');
        }
        $mapping = [
            'address' => 'partner_address', 'area' => 'partner_area',
            'postcode' => 'partner_postcode', 'phone' => 'partner_phone',
            'email' => 'partner_email', 'website' => 'partner_website',
            'maps_url' => 'partner_maps_url', 'contact_person' => 'partner_contact_person',
        ];
        foreach ($mapping as $source => $destination) {
            if (!empty($row[$source])) {
                update_post_meta($id, $destination, wp_slash((string) $row[$source]));
            }
        }
        update_post_meta($id, '_spek_partner_review_resolution', 'pending');
        update_post_meta($id, '_spek_partner_google_match_status', 'review');
        $queue[$key]['status'] = 'linked';
        $queue[$key]['partner_id'] = (int) $id;
        $queue[$key]['reviewed_by'] = get_current_user_id();
        update_option(SPEK_PARTNER_REVIEW_QUEUE_OPTION, $queue, false);
        wp_safe_redirect(get_edit_post_link($id, ''));
        exit;
    }

    wp_die(esc_html__('Άγνωστη ενέργεια.', 'spek-theme'), '', ['response' => 400]);
}
add_action('admin_post_spek_partner_review_action', 'spek_partner_review_handle_action');

function spek_partner_review_redirect(string $url, string $message): void
{
    wp_safe_redirect(add_query_arg('spek_review_notice', sanitize_key($message), $url));
    exit;
}


/**
 * Only real, unresolved Google Maps URL suggestions can be approved/rejected
 * in bulk. Orphan Excel rows and missing-coordinate flags are excluded.
 */
function spek_partner_review_bulk_eligible(int $post_id): bool
{
    if ($post_id < 1 || get_post_type($post_id) !== 'spek_partner') {
        return false;
    }
    if (!in_array(get_post_status($post_id), ['publish', 'draft', 'pending', 'private'], true)) {
        return false;
    }
    $proposal = trim((string) get_post_meta($post_id, '_spek_partner_suggested_maps_url', true));
    $decision = (string) get_post_meta($post_id, '_spek_partner_review_resolution', true);
    return $proposal !== ''
        && !in_array($decision, ['approved', 'rejected'], true)
        && spek_partner_review_status($post_id) === 'pending';
}

/**
 * Apply one decision without touching the street/postcode/lat/lng.
 * Revalidate every post and proposed URL at submit time.
 */
function spek_partner_review_bulk_decide(int $post_id, string $decision): bool
{
    if (!in_array($decision, ['approve', 'reject'], true)
        || !current_user_can('edit_post', $post_id)
        || !spek_partner_review_bulk_eligible($post_id)) {
        return false;
    }

    $proposal = (string) get_post_meta($post_id, '_spek_partner_suggested_maps_url', true);
    $url = function_exists('spek_partner_import_normalize_maps_url')
        ? spek_partner_import_normalize_maps_url($proposal)
        : '';
    if ($decision === 'approve' && $url === '') {
        // Never mass-approve an invalid Google Maps link.
        return false;
    }

    if ($decision === 'approve') {
        $old_url = (string) get_post_meta($post_id, 'partner_maps_url', true);
        // Query-based Place IDs are supported; don't carry over a stale ID
        // if the approved URL points elsewhere.
        $query = (string) wp_parse_url($url, PHP_URL_QUERY);
        $params = [];
        parse_str($query, $params);
        $place_id = '';
        foreach (['query_place_id', 'destination_place_id'] as $key) {
            if (!empty($params[$key]) && is_string($params[$key])) {
                $place_id = sanitize_text_field($params[$key]);
                break;
            }
        }
        update_post_meta($post_id, 'partner_maps_url', $url);
        update_post_meta($post_id, '_spek_partner_google_place_id', $place_id);
        update_post_meta($post_id, '_spek_partner_review_accepted_url', $url);
        update_post_meta($post_id, '_spek_partner_google_match_status', 'approved');
        // A changed location URL may require manual pin verification.
        // Never silently overwrite or invent location coordinates.
        if ($old_url !== $url && spek_partner_review_has_coordinates($post_id)) {
            update_post_meta($post_id, '_spek_partner_pin_needs_review', '1');
        }
        update_post_meta($post_id, '_spek_partner_review_resolution', 'approved');
    } else {
        // Reject the suggestion only, not the whole partner or current URL.
        update_post_meta($post_id, '_spek_partner_review_rejected_url', $proposal);
        update_post_meta($post_id, '_spek_partner_review_resolution', 'rejected');
    }

    delete_post_meta($post_id, '_spek_partner_suggested_maps_url');
    update_post_meta($post_id, '_spek_partner_reviewed_by', get_current_user_id());
    update_post_meta($post_id, '_spek_partner_reviewed_at', current_time('mysql'));
    return true;
}

/** Bulk approve/reject only the checked partners on the current page. */
function spek_partner_review_handle_bulk_action(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Δεν επιτρέπεται αυτή η ενέργεια.', 'spek-theme'), '', ['response' => 403]);
    }
    check_admin_referer('spek_partner_bulk_review');
    $back = spek_partner_review_back_url();
    $back = remove_query_arg(
        ['spek_review_notice', 'spek_bulk_approved', 'spek_bulk_rejected', 'spek_bulk_skipped'],
        $back
    );
    $decision = isset($_POST['bulk_decision'])
        ? sanitize_key(wp_unslash((string) $_POST['bulk_decision']))
        : '';
    if (!in_array($decision, ['approve', 'reject'], true)) {
        spek_partner_review_redirect($back, 'bulk_invalid');
    }

    $raw = isset($_POST['partner_ids']) && is_array($_POST['partner_ids'])
        ? wp_unslash($_POST['partner_ids'])
        : [];
    // Up to one page of selections (25) plus a small tolerance. Never
    // accept arbitrary unlimited IDs from forged requests.
    if (count($raw) > 100) {
        spek_partner_review_redirect($back, 'bulk_invalid');
    }
    $ids = array_values(array_unique(array_filter(array_map('absint', $raw))));
    if (!$ids) {
        spek_partner_review_redirect($back, 'bulk_empty');
    }

    $applied = 0;
    foreach ($ids as $id) {
        if (spek_partner_review_bulk_decide((int) $id, $decision)) {
            $applied++;
        }
    }
    $counts = [
        'spek_review_notice' => 'bulk_done',
        'spek_bulk_approved' => $decision === 'approve' ? $applied : 0,
        'spek_bulk_rejected' => $decision === 'reject' ? $applied : 0,
        'spek_bulk_skipped' => count($ids) - $applied,
    ];
    wp_safe_redirect(add_query_arg($counts, $back));
    exit;
}
add_action('admin_post_spek_partner_review_bulk_action', 'spek_partner_review_handle_bulk_action');

/**
 * Render review list with server-side filters and 25 entries per page.
 * At SPEK's scale (~1,500 partners), loading just IDs is inexpensive.
 */
function spek_partner_review_render_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $filter = isset($_GET['status']) ? sanitize_key(wp_unslash((string) $_GET['status'])) : 'pending';
    if (!in_array($filter, ['pending', 'suggestions', 'unmatched', 'missing', 'approved', 'rejected', 'all'], true)) {
        $filter = 'pending';
    }
    $term = isset($_GET['s']) ? sanitize_text_field(wp_unslash((string) $_GET['s'])) : '';
    $page = max(1, absint($_GET['paged'] ?? 1));
    $per_page = 25;
    $ids = get_posts([
        'post_type' => 'spek_partner',
        'post_status' => ['publish', 'draft', 'pending', 'private'],
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    // Prefetch all partner metadata once, instead of an extra SQL query per row.
    if ($ids) {
        update_meta_cache('post', array_map('intval', $ids));
    }
    $groups = ['pending' => [], 'missing' => [], 'approved' => [], 'rejected' => [], 'ok' => []];
    $suggestion_ids = [];
    $unmatched_ids = [];
    foreach ($ids as $id) {
        $id = (int) $id;
        $status = spek_partner_review_status($id);
        $groups[$status][] = $id;
        if (spek_partner_review_bulk_eligible($id)) {
            $suggestion_ids[] = $id;
        } elseif ($status === 'pending') {
            // "No Google Places result" is a manual-review issue,
            // not a Google link that can be mass-approved.
            $unmatched_ids[] = $id;
        }
    }
    $queue = spek_partner_review_queue();
    $queued = [];
    $dismissed = [];
    foreach ($queue as $key => $item) {
        if (($item['status'] ?? '') === 'pending') {
            $queued[$key] = $item;
        } elseif (($item['status'] ?? '') === 'rejected') {
            $dismissed[$key] = $item;
        }
    }

    $items = [];
    // Put actual address suggestions first. Without this ordering a page of
    // no-match partners hides every checkbox even if suggestions exist later.
    if ($filter === 'suggestions') {
        $filtered_ids = $suggestion_ids;
    } elseif ($filter === 'unmatched') {
        $filtered_ids = $unmatched_ids;
    } elseif ($filter === 'pending') {
        $filtered_ids = array_merge(
            $suggestion_ids,
            $unmatched_ids,
            $groups['missing']
        );
    } elseif ($filter === 'all') {
        $filtered_ids = array_merge(
            $suggestion_ids,
            $unmatched_ids,
            $groups['missing'],
            $groups['approved'],
            $groups['rejected'],
            $groups['ok']
        );
    } else {
        $filtered_ids = $groups[$filter];
    }
    foreach ($filtered_ids as $id) {
            $group = spek_partner_review_status((int) $id);
            $name = (string) get_the_title($id);
            $location = implode(' ', [
                get_post_meta($id, 'partner_address', true),
                get_post_meta($id, 'partner_area', true),
                get_post_meta($id, 'partner_postcode', true),
            ]);
            if ($term !== '' && mb_stripos($name . ' ' . $location, $term, 0, 'UTF-8') === false) {
                continue;
            }
            $items[] = ['type' => 'partner', 'id' => $id, 'status' => $group];
    }
    if (in_array($filter, ['all', 'pending', 'rejected'], true)) {
        $queue_rows = $filter === 'rejected' ? $dismissed
            : ($filter === 'all' ? array_merge($queued, $dismissed) : $queued);
        foreach ($queue_rows as $key => $item) {
            $row = (array) ($item['data'] ?? []);
            if ($term !== '' && mb_stripos(implode(' ', $row), $term, 0, 'UTF-8') === false) {
                continue;
            }
            $items[] = ['type' => 'queue', 'key' => $key, 'status' => $item['status']];
        }
    }

    $total = count($items);
    $items = array_slice($items, ($page - 1) * $per_page, $per_page);
    $base = admin_url('edit.php?post_type=spek_partner&page=spek-partner-review');
    $return_url = add_query_arg(['status' => $filter, 's' => $term, 'paged' => $page], $base);
    $notice = isset($_GET['spek_review_notice'])
        ? sanitize_key(wp_unslash((string) $_GET['spek_review_notice'])) : '';
    ?>
    <div class="wrap spek-review">
        <h1><?php esc_html_e('Έλεγχος συνεργατών', 'spek-theme'); ?></h1>
        <p class="description">Όλες οι εκκρεμότητες των συνεργατών και οι γραμμές Excel που δεν εισήχθησαν με ασφάλεια. Κάθε απόφαση καταγράφεται στον συνεργάτη.</p>
        <?php if ($notice !== '') : ?>
            <div class="notice notice-info is-dismissible"><p><?php
                $messages = [
                    'saved' => 'Οι αλλαγές αποθηκεύτηκαν. Η έγκριση παραμένει σε εκκρεμότητα.',
                    'approved' => 'Ο συνεργάτης εγκρίθηκε.',
                    'rejected' => 'Η πρόταση απορρίφθηκε.',
                    'failed' => 'Η ενέργεια δεν ολοκληρώθηκε.',
                    'invalid_coords' => 'Μη έγκυρες συντεταγμένες.',
                    'invalid_url' => 'Το Google Maps URL δεν είναι έγκυρο.',
                    'empty_name' => 'Η επωνυμία είναι υποχρεωτική.',
                    'missing_url' => 'Επιβεβαιώστε ή συμπληρώστε Google Maps URL πριν από την έγκριση.',
                    'missing_queue' => 'Η εγγραφή του Excel δεν είναι πλέον διαθέσιμη.',
                    'bulk_empty' => 'Επίλεξε τουλάχιστον μία εκκρεμή πρόταση Google Maps.',
                    'bulk_invalid' => 'Η μαζική ενέργεια δεν ήταν έγκυρη ή επιλέχθηκαν υπερβολικά πολλές εγγραφές.',
                ];
                if ($notice === 'bulk_done') {
                    $approved_count = absint($_GET['spek_bulk_approved'] ?? 0);
                    $rejected_count = absint($_GET['spek_bulk_rejected'] ?? 0);
                    $skipped_count = absint($_GET['spek_bulk_skipped'] ?? 0);
                    printf(
                        esc_html__('Ολοκληρώθηκε: %1$d εγκρίσεις, %2$d απορρίψεις, %3$d παραλείψεις (χωρίς ενεργή/έγκυρη πρόταση ή δικαίωμα).', 'spek-theme'),
                        $approved_count,
                        $rejected_count,
                        $skipped_count
                    );
                } else {
                    echo esc_html($messages[$notice] ?? 'Η ενέργεια ολοκληρώθηκε.');
                }
            ?></p></div>
        <?php endif; ?>

        <div class="spek-review-stats">
            <div><strong><?php echo esc_html((string) (count($groups['pending']) + count($groups['missing']) + count($queued))); ?></strong><span>Εκκρεμότητες</span></div>
            <div><strong><?php echo esc_html((string) count($groups['missing'])); ?></strong><span>Χωρίς pin</span></div>
            <div><strong><?php echo esc_html((string) count($groups['approved'])); ?></strong><span>Εγκεκριμένοι</span></div>
            <div><strong><?php echo esc_html((string) count($queued)); ?></strong><span>Γραμμές Excel προς έλεγχο</span></div>
        </div>

        <div class="spek-review-tabs">
            <?php
            $tabs = [
                'pending' => 'Εκκρεμότητες',
                'suggestions' => sprintf('Προτάσεις Google (%d)', count($suggestion_ids)),
                'unmatched' => sprintf('Χωρίς αντιστοίχιση (%d)', count($unmatched_ids)),
                'missing' => 'Χωρίς pin',
                'approved' => 'Εγκεκριμένα',
                'rejected' => 'Απορριφθέντα',
                'all' => 'Όλα',
            ];
            foreach ($tabs as $tab => $label) :
                $url = add_query_arg(['status' => $tab, 's' => $term], $base);
                ?>
                <a class="<?php echo $filter === $tab ? 'is-active' : ''; ?>" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </div>

        <form method="get" class="spek-review-search">
            <input type="hidden" name="post_type" value="spek_partner">
            <input type="hidden" name="page" value="spek-partner-review">
            <input type="hidden" name="status" value="<?php echo esc_attr($filter); ?>">
            <input type="search" name="s" value="<?php echo esc_attr($term); ?>" placeholder="Αναζήτηση επωνυμίας, διεύθυνσης, περιοχής ή ΤΚ">
            <?php submit_button('Αναζήτηση', 'secondary', 'submit', false); ?>
        </form>

        <?php
        $bulk_eligible_count = 0;
        foreach ($items as $item) {
            if ($item['type'] === 'partner'
                && spek_partner_review_bulk_eligible((int) $item['id'])
                && current_user_can('edit_post', (int) $item['id'])) {
                $bulk_eligible_count++;
            }
        }
        ?>
        <form id="spek-partner-bulk-form"
                  class="spek-review-bulk"
                  method="post"
                  action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="spek_partner_review_bulk_action">
                <input type="hidden" name="return_url" value="<?php echo esc_url($return_url); ?>">
                <?php wp_nonce_field('spek_partner_bulk_review'); ?>
                <label class="spek-review-bulk__toggle">
                    <input type="checkbox" data-spek-bulk-select-all <?php disabled($bulk_eligible_count === 0); ?>>
                    <span>Επιλογή προτάσεων στην τρέχουσα σελίδα</span>
                </label>
                <span class="spek-review-bulk__count" data-spek-bulk-counter>0 επιλεγμένες</span>
                <button type="submit" name="bulk_decision" value="approve"
                        class="button button-primary" data-spek-bulk-action disabled>
                    Έγκριση επιλεγμένων
                </button>
                <button type="submit" name="bulk_decision" value="reject"
                        class="button" data-spek-bulk-action disabled>
                    Απόρριψη επιλεγμένων
                </button>
                <?php if ($bulk_eligible_count === 0) : ?>
                    <p class="spek-review-bulk__note">
                        Δεν υπάρχουν έτοιμες προτάσεις Google Maps προς μαζική έγκριση σε αυτή τη σελίδα.
                        Οι εγγραφές με «Δεν βρέθηκε Google Places αποτέλεσμα» χρειάζονται πρώτα χειροκίνητη αντιστοίχιση.
                        <?php if (count($suggestion_ids) > 0) : ?>
                            <a href="<?php echo esc_url(add_query_arg(['status' => 'suggestions', 's' => ''], $base)); ?>">
                                Δες τις <?php echo esc_html((string) count($suggestion_ids)); ?> εκκρεμείς προτάσεις Google.
                            </a>
                        <?php endif; ?>
                    </p>
                <?php else : ?>
                    <p class="spek-review-bulk__note">
                        <?php echo esc_html(sprintf('%d προτάσεις διαθέσιμες για επιλογή σε αυτή τη σελίδα.', $bulk_eligible_count)); ?>
                        Η μαζική ενέργεια αλλάζει μόνο το Google Maps URL / Place ID. Οι διευθύνσεις και τα pins δεν τροποποιούνται αυτόματα.
                    </p>
                <?php endif; ?>
            </form>

        <p class="spek-review-count"><?php echo esc_html(sprintf('%d αποτελέσματα', $total)); ?></p>
        <?php if (!$items) : ?>
            <div class="spek-review-empty">Δεν βρέθηκαν εγγραφές για αυτό το φίλτρο.</div>
        <?php endif; ?>

        <div class="spek-review-list">
        <?php foreach ($items as $item) :
            if ($item['type'] === 'queue') :
                $q = $queue[$item['key']];
                $row = (array) ($q['data'] ?? []);
                ?>
                <article class="spek-review-card">
                    <div class="spek-review-card__header">
                        <div><span class="spek-review-pill is-<?php echo ($q['status'] ?? '') === 'rejected' ? 'rejected' : 'pending'; ?>">Excel · <?php echo esc_html($q['type'] ?? 'review'); ?></span>
                            <h2><?php echo esc_html(($row['name'] ?? '') !== '' ? $row['name'] : 'Χωρίς επωνυμία'); ?></h2>
                            <p><?php echo esc_html(implode(', ', array_filter([$row['address'] ?? '', $row['postcode'] ?? '', $row['area'] ?? '']))); ?></p>
                            <p class="spek-review-note"><?php echo esc_html($q['message'] ?? ''); ?> · Γραμμή <?php echo esc_html((string) ($q['source_row'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php if (($q['status'] ?? '') === 'pending') : ?>
                    <div class="spek-review-actions">
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="spek_partner_review_action">
                            <input type="hidden" name="review_action" value="create_draft">
                            <input type="hidden" name="queue_key" value="<?php echo esc_attr($item['key']); ?>">
                            <input type="hidden" name="return_url" value="<?php echo esc_url($return_url); ?>">
                            <?php wp_nonce_field('spek_partner_queue_' . $item['key']); ?>
                            <?php if (empty($row['name'])) : ?>
                                <label>Επωνυμία <input type="text" name="partner_name" placeholder="Συμπλήρωσε επωνυμία" required></label>
                            <?php endif; ?>
                            <button type="submit" class="button button-primary">Δημιουργία προσχεδίου &amp; επεξεργασία</button>
                        </form>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="spek_partner_review_action">
                            <input type="hidden" name="review_action" value="dismiss">
                            <input type="hidden" name="queue_key" value="<?php echo esc_attr($item['key']); ?>">
                            <input type="hidden" name="return_url" value="<?php echo esc_url($return_url); ?>">
                            <?php wp_nonce_field('spek_partner_queue_' . $item['key']); ?>
                            <button type="submit" class="button">Απόρριψη εγγραφής</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </article>
            <?php else :
                $id = (int) $item['id'];
                $suggestion = (string) get_post_meta($id, '_spek_partner_suggested_maps_url', true);
                $current_maps = (string) get_post_meta($id, 'partner_maps_url', true);
                $map_link = $suggestion !== '' ? $suggestion : $current_maps;
                $status = $item['status'];
                $saved_by = (int) get_post_meta($id, '_spek_partner_reviewed_by', true);
                $reviewed = (string) get_post_meta($id, '_spek_partner_reviewed_at', true);
                $google_name = (string) get_post_meta($id, '_spek_partner_google_candidate_name', true);
                $google_address = (string) get_post_meta($id, '_spek_partner_google_candidate_address', true);
                $google_score = (string) get_post_meta($id, '_spek_partner_google_match_score', true);
                $google_note = (string) get_post_meta($id, '_spek_partner_google_match_note', true);
                $pin_needs_review = (string) get_post_meta($id, '_spek_partner_pin_needs_review', true) === '1';
                ?>
                <article class="spek-review-card">
                    <div class="spek-review-card__header">
                        <?php if (spek_partner_review_bulk_eligible($id) && current_user_can('edit_post', $id)) : ?>
                        <label class="spek-review-card__select" title="Επιλογή πρότασης για μαζική ενέργεια">
                            <input type="checkbox"
                                   name="partner_ids[]"
                                   value="<?php echo esc_attr((string) $id); ?>"
                                   form="spek-partner-bulk-form"
                                   data-spek-bulk-item
                                   aria-label="<?php echo esc_attr('Επιλογή ' . get_the_title($id)); ?>">
                        </label>
                        <?php endif; ?>
                        <div class="spek-review-card__main">
                            <span class="spek-review-pill is-<?php echo esc_attr($status); ?>"><?php
                                echo esc_html(
                                    $status === 'pending' && $suggestion === ''
                                        ? 'Χωρίς πρόταση Google'
                                        : spek_partner_review_label($status)
                                );
                            ?></span>
                            <h2><?php echo esc_html(get_the_title($id)); ?></h2>
                            <p><?php echo esc_html(implode(', ', array_filter([
                                get_post_meta($id, 'partner_address', true),
                                get_post_meta($id, 'partner_postcode', true),
                                get_post_meta($id, 'partner_area', true),
                            ]))); ?></p>
                            <?php if ($suggestion !== '') : ?>
                                <p class="spek-review-note">Προτεινόμενη τοποθεσία Google — μπορείς να την εγκρίνεις ή να την απορρίψεις.</p>
                            <?php elseif ($status === 'pending') : ?>
                                <p class="spek-review-note">Δεν υπάρχει υποψήφια αντιστοίχιση Google Maps για μαζική έγκριση. Διόρθωσε ή συμπλήρωσε την τοποθεσία από τη Γρήγορη επεξεργασία.</p>
                            <?php endif; ?>
                            <?php if ($google_name !== '' || $google_address !== '') : ?>
                                <p><strong>Google:</strong> <?php echo esc_html(implode(' · ', array_filter([$google_name, $google_address]))); ?>
                                <?php if ($google_score !== '') : ?> · <?php echo esc_html($google_score); ?>% βεβαιότητα<?php endif; ?></p>
                            <?php endif; ?>
                            <?php if ($google_note !== '' && $status === 'pending') : ?><p class="spek-review-note"><?php echo esc_html($google_note); ?></p><?php endif; ?>
                            <?php if ($pin_needs_review) : ?><p class="spek-review-note">Το Google Maps URL άλλαξε, αλλά το pin διατηρήθηκε. Επιβεβαίωσε τις συντεταγμένες του συνεργάτη.</p><?php endif; ?>
                            <?php if ($status === 'missing') : ?>
                                <?php
                                $stored_lat = (string) get_post_meta($id, 'partner_latitude', true);
                                $stored_lng = (string) get_post_meta($id, 'partner_longitude', true);
                                $suspect_location = $stored_lat !== '' && $stored_lng !== '';
                                ?>
                                <p class="spek-review-note"><?php echo esc_html(
                                    $suspect_location
                                        ? 'Το αποθηκευμένο pin είναι άκυρο ή εκτός Ελλάδας για ελληνικό ΤΚ. Χρειάζεται έλεγχος συντεταγμένων.'
                                        : 'Απουσιάζουν συντεταγμένες. Ο συνεργάτης εμφανίζεται στη λίστα αλλά όχι ως pin στον χάρτη.'
                                ); ?></p>
                            <?php endif; ?>
                            <?php if ($reviewed !== '') : ?><p class="description"><?php echo esc_html($reviewed); ?><?php if ($saved_by) : ?> · <?php echo esc_html(get_the_author_meta('display_name', $saved_by)); ?><?php endif; ?></p><?php endif; ?>
                        </div>
                        <div class="spek-review-card__links">
                            <?php if ($suggestion !== '') : ?>
                                <a href="<?php echo esc_url($suggestion); ?>" target="_blank" rel="noopener noreferrer">Προτεινόμενη Google ↗</a>
                                <?php if ($current_maps !== '') : ?><a href="<?php echo esc_url($current_maps); ?>" target="_blank" rel="noopener noreferrer">Τωρινή τοποθεσία ↗</a><?php endif; ?>
                            <?php elseif ($map_link !== '') : ?>
                                <a href="<?php echo esc_url($map_link); ?>" target="_blank" rel="noopener noreferrer">Άνοιγμα στο Google Maps ↗</a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url(get_edit_post_link($id, '')); ?>">Πλήρης επεξεργασία ↗</a>
                        </div>
                    </div>
                    <details>
                        <summary>Γρήγορη επεξεργασία / Έγκριση</summary>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="spek-review-editor">
                            <input type="hidden" name="action" value="spek_partner_review_action">
                            <input type="hidden" name="partner_id" value="<?php echo esc_attr((string) $id); ?>">
                            <input type="hidden" name="return_url" value="<?php echo esc_url($return_url); ?>">
                            <?php wp_nonce_field('spek_partner_review_' . $id); ?>
                            <?php
                            $fields = [
                                'partner_name' => ['Επωνυμία', get_the_title($id)],
                                'partner_address' => ['Διεύθυνση', get_post_meta($id, 'partner_address', true)],
                                'partner_area' => ['Πόλη / Περιοχή', get_post_meta($id, 'partner_area', true)],
                                'partner_postcode' => ['ΤΚ', get_post_meta($id, 'partner_postcode', true)],
                                'partner_phone' => ['Τηλέφωνο', get_post_meta($id, 'partner_phone', true)],
                                'partner_email' => ['Email', get_post_meta($id, 'partner_email', true)],
                                'partner_maps_url' => ['Google Maps URL', $map_link],
                                'partner_latitude' => ['Latitude', get_post_meta($id, 'partner_latitude', true)],
                                'partner_longitude' => ['Longitude', get_post_meta($id, 'partner_longitude', true)],
                            ];
                            foreach ($fields as $name => $field) : ?>
                                <label><span><?php echo esc_html($field[0]); ?></span>
                                    <input type="<?php echo $name === 'partner_email' ? 'email' : 'text'; ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr((string) $field[1]); ?>">
                                </label>
                            <?php endforeach; ?>
                            <p class="spek-review-note">Το Google Maps URL δεν αλλάζει μόνο του τις συντεταγμένες του pin. Έλεγξε τις Latitude/Longitude πριν την τελική επιβεβαίωση.</p>
                            <div class="spek-review-actions">
                                <button type="submit" name="review_action" value="save" class="button">Αποθήκευση αλλαγών</button>
                                <button type="submit" name="review_action" value="approve" class="button button-primary">Έγκριση</button>
                                <?php if ($suggestion !== '' || $status === 'pending') : ?>
                                <button type="submit" name="review_action" value="reject" class="button" formnovalidate onclick="return confirm('Να απορριφθεί η προτεινόμενη αντιστοίχιση;');">Απόρριψη πρότασης</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </details>
                </article>
            <?php endif; ?>
        <?php endforeach; ?>
        </div>
        <?php
        $pages = (int) ceil($total / $per_page);
        if ($pages > 1) {
            echo '<div class="tablenav"><div class="tablenav-pages">';
            echo wp_kses_post(paginate_links([
                'base' => add_query_arg('paged', '%#%', $return_url),
                'format' => '',
                'current' => $page,
                'total' => $pages,
                'prev_text' => '‹',
                'next_text' => '›',
            ]));
            echo '</div></div>';
        }
        ?>
    </div>
    <?php
}
