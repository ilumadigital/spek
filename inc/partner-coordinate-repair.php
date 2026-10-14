<?php
/**
 * One-time, resumable repair for historical @ map-centre partner coordinates.
 *
 * A full Google Maps place URL exposes POI coordinates as !3dLAT!4dLNG.
 * Older SPEK admin versions preferred the /@LAT,LNG camera centre. Only
 * replace a saved position if it still equals the old camera-centre pair,
 * or if the stored position is completely blank. Preserve all manual edits.
 *
 * @package SpekTheme
 */
if (!defined('ABSPATH')) {
    exit;
}

const SPEK_PARTNER_COORD_REPAIR_VERSION = 'place-marker-2026-10-14-v1';
const SPEK_PARTNER_COORD_REPAIR_OPTION = 'spek_partner_coord_repair_progress';

/** Current progress; versioning prevents accidental repeated overwrites. */
function spek_partner_coord_repair_state(): array
{
    $state = get_option(SPEK_PARTNER_COORD_REPAIR_OPTION, []);
    if (!is_array($state) || ($state['version'] ?? '') !== SPEK_PARTNER_COORD_REPAIR_VERSION) {
        return [
            'version' => SPEK_PARTNER_COORD_REPAIR_VERSION,
            'status' => 'pending',
            'cursor' => 0,
            'total' => 0,
            'fixed' => 0,
            'filled' => 0,
            'already_correct' => 0,
            'protected' => 0,
            'no_place' => 0,
            'invalid' => 0,
            'started_at' => '',
            'finished_at' => '',
        ];
    }
    return $state;
}

/** Read the map viewport coordinates separately, irrespective of parser priority. */
function spek_partner_coord_repair_old_center(string $url): array
{
    $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    for ($i = 0; $i < 3; $i++) {
        $url = rawurldecode($url);
    }
    if (!preg_match('~@(-?[0-9]+(?:\.[0-9]+)?),(-?[0-9]+(?:\.[0-9]+)?)(?=,|/|\?|&|$)~', $url, $m)) {
        return [];
    }
    $lat = (float) $m[1];
    $lng = (float) $m[2];
    if (!is_finite($lat) || !is_finite($lng)
        || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        return [];
    }
    return ['latitude' => $lat, 'longitude' => $lng];
}

function spek_partner_coord_repair_same($lat_a, $lng_a, $lat_b, $lng_b): bool
{
    return is_numeric($lat_a) && is_numeric($lng_a)
        && abs((float) $lat_a - (float) $lat_b) <= 0.000001
        && abs((float) $lng_a - (float) $lng_b) <= 0.000001;
}

/**
 * Inspect + possibly update a single partner.
 * Never geocode or call paid APIs, and never modify other partner fields.
 */
function spek_partner_coord_repair_one(int $post_id): string
{
    $maps_url = (string) get_post_meta($post_id, 'partner_maps_url', true);
    if (!$maps_url || !function_exists('spek_extract_lat_lng_from_maps_url')) {
        return 'no_place';
    }
    if (function_exists('spek_partner_import_normalize_maps_url')
        && spek_partner_import_normalize_maps_url($maps_url) === '') {
        return 'invalid';
    }

    $poi = spek_extract_lat_lng_from_maps_url($maps_url);
    if (($poi['source'] ?? '') !== 'place') {
        // Short Maps links, no-POI URLs and @-only URLs aren't exact pins.
        return 'no_place';
    }
    if (function_exists('spek_partner_import_coords_plausible') &&
        !spek_partner_import_coords_plausible([
            'postcode' => (string) get_post_meta($post_id, 'partner_postcode', true),
        ], $poi)) {
        return 'invalid';
    }

    $old_lat = (string) get_post_meta($post_id, 'partner_latitude', true);
    $old_lng = (string) get_post_meta($post_id, 'partner_longitude', true);
    $center = spek_partner_coord_repair_old_center($maps_url);

    if (spek_partner_coord_repair_same(
        $old_lat, $old_lng, $poi['latitude'], $poi['longitude']
    )) {
        return 'already_correct';
    }
    $both_blank = $old_lat === '' && $old_lng === '';
    $was_old_center = $center && spek_partner_coord_repair_same(
        $old_lat, $old_lng, $center['latitude'], $center['longitude']
    );
    if (!$both_blank && !$was_old_center) {
        // Includes half-entered, manual coordinates, and other geocoders.
        return 'protected';
    }

    // Keep a one-time backup for manual restoration if necessary.
    if (!metadata_exists('post', $post_id, '_spek_partner_coord_repair_backup')) {
        add_post_meta($post_id, '_spek_partner_coord_repair_backup', wp_json_encode([
            'latitude' => $old_lat,
            'longitude' => $old_lng,
            'maps_url' => $maps_url,
            'created_at' => current_time('mysql'),
            'source' => (string) get_post_meta($post_id, '_spek_partner_coords_source', true),
        ]), true);
    }
    update_post_meta($post_id, 'partner_latitude', (string) $poi['latitude']);
    update_post_meta($post_id, 'partner_longitude', (string) $poi['longitude']);
    update_post_meta($post_id, '_spek_partner_coords_source', 'place');
    // Pin is now from the actual POI pair in the saved Maps URL.
    delete_post_meta($post_id, '_spek_partner_pin_needs_review');
    update_post_meta($post_id, '_spek_partner_coord_repaired_at', current_time('mysql'));

    return $both_blank ? 'filled' : 'fixed';
}

/** Fetch IDs in stable order, including existing drafts, excluding trash. */
function spek_partner_coord_repair_ids(): array
{
    return get_posts([
        'post_type' => 'spek_partner',
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
        'no_found_rows' => true,
        'suppress_filters' => true,
    ]);
}

/** Process 50 partners/request, resumably and safely across cron/admin calls. */
function spek_partner_coord_repair_batch(): array
{
    $lock_key = 'spek_partner_coord_repair_lock';
    if (get_transient($lock_key)) {
        return spek_partner_coord_repair_state();
    }
    set_transient($lock_key, '1', 60);

    try {
        $state = spek_partner_coord_repair_state();
        if ($state['status'] === 'complete') {
            return $state;
        }
        $ids = spek_partner_coord_repair_ids();
        $total = count($ids);
        $cursor = min((int) $state['cursor'], $total);
        $batch = array_slice($ids, $cursor, 50);
        if ($state['started_at'] === '') {
            $state['started_at'] = current_time('mysql');
        }

        foreach ($batch as $id) {
            $result = spek_partner_coord_repair_one((int) $id);
            if (!array_key_exists($result, $state)) {
                $result = 'invalid';
            }
            $state[$result] = (int) $state[$result] + 1;
        }
        $state['cursor'] = $cursor + count($batch);
        $state['total'] = $total;
        $state['status'] = $state['cursor'] >= $total ? 'complete' : 'running';
        if ($state['status'] === 'complete') {
            $state['finished_at'] = current_time('mysql');
        }
        update_option(SPEK_PARTNER_COORD_REPAIR_OPTION, $state, false);
    } finally {
        delete_transient($lock_key);
    }

    if ($state['status'] !== 'complete') {
        spek_partner_coord_repair_schedule(5);
    }
    return $state;
}

function spek_partner_coord_repair_schedule(int $delay = 20): void
{
    if (!wp_next_scheduled('spek_partner_coord_repair_event')) {
        wp_schedule_single_event(time() + $delay, 'spek_partner_coord_repair_event');
    }
}

/** Start once after deployment; cron continues in small batches. */
function spek_partner_coord_repair_bootstrap(): void
{
    if (defined('WP_INSTALLING') && WP_INSTALLING) {
        return;
    }
    $state = spek_partner_coord_repair_state();
    if ($state['status'] !== 'complete'
        && (!function_exists('wp_doing_cron') || !wp_doing_cron())) {
        spek_partner_coord_repair_schedule();
    }
}
add_action('init', 'spek_partner_coord_repair_bootstrap', 20);
add_action('spek_partner_coord_repair_event', 'spek_partner_coord_repair_batch');

/**
 * WordPress admin review page can advance batches automatically even if WP-Cron
 * is disabled (for example due to infrequent website traffic).
 */
function spek_partner_coord_repair_ajax(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Δεν υπάρχει δικαίωμα πρόσβασης.'], 403);
    }
    check_ajax_referer('spek_partner_coord_repair', 'nonce');
    $state = spek_partner_coord_repair_batch();
    wp_send_json_success($state);
}
add_action('wp_ajax_spek_partner_coord_repair_batch', 'spek_partner_coord_repair_ajax');
