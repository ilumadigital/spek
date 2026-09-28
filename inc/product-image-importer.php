<?php
/**
 * SPEK bulk product image importer.
 *
 * Upload a folder of product images, run a dry matching pass against SKU/title,
 * then import only safe matches. The best image becomes featured image and the
 * rest are stored in product_gallery.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

const SPEK_IMAGE_IMPORT_TTL = 86400;
const SPEK_IMAGE_IMPORT_MAX_FILES = 5000;
const SPEK_IMAGE_IMPORT_MAX_FILE_BYTES = 31457280; // 30 MB/image.
const SPEK_IMAGE_IMPORT_MANUAL_ALIAS_OPTION = 'spek_image_import_manual_aliases';

function spek_product_image_importer_menu(): void
{
    add_submenu_page(
        'edit.php?post_type=spek_product',
        __('Εισαγωγή εικόνων προϊόντων', 'spek-theme'),
        __('Εισαγωγή εικόνων', 'spek-theme'),
        'manage_options',
        'spek-product-image-importer',
        'spek_render_product_image_importer_page'
    );
}
add_action('admin_menu', 'spek_product_image_importer_menu');

function spek_image_import_guard(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Δεν έχετε δικαίωμα για αυτή την ενέργεια.', 'spek-theme')], 403);
    }
    check_ajax_referer('spek_product_image_import_ajax', 'nonce');
}

function spek_image_import_base_dir(): string
{
    $upload = wp_upload_dir();
    return trailingslashit($upload['basedir']) . 'spek-product-image-import';
}

function spek_image_import_session_dir(string $token): string
{
    $token = preg_replace('/[^a-f0-9]/', '', strtolower($token));
    return trailingslashit(spek_image_import_base_dir()) . $token;
}

function spek_image_import_delete_tree(string $dir): void
{
    if (!is_dir($dir)) { return; }
    $items = scandir($dir);
    if (!is_array($items)) { return; }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') { continue; }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) { spek_image_import_delete_tree($path); }
        else { @unlink($path); }
    }
    @rmdir($dir);
}

function spek_image_import_cleanup_old_sessions(): void
{
    $base = spek_image_import_base_dir();
    if (!is_dir($base)) { return; }
    $now = time();
    foreach ((array) glob(trailingslashit($base) . '*', GLOB_ONLYDIR) as $dir) {
        $mtime = @filemtime($dir);
        if ($mtime && ($now - $mtime) > SPEK_IMAGE_IMPORT_TTL) {
            spek_image_import_delete_tree($dir);
        }
    }
}

function spek_image_import_allowed_extension(string $name): string
{
    $ext = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) ? $ext : '';
}

function spek_ajax_image_import_create_session(): void
{
    spek_image_import_guard();
    spek_image_import_cleanup_old_sessions();

    $base = spek_image_import_base_dir();
    if (!wp_mkdir_p($base)) {
        wp_send_json_error(['message' => __('Δεν ήταν δυνατή η δημιουργία προσωρινού φακέλου.', 'spek-theme')], 500);
    }

    try { $token = bin2hex(random_bytes(16)); }
    catch (Throwable $e) { $token = strtolower(wp_generate_password(32, false, false)); }

    $dir = spek_image_import_session_dir($token);
    if (!wp_mkdir_p(trailingslashit($dir) . 'files')) {
        wp_send_json_error(['message' => __('Δεν ήταν δυνατή η δημιουργία συνεδρίας.', 'spek-theme')], 500);
    }

    file_put_contents(trailingslashit($dir) . 'created.txt', (string) time(), LOCK_EX);
    wp_send_json_success(['token' => $token]);
}
add_action('wp_ajax_spek_image_import_create_session', 'spek_ajax_image_import_create_session');

function spek_ajax_image_import_upload_file(): void
{
    spek_image_import_guard();
    $token = sanitize_text_field(wp_unslash($_POST['token'] ?? ''));
    $dir = spek_image_import_session_dir($token);
    if (!$token || !is_dir($dir)) {
        wp_send_json_error(['message' => __('Η συνεδρία εισαγωγής δεν υπάρχει.', 'spek-theme')], 400);
    }

    if (empty($_FILES['image_file']['tmp_name']) || !is_uploaded_file($_FILES['image_file']['tmp_name'])) {
        wp_send_json_error(['message' => __('Δεν παραλήφθηκε εικόνα.', 'spek-theme')], 400);
    }

    $original_name = sanitize_text_field(wp_unslash((string) ($_POST['original_name'] ?? $_FILES['image_file']['name'] ?? 'image.jpg')));
    $original_name = wp_basename($original_name);

    if (spek_image_import_allowed_extension($original_name) === '') {
        wp_send_json_error(['message' => __('Μη υποστηριζόμενος τύπος εικόνας.', 'spek-theme')], 400);
    }

    $client_normalized = !empty($_POST['client_normalized']);
    $upload_name = sanitize_text_field(wp_unslash((string) ($_POST['upload_name'] ?? $_FILES['image_file']['name'] ?? $original_name)));
    $upload_name = wp_basename($upload_name);
    $ext = spek_image_import_allowed_extension($upload_name);

    if ($ext === '') {
        wp_send_json_error(['message' => __('Μη υποστηριζόμενος τύπος εικόνας.', 'spek-theme')], 400);
    }

    $size = (int) @filesize($_FILES['image_file']['tmp_name']);
    if ($size < 1 || $size > SPEK_IMAGE_IMPORT_MAX_FILE_BYTES) {
        wp_send_json_error(['message' => __('Η εικόνα είναι κενή ή υπερβαίνει τα 30 MB.', 'spek-theme')], 400);
    }

    $info = @getimagesize($_FILES['image_file']['tmp_name']);
    if (!$info || empty($info['mime']) || strpos((string) $info['mime'], 'image/') !== 0) {
        wp_send_json_error(['message' => __('Το αρχείο δεν αναγνωρίστηκε ως εικόνα.', 'spek-theme')], 400);
    }

    if ($client_normalized) {
        $expected = (int) SPEK_PRODUCT_IMAGE_CANVAS;
        $width = (int) ($info[0] ?? 0);
        $height = (int) ($info[1] ?? 0);
        $mime = strtolower((string) ($info['mime'] ?? ''));

        if ($width !== $expected || $height !== $expected || $mime !== 'image/jpeg') {
            wp_send_json_error(
                ['message' => __('Η εικόνα browser normalization δεν είναι έγκυρη 1600×1600 JPEG.', 'spek-theme')],
                400
            );
        }
    }

    $id = wp_generate_password(12, false, false);
    $files_dir = trailingslashit($dir) . 'files';
    $safe_name = sanitize_file_name($upload_name);
    if ($safe_name === '') { $safe_name = 'image.' . $ext; }
    $stored_name = $id . '--' . $safe_name;
    $target = trailingslashit($files_dir) . $stored_name;

    if (!@move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
        wp_send_json_error(['message' => __('Αποτυχία προσωρινής αποθήκευσης εικόνας.', 'spek-theme')], 500);
    }

    $meta = [
        'id' => $id,
        'original_name' => $original_name,
        'upload_name' => $upload_name,
        'stored_name' => $stored_name,
        'size' => $size,
        'sha256' => hash_file('sha256', $target) ?: '',
        'client_normalized' => $client_normalized,
        'normalized_width' => $client_normalized ? (int) ($info[0] ?? 0) : 0,
        'normalized_height' => $client_normalized ? (int) ($info[1] ?? 0) : 0,
    ];
    file_put_contents(trailingslashit($files_dir) . $id . '.json', wp_json_encode($meta, JSON_UNESCAPED_UNICODE), LOCK_EX);
    wp_send_json_success(['file' => $meta]);
}
add_action('wp_ajax_spek_image_import_upload_file', 'spek_ajax_image_import_upload_file');

function spek_image_import_session_files(string $dir): array
{
    $files_dir = trailingslashit($dir) . 'files';
    $out = [];
    foreach ((array) glob(trailingslashit($files_dir) . '*.json') as $sidecar) {
        $meta = json_decode((string) @file_get_contents($sidecar), true);
        if (!is_array($meta) || empty($meta['stored_name']) || empty($meta['original_name'])) { continue; }
        $path = trailingslashit($files_dir) . wp_basename((string) $meta['stored_name']);
        if (!is_file($path)) { continue; }
        $meta['path'] = $path;
        $out[] = $meta;
    }
    usort($out, static fn($a, $b) => strnatcasecmp((string) $a['original_name'], (string) $b['original_name']));
    return $out;
}

function spek_image_import_normalize_text(string $value): string
{
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = remove_accents($value);
    $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
    return trim(is_string($value) ? preg_replace('/\s+/u', ' ', $value) : '');
}

function spek_image_import_filename_alias_key(string $filename): string
{
    return spek_image_import_normalize_text(
        (string) pathinfo($filename, PATHINFO_FILENAME)
    );
}

function spek_image_import_manual_aliases(): array
{
    $stored = get_option(SPEK_IMAGE_IMPORT_MANUAL_ALIAS_OPTION, []);

    if (!is_array($stored)) {
        $stored = [];
    }

    return [
        'filename' => array_filter(
            array_map('absint', (array) ($stored['filename'] ?? []))
        ),
        'numeric' => array_filter(
            array_map('absint', (array) ($stored['numeric'] ?? []))
        ),
    ];
}

function spek_image_import_save_manual_alias(
    string $filename,
    int $product_id,
    array $index
): void {
    if ($product_id < 1 || get_post_type($product_id) !== 'spek_product') {
        return;
    }

    $aliases = spek_image_import_manual_aliases();
    $filename_key = spek_image_import_filename_alias_key($filename);

    if ($filename_key !== '') {
        $aliases['filename'][$filename_key] = $product_id;
    }

    $stem = (string) pathinfo($filename, PATHINFO_FILENAME);
    $upper = function_exists('mb_strtoupper')
        ? mb_strtoupper($stem, 'UTF-8')
        : strtoupper($stem);

    if (preg_match('/^0*([0-9]{1,8})(?=$|[^0-9])/u', $upper, $m)) {
        $numeric_key = (string) ((int) $m[1]);

        // Never teach an alias over a real current numeric SKU.
        if (!isset($index['numeric'][$numeric_key])) {
            $aliases['numeric'][$numeric_key] = $product_id;
        }
    }

    // Keep the option bounded even after years of use.
    if (count($aliases['filename']) > 5000) {
        $aliases['filename'] = array_slice(
            $aliases['filename'],
            -5000,
            null,
            true
        );
    }

    if (count($aliases['numeric']) > 2500) {
        $aliases['numeric'] = array_slice(
            $aliases['numeric'],
            -2500,
            null,
            true
        );
    }

    update_option(
        SPEK_IMAGE_IMPORT_MANUAL_ALIAS_OPTION,
        $aliases,
        false
    );
}

function spek_image_import_product_index(): array
{
    $sku_data = function_exists('spek_import_product_sku_index')
        ? spek_import_product_sku_index()
        : ['index' => []];

    $raw_index = (array) ($sku_data['index'] ?? []);

    if (!$raw_index) {
        $ids = get_posts([
            'post_type' => 'spek_product',
            'post_status' => ['publish', 'draft', 'private', 'pending'],
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'suppress_filters' => true,
        ]);

        foreach ($ids as $id) {
            $product_code = trim((string) get_post_meta((int) $id, 'product_code', true));
            if ($product_code !== '') {
                $raw_index[$product_code][] = (int) $id;
            }
        }
    }

    $sku = [];
    $numeric = [];
    $products = [];
    $aliases = [];

    foreach ($raw_index as $code => $ids) {
        $ids = array_values(array_unique(array_map('intval', (array) $ids)));
        if (!$ids) { continue; }

        $record = [
            'sku' => (string) $code,
            'ids' => $ids,
            'ambiguous' => count($ids) !== 1,
        ];

        if (count($ids) === 1) {
            $id = $ids[0];
            $record['id'] = $id;
            $record['title'] = (string) get_the_title($id);
            $record['title_en'] = (string) get_post_meta($id, '_spek_title_en', true);
            $record['source_el'] = (string) get_post_meta($id, '_spek_source_description_el', true);
            $record['source_en'] = (string) get_post_meta($id, '_spek_source_description_en', true);
            $record['resolver_text'] = spek_image_import_normalize_text(
                $record['title'] . ' '
                . $record['title_en'] . ' '
                . $record['source_el'] . ' '
                . $record['source_en']
            );
            $products[$id] = $record;
        }

        $sku[(string) $code] = $record;

        if (ctype_digit((string) $code)) {
            $numeric[(string) ((int) $code)] = $record;
        }
    }

    // Legacy product codes can exist inside the enriched source descriptions.
    // They are only aliases when that numeric code is not already a current SKU.
    foreach ($products as $id => $record) {
        $source =
            (string) get_post_meta($id, '_spek_source_description_el', true)
            . ' '
            . (string) get_post_meta($id, '_spek_source_description_en', true);

        if (
            $source !== ''
            && preg_match_all('/(?<![0-9])([0-9]{4,5})(?![0-9])/u', $source, $matches)
        ) {
            foreach (array_unique($matches[1]) as $legacy_code) {
                $canonical = (string) ((int) $legacy_code);

                if (isset($numeric[$canonical])) {
                    continue;
                }

                if (!isset($aliases[$canonical])) {
                    $aliases[$canonical] = [];
                }

                $aliases[$canonical][(int) $id] = $record;
            }
        }
    }

    // Known aliases from the SPEK 2026 migration. Current SKU matches always win.
    $fallback_alias_skus = apply_filters('spek_image_import_legacy_sku_aliases', [
        '62123' => '31000',
        '1020'  => '10084',
        '61220' => '31002',
        '14330' => '26201',
        '14331' => '26200',
        '14332' => '26203',
        '14333' => '26207',
        '2003'  => '27119',
    ]);

    foreach ((array) $fallback_alias_skus as $legacy => $current_sku) {
        $legacy = (string) ((int) $legacy);
        $current_sku = function_exists('spek_import_normalize_sku')
            ? spek_import_normalize_sku($current_sku)
            : (string) $current_sku;

        if (
            isset($numeric[$legacy])
            || !isset($sku[$current_sku])
            || !empty($sku[$current_sku]['ambiguous'])
        ) {
            continue;
        }

        $record = $sku[$current_sku];
        $aliases[$legacy][(int) $record['id']] = $record;
    }

    $manual = spek_image_import_manual_aliases();
    $manual_filename = [];
    $manual_numeric = [];

    foreach ((array) $manual['filename'] as $key => $product_id) {
        $product_id = (int) $product_id;

        if (isset($products[$product_id])) {
            $manual_filename[(string) $key] = $products[$product_id];
        }
    }

    foreach ((array) $manual['numeric'] as $key => $product_id) {
        $product_id = (int) $product_id;
        $canonical = (string) ((int) $key);

        if (
            $canonical !== ''
            && !isset($numeric[$canonical])
            && isset($products[$product_id])
        ) {
            $manual_numeric[$canonical] = $products[$product_id];
        }
    }

    return [
        'sku' => $sku,
        'numeric' => $numeric,
        'aliases' => $aliases,
        'manual_filename' => $manual_filename,
        'manual_numeric' => $manual_numeric,
        'products' => $products,
    ];
}

function spek_image_import_code_match(string $filename, array $index): array
{
    $stem = (string) pathinfo($filename, PATHINFO_FILENAME);
    $upper = function_exists('mb_strtoupper') ? mb_strtoupper($stem, 'UTF-8') : strtoupper($stem);

    if (preg_match('/^0*([0-9]{1,8})(?=$|[^0-9])/u', $upper, $m)) {
        $canonical = (string) ((int) $m[1]);

        if (isset($index['numeric'][$canonical])) {
            $r = $index['numeric'][$canonical];
            return $r['ambiguous']
                ? ['status' => 'ambiguous', 'reason' => 'duplicate_sku']
                : ['status' => 'matched', 'method' => 'code', 'confidence' => 100, 'record' => $r, 'reason' => 'leading_numeric_code'];
        }

        if (!empty($index['manual_numeric'][$canonical])) {
            return [
                'status' => 'matched',
                'method' => 'manual_alias',
                'confidence' => 99,
                'record' => $index['manual_numeric'][$canonical],
                'reason' => 'learned_numeric_alias',
            ];
        }

        if (!empty($index['aliases'][$canonical])) {
            $records = array_values($index['aliases'][$canonical]);

            if (count($records) === 1) {
                return [
                    'status' => 'matched',
                    'method' => 'code',
                    'confidence' => 96,
                    'record' => $records[0],
                    'reason' => 'legacy_numeric_code',
                ];
            }

            return [
                'status' => 'ambiguous',
                'reason' => 'legacy_code_candidates',
            ];
        }
    }

    foreach ($index['sku'] as $code => $r) {
        $quoted = preg_quote((string) $code, '/');
        if (preg_match('/^' . $quoted . '(?=$|[^0-9A-Z])/iu', $upper)) {
            return $r['ambiguous']
                ? ['status' => 'ambiguous', 'reason' => 'duplicate_sku']
                : ['status' => 'matched', 'method' => 'code', 'confidence' => 100, 'record' => $r, 'reason' => 'leading_sku'];
        }
    }

    foreach ($index['sku'] as $code => $r) {
        $code = (string) $code;
        if (ctype_digit($code) && strlen($code) < 4) { continue; }

        $quoted = preg_quote($code, '/');
        if (preg_match('/(?<![0-9A-Z])' . $quoted . '(?![0-9A-Z])/iu', $upper)) {
            return $r['ambiguous']
                ? ['status' => 'ambiguous', 'reason' => 'duplicate_sku']
                : ['status' => 'matched', 'method' => 'code', 'confidence' => 98, 'record' => $r, 'reason' => 'contained_sku'];
        }
    }

    return ['status' => 'none'];
}

function spek_image_import_filename_alias_match(
    string $filename,
    array $index
): array {
    $key = spek_image_import_filename_alias_key($filename);

    if (
        $key !== ''
        && !empty($index['manual_filename'][$key])
    ) {
        return [
            'status' => 'matched',
            'method' => 'manual_alias',
            'confidence' => 100,
            'record' => $index['manual_filename'][$key],
            'reason' => 'learned_filename_alias',
        ];
    }

    return ['status' => 'none'];
}

function spek_image_import_stopwords(): array
{
    return array_fill_keys([
        'technical','sheet','image','img','drawing','page','new','correct','logo','product','spek','professional',
        'side','entry','fill','valve','soft','close','with','from','for','toilet','seat','hinge','cover','set',
        'νεα','νεο','νεος','φωτο','φωτογραφια','κωδικος','σωστο','τυπου','σετ','καλυμμα','λεκανης',
        'μεντεσες','μεντεσεδες','λαστιχο','λαστιχα','γενικης','χρησης','σελιδα','σχεδιο','παλιο','παλια',
    ], true);
}

function spek_image_import_title_token_index(array $index): array
{
    $stop = spek_image_import_stopwords();
    $tokens = [];

    foreach ($index['products'] as $id => $record) {
        $text = spek_image_import_normalize_text(
            (string) ($record['title'] ?? '') . ' ' . (string) ($record['title_en'] ?? '')
        );

        foreach (array_unique(explode(' ', $text)) as $token) {
            $len = function_exists('mb_strlen') ? mb_strlen($token, 'UTF-8') : strlen($token);
            if ($token === '' || isset($stop[$token]) || $len < 5 || ctype_digit($token)) {
                continue;
            }
            $tokens[$token][(int) $id] = true;
        }
    }

    return $tokens;
}

function spek_image_import_title_match(string $filename, array $index, array $token_index): array
{
    $stop = spek_image_import_stopwords();
    $stem = spek_image_import_normalize_text((string) pathinfo($filename, PATHINFO_FILENAME));
    $file_tokens = [];

    foreach (array_unique(explode(' ', $stem)) as $token) {
        $len = function_exists('mb_strlen') ? mb_strlen($token, 'UTF-8') : strlen($token);
        if ($token === '' || isset($stop[$token]) || $len < 5 || ctype_digit($token)) {
            continue;
        }
        $file_tokens[] = $token;
    }

    if (!$file_tokens) { return ['status' => 'none']; }

    $scores = [];
    $unique_hits = [];

    foreach ($file_tokens as $token) {
        $ids = array_keys((array) ($token_index[$token] ?? []));
        foreach ($ids as $id) {
            $scores[(int) $id] = ($scores[(int) $id] ?? 0) + 1;
        }

        if (count($ids) === 1) {
            $unique_hits[(int) $ids[0]] = true;
        }
    }

    if (!$scores) { return ['status' => 'none']; }

    if (count($unique_hits) === 1) {
        $id = (int) array_key_first($unique_hits);

        if (isset($index['products'][$id])) {
            return [
                'status' => 'matched',
                'method' => 'title',
                'confidence' => 90,
                'record' => $index['products'][$id],
                'reason' => 'unique_title_token',
            ];
        }
    }

    arsort($scores, SORT_NUMERIC);
    $suggestions = [];

    foreach (array_slice(array_keys($scores), 0, 5) as $id) {
        if (!isset($index['products'][$id])) { continue; }

        $r = $index['products'][$id];
        $suggestions[] = [
            'product_id' => (int) $id,
            'sku' => (string) $r['sku'],
            'title' => (string) $r['title'],
            'score' => (int) $scores[$id],
        ];
    }

    return [
        'status' => 'ambiguous',
        'reason' => 'title_candidates',
        'suggestions' => $suggestions,
    ];
}

function spek_image_import_resolver_suggestions(
    string $filename,
    array $index,
    int $limit = 5
): array {
    $stem = spek_image_import_normalize_text(
        (string) pathinfo($filename, PATHINFO_FILENAME)
    );

    if ($stem === '') {
        return [];
    }

    $stop = spek_image_import_stopwords();
    $tokens = [];

    foreach (array_unique(explode(' ', $stem)) as $token) {
        $len = function_exists('mb_strlen')
            ? mb_strlen($token, 'UTF-8')
            : strlen($token);

        if (
            $token === ''
            || isset($stop[$token])
            || $len < 3
        ) {
            continue;
        }

        $tokens[] = $token;
    }

    if (!$tokens) {
        return [];
    }

    $scored = [];

    foreach ((array) $index['products'] as $id => $record) {
        $text = (string) ($record['resolver_text'] ?? '');
        $title = spek_image_import_normalize_text(
            (string) ($record['title'] ?? '')
            . ' '
            . (string) ($record['title_en'] ?? '')
        );

        if ($text === '') {
            continue;
        }

        $score = 0;
        $matched_tokens = 0;

        foreach ($tokens as $token) {
            $quoted = preg_quote($token, '/');
            $in_title = preg_match('/(?:^|\s)' . $quoted . '(?:\s|$)/u', $title);
            $in_text = preg_match('/(?:^|\s)' . $quoted . '(?:\s|$)/u', $text);

            if ($in_title) {
                $score += ctype_digit($token) ? 10 : 6;
                $matched_tokens++;
                continue;
            }

            if ($in_text) {
                $score += ctype_digit($token) ? 8 : 4;
                $matched_tokens++;
                continue;
            }

            $len = function_exists('mb_strlen')
                ? mb_strlen($token, 'UTF-8')
                : strlen($token);

            if ($len >= 5 && strpos($text, $token) !== false) {
                $score += 2;
                $matched_tokens++;
            }
        }

        if ($score < 2 || $matched_tokens < 1) {
            continue;
        }

        $scored[(int) $id] = [
            'product_id' => (int) $id,
            'sku' => (string) ($record['sku'] ?? ''),
            'title' => (string) ($record['title'] ?? ''),
            'score' => $score,
            'matched_tokens' => $matched_tokens,
        ];
    }

    uasort($scored, static function ($a, $b) {
        if ((int) $a['score'] === (int) $b['score']) {
            return strnatcasecmp(
                (string) $a['title'],
                (string) $b['title']
            );
        }

        return (int) $b['score'] <=> (int) $a['score'];
    });

    return array_slice(array_values($scored), 0, max(1, $limit));
}

function spek_image_import_recalculate_analysis(array &$entries): array
{
    $summary = [
        'total' => count($entries),
        'matched_code' => 0,
        'matched_title' => 0,
        'matched_manual' => 0,
        'ambiguous' => 0,
        'unmatched' => 0,
        'products' => [],
    ];

    $by_product = [];

    foreach ($entries as $i => &$entry) {
        unset($entry['featured_candidate'], $entry['first_for_product']);

        $status = (string) ($entry['status'] ?? 'unmatched');

        if ($status === 'matched') {
            $method = (string) ($entry['method'] ?? '');

            if ($method === 'title') {
                $summary['matched_title']++;
            } elseif (in_array($method, ['manual', 'manual_alias'], true)) {
                $summary['matched_manual']++;
            } else {
                $summary['matched_code']++;
            }

            $product_id = (int) ($entry['product_id'] ?? 0);

            if ($product_id > 0) {
                $summary['products'][$product_id] = true;
                $entry['priority'] = spek_image_import_feature_priority($entry);
                $by_product[$product_id][] = $i;
            }
        } elseif ($status === 'ambiguous') {
            $summary['ambiguous']++;
        } else {
            $entry['status'] = 'unmatched';
            $summary['unmatched']++;
        }
    }
    unset($entry);

    foreach ($by_product as $indexes) {
        usort($indexes, static function ($a, $b) use ($entries) {
            $pa = (int) ($entries[$a]['priority'] ?? 0);
            $pb = (int) ($entries[$b]['priority'] ?? 0);

            if ($pa === $pb) {
                return strnatcasecmp(
                    (string) $entries[$a]['original_name'],
                    (string) $entries[$b]['original_name']
                );
            }

            return $pb <=> $pa;
        });

        foreach ($indexes as $pos => $idx) {
            $entries[$idx]['first_for_product'] = ($pos === 0);
        }

        foreach ($indexes as $idx) {
            if (($entries[$idx]['image_role'] ?? 'auto') === 'gallery') {
                continue;
            }

            $entries[$idx]['featured_candidate'] = true;
            break;
        }
    }

    $summary['products'] = count($summary['products']);

    return $summary;
}

function spek_image_import_preview_entries(array $entries): array
{
    return array_map(static fn($e) => [
        'file_id' => (string) ($e['file_id'] ?? ''),
        'file' => (string) ($e['original_name'] ?? ''),
        'status' => (string) ($e['status'] ?? 'unmatched'),
        'reason' => (string) ($e['reason'] ?? ''),
        'method' => (string) ($e['method'] ?? ''),
        'product_id' => (int) ($e['product_id'] ?? 0),
        'sku' => (string) ($e['sku'] ?? ''),
        'product' => (string) ($e['product_title'] ?? ''),
        'confidence' => (int) ($e['confidence'] ?? 0),
        'image_role' => (string) ($e['image_role'] ?? 'auto'),
        'suggestions' => (array) ($e['suggestions'] ?? []),
    ], $entries);
}

function spek_image_import_write_analysis(string $dir, array $entries): array
{
    $summary = spek_image_import_recalculate_analysis($entries);

    file_put_contents(
        trailingslashit($dir) . 'analysis.json',
        wp_json_encode(
            ['entries' => $entries, 'summary' => $summary],
            JSON_UNESCAPED_UNICODE
        ),
        LOCK_EX
    );

    $learned = spek_image_import_manual_aliases();

    return [
        'summary' => $summary,
        'entries' => spek_image_import_preview_entries($entries),
        'learned_alias_count' => count((array) $learned['filename']),
    ];
}

function spek_image_import_feature_priority(array $entry): int
{
    $role = (string) ($entry['image_role'] ?? 'auto');
    $reason = (string) ($entry['reason'] ?? '');
    $method = (string) ($entry['method'] ?? '');

    if ($role === 'featured') { return 1000; }
    if ($role === 'gallery') { return -100; }
    if ($method === 'manual') { return 500; }
    if ($reason === 'leading_numeric_code' || $reason === 'leading_sku') { return 400; }
    if ($method === 'manual_alias') { return 380; }
    if ($reason === 'legacy_numeric_code') { return 350; }
    if ($reason === 'contained_sku') { return 300; }
    if ($method === 'title') { return 200; }

    return 100;
}

function spek_ajax_image_import_analyze(): void
{
    spek_image_import_guard();

    $token = sanitize_text_field(wp_unslash($_POST['token'] ?? ''));
    $dir = spek_image_import_session_dir($token);

    if (!$token || !is_dir($dir)) {
        wp_send_json_error(['message' => __('Η συνεδρία εισαγωγής δεν υπάρχει.', 'spek-theme')], 400);
    }

    $files = spek_image_import_session_files($dir);

    if (!$files) {
        wp_send_json_error(['message' => __('Δεν βρέθηκαν εικόνες.', 'spek-theme')], 400);
    }

    if (count($files) > SPEK_IMAGE_IMPORT_MAX_FILES) {
        wp_send_json_error(['message' => __('Υπερβολικά πολλές εικόνες στη συνεδρία.', 'spek-theme')], 400);
    }

    $index = spek_image_import_product_index();
    $title_index = spek_image_import_title_token_index($index);
    $entries = [];
    foreach ($files as $file) {
        $name = (string) $file['original_name'];

        $match = spek_image_import_code_match($name, $index);

        if (($match['status'] ?? '') === 'none') {
            $match = spek_image_import_filename_alias_match($name, $index);
        }

        if (($match['status'] ?? '') === 'none') {
            $match = spek_image_import_title_match($name, $index, $title_index);
        }

        $entry = [
            'file_id' => (string) $file['id'],
            'original_name' => $name,
            'stored_name' => (string) $file['stored_name'],
            'sha256' => (string) ($file['sha256'] ?? ''),
            'client_normalized' => !empty($file['client_normalized']),
            'normalized_width' => (int) ($file['normalized_width'] ?? 0),
            'normalized_height' => (int) ($file['normalized_height'] ?? 0),
            'status' => (string) ($match['status'] ?? 'none'),
            'reason' => (string) ($match['reason'] ?? ''),
        ];

        if ($entry['status'] === 'matched' && !empty($match['record'])) {
            $r = $match['record'];
            $entry['method'] = (string) ($match['method'] ?? '');
            $entry['confidence'] = (int) ($match['confidence'] ?? 0);
            $entry['product_id'] = (int) $r['id'];
            $entry['sku'] = (string) $r['sku'];
            $entry['product_title'] = (string) $r['title'];
            $entry['priority'] = spek_image_import_feature_priority($entry);
        } elseif ($entry['status'] === 'ambiguous') {
            $entry['suggestions'] = (array) ($match['suggestions'] ?? []);
        } else {
            $entry['status'] = 'unmatched';
        }

        if (
            in_array($entry['status'], ['ambiguous', 'unmatched'], true)
            && empty($entry['suggestions'])
        ) {
            $entry['suggestions'] = spek_image_import_resolver_suggestions(
                $name,
                $index
            );
        }

        $entries[] = $entry;
    }

    $payload = spek_image_import_write_analysis($dir, $entries);

    wp_send_json_success($payload);
}
add_action('wp_ajax_spek_image_import_analyze', 'spek_ajax_image_import_analyze');

function spek_ajax_image_import_preview(): void
{
    spek_image_import_guard();

    $token = sanitize_text_field(wp_unslash($_REQUEST['token'] ?? ''));
    $file_id = sanitize_text_field(wp_unslash($_REQUEST['file_id'] ?? ''));
    $dir = spek_image_import_session_dir($token);

    if (!$token || !$file_id || !is_dir($dir)) {
        status_header(404);
        exit;
    }

    foreach (spek_image_import_session_files($dir) as $file) {
        if ((string) ($file['id'] ?? '') !== $file_id) {
            continue;
        }

        $path = (string) ($file['path'] ?? '');

        if (!is_file($path)) {
            break;
        }

        $info = @getimagesize($path);
        $mime = (string) ($info['mime'] ?? 'image/jpeg');

        nocache_headers();
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        readfile($path);
        exit;
    }

    status_header(404);
    exit;
}
add_action('wp_ajax_spek_image_import_preview', 'spek_ajax_image_import_preview');

function spek_ajax_image_import_product_search(): void
{
    spek_image_import_guard();

    $query = sanitize_text_field(wp_unslash($_POST['q'] ?? ''));
    $query_normalized = spek_image_import_normalize_text($query);

    if ($query_normalized === '') {
        wp_send_json_success(['items' => []]);
    }

    $index = spek_image_import_product_index();
    $items = [];

    foreach ((array) $index['products'] as $id => $record) {
        $sku = (string) ($record['sku'] ?? '');
        $title = (string) ($record['title'] ?? '');
        $title_en = (string) ($record['title_en'] ?? '');
        $haystack = spek_image_import_normalize_text(
            $sku . ' ' . $title . ' ' . $title_en
        );

        $score = 0;

        if ($sku === $query) {
            $score = 100;
        } elseif (
            $query !== ''
            && stripos($sku, $query) === 0
        ) {
            $score = 92;
        } elseif (strpos($haystack, $query_normalized) !== false) {
            $score = 75;
        }

        if ($score < 1) {
            continue;
        }

        $items[] = [
            'product_id' => (int) $id,
            'sku' => $sku,
            'title' => $title,
            'score' => $score,
            'has_image' => has_post_thumbnail((int) $id),
        ];
    }

    usort($items, static function ($a, $b) {
        if ((int) $a['score'] === (int) $b['score']) {
            return strnatcasecmp((string) $a['title'], (string) $b['title']);
        }

        return (int) $b['score'] <=> (int) $a['score'];
    });

    wp_send_json_success([
        'items' => array_slice($items, 0, 12),
    ]);
}
add_action('wp_ajax_spek_image_import_product_search', 'spek_ajax_image_import_product_search');

function spek_ajax_image_import_resolve(): void
{
    spek_image_import_guard();

    $token = sanitize_text_field(wp_unslash($_POST['token'] ?? ''));
    $file_id = sanitize_text_field(wp_unslash($_POST['file_id'] ?? ''));
    $product_id = absint($_POST['product_id'] ?? 0);
    $image_role = sanitize_key(wp_unslash($_POST['image_role'] ?? 'auto'));
    $remember = !empty($_POST['remember']);

    if (!in_array($image_role, ['auto', 'featured', 'gallery'], true)) {
        $image_role = 'auto';
    }

    $dir = spek_image_import_session_dir($token);
    $analysis_path = trailingslashit($dir) . 'analysis.json';

    if (
        !$token
        || !$file_id
        || !is_file($analysis_path)
        || get_post_type($product_id) !== 'spek_product'
    ) {
        wp_send_json_error(
            ['message' => __('Μη έγκυρη αντιστοίχιση.', 'spek-theme')],
            400
        );
    }

    $analysis = json_decode(
        (string) file_get_contents($analysis_path),
        true
    );

    $entries = (array) ($analysis['entries'] ?? []);
    $found = false;
    $index = spek_image_import_product_index();
    $record = (array) ($index['products'][$product_id] ?? []);

    if (!$record) {
        wp_send_json_error(
            ['message' => __('Το προϊόν δεν βρέθηκε.', 'spek-theme')],
            404
        );
    }

    foreach ($entries as &$entry) {
        if ((string) ($entry['file_id'] ?? '') !== $file_id) {
            continue;
        }

        $entry['status'] = 'matched';
        $entry['method'] = 'manual';
        $entry['confidence'] = 100;
        $entry['product_id'] = $product_id;
        $entry['sku'] = (string) ($record['sku'] ?? '');
        $entry['product_title'] = (string) ($record['title'] ?? '');
        $entry['reason'] = 'manual_review';
        $entry['image_role'] = $image_role;
        $entry['suggestions'] = [];
        $entry['priority'] = spek_image_import_feature_priority($entry);
        $found = true;

        if ($remember) {
            spek_image_import_save_manual_alias(
                (string) ($entry['original_name'] ?? ''),
                $product_id,
                $index
            );
        }

        break;
    }
    unset($entry);

    if (!$found) {
        wp_send_json_error(
            ['message' => __('Η εικόνα δεν βρέθηκε στο dry run.', 'spek-theme')],
            404
        );
    }

    if ($remember) {
        $refreshed_index = spek_image_import_product_index();

        foreach ($entries as &$candidate) {
            if (($candidate['status'] ?? '') === 'matched') {
                continue;
            }

            $candidate_name = (string) ($candidate['original_name'] ?? '');
            $learned_match = spek_image_import_code_match(
                $candidate_name,
                $refreshed_index
            );

            if (($learned_match['status'] ?? '') === 'none') {
                $learned_match = spek_image_import_filename_alias_match(
                    $candidate_name,
                    $refreshed_index
                );
            }

            if (
                ($learned_match['status'] ?? '') !== 'matched'
                || empty($learned_match['record'])
            ) {
                continue;
            }

            $learned_record = (array) $learned_match['record'];
            $candidate['status'] = 'matched';
            $candidate['method'] = (string) ($learned_match['method'] ?? 'manual_alias');
            $candidate['confidence'] = (int) ($learned_match['confidence'] ?? 99);
            $candidate['product_id'] = (int) ($learned_record['id'] ?? 0);
            $candidate['sku'] = (string) ($learned_record['sku'] ?? '');
            $candidate['product_title'] = (string) ($learned_record['title'] ?? '');
            $candidate['reason'] = (string) ($learned_match['reason'] ?? 'learned_filename_alias');
            $candidate['image_role'] = 'auto';
            $candidate['suggestions'] = [];
            $candidate['priority'] = spek_image_import_feature_priority($candidate);
        }
        unset($candidate);
    }

    wp_send_json_success(
        spek_image_import_write_analysis($dir, $entries)
    );
}
add_action('wp_ajax_spek_image_import_resolve', 'spek_ajax_image_import_resolve');

function spek_ajax_image_import_clear_aliases(): void
{
    spek_image_import_guard();
    delete_option(SPEK_IMAGE_IMPORT_MANUAL_ALIAS_OPTION);
    wp_send_json_success();
}
add_action('wp_ajax_spek_image_import_clear_aliases', 'spek_ajax_image_import_clear_aliases');

function spek_image_import_find_existing_attachment(string $sha256): int
{
    if ($sha256 === '') { return 0; }

    $ids = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'suppress_filters' => true,
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => '_spek_source_sha256',
                'value' => $sha256,
                'compare' => '=',
            ],
            [
                'key' => '_spek_product_image_normalized_version',
                'value' => (string) SPEK_PRODUCT_IMAGE_NORMALIZATION_VERSION,
                'compare' => '=',
            ],
        ],
    ]);

    return $ids ? (int) $ids[0] : 0;
}

function spek_image_import_gallery_ids(int $product_id): array
{
    $gallery = get_post_meta($product_id, 'product_gallery', true);

    if (!is_array($gallery)) {
        $gallery = is_string($gallery) && $gallery !== ''
            ? preg_split('/[\s,]+/', $gallery)
            : [];
    }

    return array_values(
        array_unique(
            array_filter(
                array_map('absint', (array) $gallery)
            )
        )
    );
}

function spek_image_import_media_file(array $entry, string $dir)
{
    $existing = spek_image_import_find_existing_attachment((string) ($entry['sha256'] ?? ''));

    if ($existing) {
        return $existing;
    }

    $path = trailingslashit($dir) . 'files/' . wp_basename((string) $entry['stored_name']);

    if (!is_file($path)) {
        return new WP_Error(
            'missing_temp_image',
            __('Λείπει προσωρινό αρχείο εικόνας.', 'spek-theme')
        );
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    if (!empty($entry['client_normalized'])) {
        $normalized = [
            'path' => $path,
            'name' => spek_product_image_normalized_filename(
                (string) $entry['original_name']
            ),
            'width' => (int) ($entry['normalized_width'] ?: SPEK_PRODUCT_IMAGE_CANVAS),
            'height' => (int) ($entry['normalized_height'] ?: SPEK_PRODUCT_IMAGE_CANVAS),
        ];
    } else {
        // Backward-compatible fallback for sessions created before browser-side
        // normalization was introduced.
        $normalized = spek_product_image_normalize_file(
            $path,
            (string) $entry['original_name']
        );

        if (is_wp_error($normalized)) {
            return $normalized;
        }
    }

    $attachment_id = spek_product_image_insert_normalized_attachment(
        $normalized,
        (int) $entry['product_id'],
        (string) $entry['product_title'],
        (string) $entry['product_title'] . ' – SPEK'
    );

    if (is_wp_error($attachment_id)) {
        return $attachment_id;
    }

    update_post_meta(
        (int) $attachment_id,
        '_spek_source_sha256',
        sanitize_text_field((string) ($entry['sha256'] ?? ''))
    );
    update_post_meta(
        (int) $attachment_id,
        '_spek_original_filename',
        sanitize_text_field((string) $entry['original_name'])
    );
    update_post_meta(
        (int) $attachment_id,
        '_spek_image_import_sku',
        sanitize_text_field((string) $entry['sku'])
    );
    spek_product_image_mark_normalized_attachment((int) $attachment_id);

    return (int) $attachment_id;
}

function spek_ajax_image_import_commit_batch(): void
{
    spek_image_import_guard();

    // Image normalization + WordPress thumbnail generation can be expensive on
    // shared hosting. Process one image per AJAX request and raise the image
    // memory/time budget so the response always remains a valid JSON payload.
    if (function_exists('wp_raise_memory_limit')) {
        wp_raise_memory_limit('image');
    }
    if (function_exists('set_time_limit')) {
        @set_time_limit(120);
    }

    $token = sanitize_text_field(wp_unslash($_POST['token'] ?? ''));
    $offset = absint($_POST['offset'] ?? 0);
    $replace_featured = !empty($_POST['replace_featured']);
    $replace_gallery = !empty($_POST['replace_gallery']);

    $dir = spek_image_import_session_dir($token);
    $analysis_path = trailingslashit($dir) . 'analysis.json';

    if (!$token || !is_file($analysis_path)) {
        wp_send_json_error(
            ['message' => __('Δεν υπάρχει ολοκληρωμένο dry run.', 'spek-theme')],
            400
        );
    }

    $analysis = json_decode((string) file_get_contents($analysis_path), true);

    $matched = array_values(
        array_filter(
            (array) ($analysis['entries'] ?? []),
            static fn($e) => ($e['status'] ?? '') === 'matched'
        )
    );

    $batch = array_slice($matched, $offset, 1);
    $stats = [
        'imported' => 0,
        'reused' => 0,
        'errors' => [],
    ];

    foreach ($batch as $entry) {
        $product_id = (int) ($entry['product_id'] ?? 0);

        if ($product_id < 1 || get_post_type($product_id) !== 'spek_product') {
            $stats['errors'][] =
                (string) ($entry['original_name'] ?? 'image') . ': invalid product';
            continue;
        }

        if (!empty($entry['first_for_product']) && $replace_gallery) {
            update_post_meta($product_id, 'product_gallery', []);
        }

        $existing = spek_image_import_find_existing_attachment(
            (string) ($entry['sha256'] ?? '')
        );

        $attachment_id = spek_image_import_media_file($entry, $dir);

        if (is_wp_error($attachment_id)) {
            $stats['errors'][] =
                (string) $entry['original_name'] . ': ' . $attachment_id->get_error_message();
            continue;
        }

        if ($existing) {
            $stats['reused']++;
        } else {
            $stats['imported']++;
        }

        $explicit_featured = (($entry['image_role'] ?? 'auto') === 'featured');

        $featured =
            (($entry['image_role'] ?? 'auto') !== 'gallery')
            && !empty($entry['featured_candidate'])
            && (
                $explicit_featured
                || $replace_featured
                || !has_post_thumbnail($product_id)
            );

        if ($featured) {
            set_post_thumbnail($product_id, $attachment_id);
        } else {
            $gallery = spek_image_import_gallery_ids($product_id);
            $featured_id = (int) get_post_thumbnail_id($product_id);

            if (
                $attachment_id !== $featured_id
                && !in_array($attachment_id, $gallery, true)
            ) {
                $gallery[] = $attachment_id;

                update_post_meta(
                    $product_id,
                    'product_gallery',
                    array_values($gallery)
                );
            }
        }
    }

    $next = $offset + count($batch);
    $done = $next >= count($matched);

    if ($done) {
        $files_dir = trailingslashit($dir) . 'files';

        if (is_dir($files_dir)) {
            spek_image_import_delete_tree($files_dir);
        }

        file_put_contents(
            trailingslashit($dir) . 'completed.txt',
            (string) time(),
            LOCK_EX
        );
    }

    wp_send_json_success([
        'offset' => $next,
        'total' => count($matched),
        'done' => $done,
        'stats' => $stats,
    ]);
}
add_action('wp_ajax_spek_image_import_commit_batch', 'spek_ajax_image_import_commit_batch');

function spek_ajax_image_import_cleanup(): void
{
    spek_image_import_guard();

    $token = sanitize_text_field(wp_unslash($_POST['token'] ?? ''));
    $dir = spek_image_import_session_dir($token);

    if ($token && is_dir($dir)) {
        spek_image_import_delete_tree($dir);
    }

    wp_send_json_success();
}
add_action('wp_ajax_spek_image_import_cleanup', 'spek_ajax_image_import_cleanup');

function spek_render_product_image_importer_page(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(
            esc_html__(
                'Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη σελίδα.',
                'spek-theme'
            )
        );
    }

    spek_image_import_cleanup_old_sessions();
    $nonce = wp_create_nonce('spek_product_image_import_ajax');
    $learned_aliases = spek_image_import_manual_aliases();
    $learned_alias_count = count((array) $learned_aliases['filename']);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Μαζική εισαγωγή εικόνων προϊόντων', 'spek-theme'); ?></h1>

        <p style="max-width:980px">
            <?php esc_html_e(
                'Επιλέξτε τον φάκελο εικόνων. Πρώτα γίνεται dry run και αντιστοίχιση χωρίς καμία αλλαγή. Μόνο οι ασφαλείς αντιστοιχίσεις μπορούν μετά να εισαχθούν.',
                'spek-theme'
            ); ?>
        </p>

        <div class="notice notice-info inline" style="max-width:980px">
            <p>
                <strong><?php esc_html_e('Κανόνες:', 'spek-theme'); ?></strong>
                <?php esc_html_e(
                    'Κωδικός στην αρχή του filename έχει προτεραιότητα. Υποστηρίζονται zero-padded κωδικοί, π.χ. 39 → 00039_set.jpg. Αν δεν υπάρχει κωδικός, γίνεται ασφαλής προσπάθεια αντιστοίχισης από το όνομα/περιγραφή προϊόντος. Αμφίβολες περιπτώσεις δεν εισάγονται. Η κανονικοποίηση γίνεται πλέον τοπικά στον browser πριν το upload: 1600×1600 px, λευκός καμβάς, ασφαλές περιθώριο, χωρίς crop ή παραμόρφωση. Έτσι ο server δεν επεξεργάζεται βαριές αρχικές εικόνες.',
                    'spek-theme'
                ); ?>
            </p>
            <p style="margin-top:10px">
                <strong><?php esc_html_e('Unmatched Resolver:', 'spek-theme'); ?></strong>
                <?php esc_html_e(
                    'Μετά το dry run, οι αμφίβολες και μη αντιστοιχισμένες εικόνες μπορούν να συνδεθούν χειροκίνητα με προϊόν. Η επιλογή μπορεί να αποθηκευτεί ως learned alias για επόμενα imports.',
                    'spek-theme'
                ); ?>
                <span id="spek-learned-alias-count">
                    <?php echo esc_html(sprintf(' Learned aliases: %d.', $learned_alias_count)); ?>
                </span>
                <button
                    type="button"
                    class="button-link-delete"
                    id="spek_clear_learned_aliases"
                    style="<?php echo $learned_alias_count > 0 ? '' : 'display:none'; ?>"
                >
                    <?php esc_html_e('Καθαρισμός learned aliases', 'spek-theme'); ?>
                </button>
            </p>
        </div>

        <div class="card" style="max-width:980px;padding:20px;margin-top:18px">
            <h2 style="margin-top:0">
                <?php esc_html_e('1. Επιλογή εικόνων', 'spek-theme'); ?>
            </h2>

            <input
                type="file"
                id="spek_image_folder"
                webkitdirectory
                directory
                multiple
                accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
            >

            <p>
                <button
                    type="button"
                    class="button button-primary"
                    id="spek_image_analyze"
                >
                    <?php esc_html_e(
                        'Ανέβασμα & Έλεγχος αντιστοίχισης',
                        'spek-theme'
                    ); ?>
                </button>
            </p>
        </div>

        <div
            id="spek-image-progress"
            style="display:none;max-width:980px;margin-top:20px"
        >
            <div style="height:18px;background:#dcdcde;border-radius:4px;overflow:hidden">
                <div
                    id="spek-image-progress-bar"
                    style="width:0;height:100%;background:#2271b1"
                ></div>
            </div>
            <p id="spek-image-progress-text"></p>
        </div>

        <div
            id="spek-image-results"
            style="max-width:1180px;margin-top:22px"
        ></div>

        <div
            id="spek-image-import-actions"
            class="card"
            style="display:none;max-width:980px;padding:20px;margin-top:18px"
        >
            <h2 style="margin-top:0">
                <?php esc_html_e('2. Πραγματική εισαγωγή', 'spek-theme'); ?>
            </h2>

            <label style="display:block;margin:8px 0">
                <input type="checkbox" id="spek_replace_featured" checked>
                <?php esc_html_e(
                    'Αντικατάσταση υπάρχουσας χαρακτηριστικής εικόνας',
                    'spek-theme'
                ); ?>
            </label>

            <label style="display:block;margin:8px 0">
                <input type="checkbox" id="spek_replace_gallery">
                <?php esc_html_e(
                    'Καθαρισμός υπάρχοντος gallery πριν προστεθούν οι νέες εικόνες',
                    'spek-theme'
                ); ?>
            </label>

            <p>
                <button
                    type="button"
                    class="button button-primary"
                    id="spek_image_commit"
                >
                    <?php esc_html_e(
                        'Εισαγωγή ασφαλών αντιστοιχίσεων',
                        'spek-theme'
                    ); ?>
                </button>
            </p>
        </div>
    </div>

    <style>
        @media (max-width: 782px) {
            .spek-resolver-card {
                grid-template-columns: 1fr !important;
            }

            .spek-resolver-card > div:first-child img {
                width: 100% !important;
                max-width: 220px;
                height: auto !important;
                aspect-ratio: 1 / 1;
            }
        }
    </style>

    <script>
    (function(){
        'use strict';

        const ajaxUrl = window.ajaxurl;
        const nonce = <?php echo wp_json_encode($nonce); ?>;
        const folder = document.getElementById('spek_image_folder');
        const analyzeBtn = document.getElementById('spek_image_analyze');
        const commitBtn = document.getElementById('spek_image_commit');
        const results = document.getElementById('spek-image-results');
        const actions = document.getElementById('spek-image-import-actions');
        const progress = document.getElementById('spek-image-progress');
        const bar = document.getElementById('spek-image-progress-bar');
        const progressText = document.getElementById('spek-image-progress-text');
        const clearAliasesBtn = document.getElementById('spek_clear_learned_aliases');

        let token = '';
        let lastAnalysis = null;
        const searchTimers = new Map();

        function esc(value) {
            const div = document.createElement('div');
            div.textContent = String(value || '');
            return div.innerHTML;
        }

        function attr(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        async function post(formData) {
            const response = await fetch(
                ajaxUrl,
                {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                }
            );

            const raw = await response.text();
            let json = null;

            try {
                json = JSON.parse(raw);
            } catch (error) {
                const plain = String(raw || '')
                    .replace(/<style[\s\S]*?<\/style>/gi, ' ')
                    .replace(/<script[\s\S]*?<\/script>/gi, ' ')
                    .replace(/<[^>]+>/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();

                throw new Error(
                    plain
                        ? 'Server error (' + response.status + '): ' + plain.slice(0, 500)
                        : 'Server error (' + response.status + '). Η απάντηση δεν ήταν έγκυρο JSON.'
                );
            }

            if (!response.ok || !json.success) {
                throw new Error(
                    json && json.data && json.data.message
                        ? json.data.message
                        : 'Import error (' + response.status + ')'
                );
            }

            return json.data;
        }

        function formData(action) {
            const data = new FormData();
            data.set('action', action);
            data.set('nonce', nonce);

            if (token) {
                data.set('token', token);
            }

            return data;
        }

        function setProgress(done, total, label) {
            progress.style.display = 'block';

            const percent = total
                ? Math.round((done / total) * 100)
                : 0;

            bar.style.width = percent + '%';
            progressText.textContent = label + ' ' + done + '/' + total;
        }

        function matchedTotal(summary) {
            return Number(summary.matched_code || 0)
                + Number(summary.matched_title || 0)
                + Number(summary.matched_manual || 0);
        }

        function previewUrl(fileId) {
            return ajaxUrl
                + '?action=spek_image_import_preview'
                + '&nonce=' + encodeURIComponent(nonce)
                + '&token=' + encodeURIComponent(token)
                + '&file_id=' + encodeURIComponent(fileId);
        }

        function reasonLabel(reason) {
            const labels = {
                duplicate_sku: 'Διπλός SKU',
                legacy_code_candidates: 'Πολλαπλά legacy matches',
                title_candidates: 'Πολλαπλά title matches',
                manual_review: 'Χειροκίνητη έγκριση',
                learned_filename_alias: 'Learned filename alias',
                learned_numeric_alias: 'Learned numeric alias'
            };

            return labels[reason] || reason || '';
        }

        function productButton(item, extraClass) {
            const imageStatus = item.has_image
                ? '<small style="color:#646970"> · έχει εικόνα</small>'
                : '';

            return '<button type="button" class="button spek-resolver-product '
                + (extraClass || '') + '"'
                + ' data-product-id="' + attr(item.product_id) + '"'
                + ' data-sku="' + attr(item.sku) + '"'
                + ' data-title="' + attr(item.title) + '">'
                + '<strong>' + esc(item.sku) + '</strong> '
                + esc(item.title)
                + imageStatus
                + '</button>';
        }

        function resolverCard(entry) {
            let suggestions = '';

            if (entry.suggestions && entry.suggestions.length) {
                suggestions =
                    '<div style="margin-top:10px"><small><strong>Προτάσεις:</strong></small>'
                    + '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px">'
                    + entry.suggestions.map(item => productButton(item, 'button-small')).join('')
                    + '</div></div>';
            }

            return '<div class="card spek-resolver-card"'
                + ' data-file-id="' + attr(entry.file_id) + '"'
                + ' style="margin:0;padding:16px;display:grid;grid-template-columns:130px minmax(0,1fr);gap:18px">'
                + '<div>'
                + '<img src="' + previewUrl(entry.file_id) + '" alt=""'
                + ' style="width:130px;height:130px;object-fit:contain;background:#fff;border:1px solid #dcdcde;border-radius:8px">'
                + '</div>'
                + '<div>'
                + '<div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start">'
                + '<div><strong>' + esc(entry.file) + '</strong>'
                + '<div style="margin-top:4px;color:#646970">'
                + esc(entry.status === 'ambiguous' ? 'Χρειάζεται έλεγχο' : 'Χωρίς αντιστοίχιση')
                + (entry.reason ? ' · ' + esc(reasonLabel(entry.reason)) : '')
                + '</div></div>'
                + '</div>'
                + suggestions
                + '<div style="margin-top:14px">'
                + '<label><strong>Αναζήτηση προϊόντος</strong></label>'
                + '<input type="search" class="regular-text spek-resolver-search"'
                + ' placeholder="SKU ή τίτλος προϊόντος" style="width:100%;margin-top:6px">'
                + '<div class="spek-resolver-search-results" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px"></div>'
                + '</div>'
                + '<div class="spek-resolver-chosen" style="margin-top:12px;padding:10px 12px;background:#f6f7f7;border-radius:6px;color:#50575e">'
                + 'Δεν έχει επιλεγεί προϊόν.'
                + '</div>'
                + '<div style="display:flex;flex-wrap:wrap;gap:14px;align-items:end;margin-top:14px">'
                + '<label>Χρήση εικόνας<br>'
                + '<select class="spek-resolver-role">'
                + '<option value="auto">Αυτόματα</option>'
                + '<option value="featured">Κύρια εικόνα</option>'
                + '<option value="gallery">Gallery</option>'
                + '</select></label>'
                + '<label style="padding-bottom:6px">'
                + '<input type="checkbox" class="spek-resolver-remember" checked> '
                + 'Να θυμάται την αντιστοίχιση'
                + '</label>'
                + '<button type="button" class="button button-primary spek-resolver-apply" disabled>'
                + 'Αντιστοίχιση'
                + '</button>'
                + '</div>'
                + '</div>'
                + '</div>';
        }

        function renderAnalysis(data) {
            lastAnalysis = data;
            const summary = data.summary || {};
            const safe = matchedTotal(summary);
            const learnedCount = Number(data.learned_alias_count || 0);
            const learnedCounter = document.getElementById('spek-learned-alias-count');

            if (learnedCounter) {
                learnedCounter.textContent = ' Learned aliases: ' + learnedCount + '.';
            }

            if (clearAliasesBtn) {
                clearAliasesBtn.style.display = learnedCount > 0 ? '' : 'none';
            }

            let html =
                '<div class="notice notice-success inline">'
                + '<p><strong>Dry run:</strong> '
                + esc(summary.total)
                + ' εικόνες · '
                + esc(safe)
                + ' αντιστοιχισμένες · '
                + esc(summary.ambiguous)
                + ' έλεγχος · '
                + esc(summary.unmatched)
                + ' χωρίς αντιστοίχιση · '
                + esc(summary.products)
                + ' προϊόντα.</p></div>';

            html +=
                '<table class="widefat striped">'
                + '<thead><tr>'
                + '<th>Αρχείο</th>'
                + '<th>Κατάσταση</th>'
                + '<th>SKU</th>'
                + '<th>Προϊόν</th>'
                + '<th>Μέθοδος</th>'
                + '</tr></thead><tbody>';

            (data.entries || []).forEach(entry => {
                const status =
                    entry.status === 'matched'
                        ? '✓ Match'
                        : (
                            entry.status === 'ambiguous'
                                ? 'Χρειάζεται έλεγχο'
                                : 'Χωρίς match'
                        );

                html +=
                    '<tr>'
                    + '<td>' + esc(entry.file) + '</td>'
                    + '<td>' + status
                    + (entry.reason ? '<br><small>' + esc(reasonLabel(entry.reason)) + '</small>' : '')
                    + '</td>'
                    + '<td>' + esc(entry.sku) + '</td>'
                    + '<td>' + esc(entry.product) + '</td>'
                    + '<td>'
                    + esc(entry.method)
                    + (
                        entry.confidence
                            ? ' ' + esc(entry.confidence) + '%'
                            : ''
                    )
                    + '</td>'
                    + '</tr>';
            });

            html += '</tbody></table>';

            const unresolved = (data.entries || []).filter(entry => {
                return entry.status === 'ambiguous' || entry.status === 'unmatched';
            });

            if (unresolved.length) {
                html +=
                    '<div style="margin-top:28px">'
                    + '<h2 style="margin-bottom:6px">3. Unmatched Resolver</h2>'
                    + '<p style="max-width:900px;color:#50575e">'
                    + 'Επίλεξε προτεινόμενο προϊόν ή αναζήτησε με SKU/τίτλο. '
                    + 'Καμία από αυτές τις εικόνες δεν θα εισαχθεί πριν εγκριθεί.'
                    + '</p>'
                    + '<div style="display:grid;gap:14px;margin-top:16px">'
                    + unresolved.map(resolverCard).join('')
                    + '</div></div>';
            }

            results.innerHTML = html;
            actions.style.display = safe > 0 ? 'block' : 'none';
        }

        async function searchProducts(input) {
            const card = input.closest('.spek-resolver-card');
            const target = card.querySelector('.spek-resolver-search-results');
            const query = input.value.trim();

            if (query.length < 2) {
                target.innerHTML = '';
                return;
            }

            const data = formData('spek_image_import_product_search');
            data.set('q', query);

            try {
                const response = await post(data);
                target.innerHTML = (response.items || []).length
                    ? response.items.map(item => productButton(item, 'button-small')).join('')
                    : '<small>Δεν βρέθηκε προϊόν.</small>';
            } catch (error) {
                target.innerHTML = '<small>' + esc(error.message) + '</small>';
            }
        }

        function normalizedFilename(name) {
            const base = String(name || 'product-image')
                .replace(/\.[^.]+$/, '')
                .replace(/[^a-zA-Z0-9._-]+/g, '-')
                .replace(/^-+|-+$/g, '') || 'product-image';

            return base + '-spek-1600.jpg';
        }

        async function decodeImage(file) {
            if ('createImageBitmap' in window) {
                try {
                    return await createImageBitmap(
                        file,
                        { imageOrientation: 'from-image' }
                    );
                } catch (error) {
                    // Fall through to HTMLImageElement decoding.
                }
            }

            return await new Promise((resolve, reject) => {
                const url = URL.createObjectURL(file);
                const image = new Image();

                image.onload = function() {
                    URL.revokeObjectURL(url);
                    resolve(image);
                };

                image.onerror = function() {
                    URL.revokeObjectURL(url);
                    reject(new Error('Δεν ήταν δυνατή η ανάγνωση της εικόνας ' + file.name));
                };

                image.src = url;
            });
        }

        function isDarkPixel(data, index) {
            return (
                data[index] <= 50
                && data[index + 1] <= 50
                && data[index + 2] <= 50
                && data[index + 3] >= 180
            );
        }

        function cleanEdgeConnectedDarkBackground(ctx, width, height) {
            if (width < 2 || height < 2) {
                return;
            }

            const imageData = ctx.getImageData(0, 0, width, height);
            const data = imageData.data;
            const pixels = width * height;
            const corners = [
                0,
                width - 1,
                (height - 1) * width,
                pixels - 1
            ];

            const darkCorners = corners.filter(pixel => {
                return isDarkPixel(data, pixel * 4);
            });

            // Conservative safety gate: only treat black as background when
            // at least three corners agree.
            if (darkCorners.length < 3) {
                return;
            }

            const visited = new Uint8Array(pixels);
            const queue = new Int32Array(pixels);
            let head = 0;
            let tail = 0;

            function enqueue(pixel) {
                if (
                    pixel < 0
                    || pixel >= pixels
                    || visited[pixel]
                    || !isDarkPixel(data, pixel * 4)
                ) {
                    return;
                }

                visited[pixel] = 1;
                queue[tail++] = pixel;
            }

            darkCorners.forEach(enqueue);

            while (head < tail) {
                const pixel = queue[head++];
                const index = pixel * 4;

                data[index] = 255;
                data[index + 1] = 255;
                data[index + 2] = 255;
                data[index + 3] = 255;

                const x = pixel % width;
                const y = Math.floor(pixel / width);

                if (x > 0) {
                    enqueue(pixel - 1);
                }
                if (x < width - 1) {
                    enqueue(pixel + 1);
                }
                if (y > 0) {
                    enqueue(pixel - width);
                }
                if (y < height - 1) {
                    enqueue(pixel + width);
                }
            }

            ctx.putImageData(imageData, 0, 0);
        }

        async function normalizeImageInBrowser(file) {
            const canvasSize = <?php echo (int) SPEK_PRODUCT_IMAGE_CANVAS; ?>;
            const padding = <?php echo (int) SPEK_PRODUCT_IMAGE_PADDING; ?>;
            const inner = canvasSize - (padding * 2);
            const image = await decodeImage(file);

            const width = Number(image.width || image.naturalWidth || 0);
            const height = Number(image.height || image.naturalHeight || 0);

            if (!width || !height) {
                if (typeof image.close === 'function') {
                    image.close();
                }
                throw new Error('Μη έγκυρες διαστάσεις εικόνας: ' + file.name);
            }

            const scale = Math.min(inner / width, inner / height);
            const targetWidth = Math.max(1, Math.round(width * scale));
            const targetHeight = Math.max(1, Math.round(height * scale));

            // Work only at the final display size. This keeps browser memory
            // predictable even when the original image is very large.
            const productCanvas = document.createElement('canvas');
            productCanvas.width = targetWidth;
            productCanvas.height = targetHeight;

            const productCtx = productCanvas.getContext('2d', {
                alpha: false,
                willReadFrequently: true
            });

            productCtx.fillStyle = '#ffffff';
            productCtx.fillRect(0, 0, targetWidth, targetHeight);
            productCtx.imageSmoothingEnabled = true;
            productCtx.imageSmoothingQuality = 'high';
            productCtx.drawImage(
                image,
                0,
                0,
                targetWidth,
                targetHeight
            );

            if (typeof image.close === 'function') {
                image.close();
            }

            // Legacy SPEK photos often have a baked-in black studio background.
            // Remove only near-black pixels connected to >=3 outer corners.
            cleanEdgeConnectedDarkBackground(
                productCtx,
                targetWidth,
                targetHeight
            );

            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = canvasSize;
            finalCanvas.height = canvasSize;

            const finalCtx = finalCanvas.getContext('2d', { alpha: false });
            finalCtx.fillStyle = '#ffffff';
            finalCtx.fillRect(0, 0, canvasSize, canvasSize);
            finalCtx.drawImage(
                productCanvas,
                Math.floor((canvasSize - targetWidth) / 2),
                Math.floor((canvasSize - targetHeight) / 2)
            );

            const blob = await new Promise((resolve, reject) => {
                finalCanvas.toBlob(
                    result => result
                        ? resolve(result)
                        : reject(new Error('Αποτυχία δημιουργίας JPEG: ' + file.name)),
                    'image/jpeg',
                    0.90
                );
            });

            return {
                blob,
                name: normalizedFilename(file.name)
            };
        }

        analyzeBtn.addEventListener('click', async function(){
            const files = Array.from(folder.files || [])
                .filter(file => /\.(jpe?g|png|webp)$/i.test(file.name));

            if (!files.length) {
                alert(
                    <?php echo wp_json_encode(
                        __('Επιλέξτε φάκελο με εικόνες.', 'spek-theme')
                    ); ?>
                );
                return;
            }

            analyzeBtn.disabled = true;
            results.innerHTML = '';
            actions.style.display = 'none';

            try {
                let data = await post(
                    formData('spek_image_import_create_session')
                );

                token = data.token;

                for (let i = 0; i < files.length; i++) {
                    setProgress(
                        i,
                        files.length,
                        <?php echo wp_json_encode(
                            __('Ανέβασμα', 'spek-theme')
                        ); ?>
                    );

                    const normalized = await normalizeImageInBrowser(
                        files[i]
                    );

                    const uploadData = formData(
                        'spek_image_import_upload_file'
                    );

                    uploadData.set(
                        'original_name',
                        files[i].name
                    );

                    uploadData.set(
                        'upload_name',
                        normalized.name
                    );

                    uploadData.set(
                        'client_normalized',
                        '1'
                    );

                    uploadData.set(
                        'image_file',
                        normalized.blob,
                        normalized.name
                    );

                    await post(uploadData);
                }

                setProgress(
                    files.length,
                    files.length,
                    <?php echo wp_json_encode(
                        __('Ανάλυση', 'spek-theme')
                    ); ?>
                );

                data = await post(
                    formData('spek_image_import_analyze')
                );

                renderAnalysis(data);
            } catch (error) {
                results.innerHTML =
                    '<div class="notice notice-error inline">' +
                    '<p>' +
                    esc(error.message) +
                    '</p></div>';
            } finally {
                analyzeBtn.disabled = false;
            }
        });

        results.addEventListener('input', function(event) {
            const input = event.target.closest('.spek-resolver-search');

            if (!input) {
                return;
            }

            const key = input.closest('.spek-resolver-card').dataset.fileId;

            if (searchTimers.has(key)) {
                clearTimeout(searchTimers.get(key));
            }

            searchTimers.set(
                key,
                setTimeout(() => searchProducts(input), 280)
            );
        });

        results.addEventListener('click', async function(event) {
            const productButton = event.target.closest('.spek-resolver-product');

            if (productButton) {
                const card = productButton.closest('.spek-resolver-card');
                const apply = card.querySelector('.spek-resolver-apply');
                const chosen = card.querySelector('.spek-resolver-chosen');

                card.dataset.productId = productButton.dataset.productId;
                chosen.innerHTML =
                    '<strong>' + esc(productButton.dataset.sku) + '</strong> '
                    + esc(productButton.dataset.title);
                apply.disabled = false;
                return;
            }

            const apply = event.target.closest('.spek-resolver-apply');

            if (!apply) {
                return;
            }

            const card = apply.closest('.spek-resolver-card');
            const productId = card.dataset.productId;

            if (!productId) {
                return;
            }

            apply.disabled = true;
            apply.textContent = 'Αποθήκευση…';

            const data = formData('spek_image_import_resolve');
            data.set('file_id', card.dataset.fileId);
            data.set('product_id', productId);
            data.set(
                'image_role',
                card.querySelector('.spek-resolver-role').value
            );

            if (card.querySelector('.spek-resolver-remember').checked) {
                data.set('remember', '1');
            }

            try {
                const response = await post(data);
                renderAnalysis(response);
            } catch (error) {
                alert(error.message);
                apply.disabled = false;
                apply.textContent = 'Αντιστοίχιση';
            }
        });

        if (clearAliasesBtn) {
            clearAliasesBtn.addEventListener('click', async function() {
                if (!window.confirm('Να διαγραφούν όλα τα learned aliases;')) {
                    return;
                }

                clearAliasesBtn.disabled = true;

                try {
                    await post(formData('spek_image_import_clear_aliases'));
                    clearAliasesBtn.disabled = false;
                    clearAliasesBtn.style.display = 'none';
                    const counter = document.getElementById('spek-learned-alias-count');

                    if (counter) {
                        counter.textContent = ' Learned aliases: 0.';
                    }
                } catch (error) {
                    alert(error.message);
                    clearAliasesBtn.disabled = false;
                }
            });
        }

        commitBtn.addEventListener('click', async function(){
            if (!token) {
                return;
            }

            commitBtn.disabled = true;

            let offset = 0;
            let imported = 0;
            let reused = 0;
            let errors = [];

            try {
                while (true) {
                    const data = formData(
                        'spek_image_import_commit_batch'
                    );

                    data.set('offset', String(offset));

                    if (
                        document
                            .getElementById('spek_replace_featured')
                            .checked
                    ) {
                        data.set('replace_featured', '1');
                    }

                    if (
                        document
                            .getElementById('spek_replace_gallery')
                            .checked
                    ) {
                        data.set('replace_gallery', '1');
                    }

                    const response = await post(data);

                    offset = response.offset;
                    imported += response.stats.imported || 0;
                    reused += response.stats.reused || 0;
                    errors = errors.concat(
                        response.stats.errors || []
                    );

                    setProgress(
                        offset,
                        response.total,
                        <?php echo wp_json_encode(
                            __('Εισαγωγή', 'spek-theme')
                        ); ?>
                    );

                    if (response.done) {
                        break;
                    }

                    // Let the shared-hosting PHP worker breathe between images.
                    await new Promise(resolve => setTimeout(resolve, 900));
                }

                results.insertAdjacentHTML(
                    'afterbegin',
                    '<div class="notice notice-success inline">' +
                    '<p>Ολοκληρώθηκε: ' +
                    imported +
                    ' νέες εικόνες · ' +
                    reused +
                    ' επαναχρησιμοποιήθηκαν' +
                    (
                        errors.length
                            ? ' · ' + errors.length + ' σφάλματα'
                            : ''
                    ) +
                    '.</p></div>'
                );

                actions.style.display = 'none';
            } catch (error) {
                alert(error.message);
            } finally {
                commitBtn.disabled = false;
            }
        });
    })();
    </script>
    <?php
}
