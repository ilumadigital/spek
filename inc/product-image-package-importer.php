<?php
/**
 * SPEK Image Matcher → WordPress package importer.
 *
 * Consumes the ZIP produced by the local SPEK Image Matcher app. The importer
 * is intentionally additive: it never replaces an existing featured image,
 * never clears a product gallery, and never creates a second Media Library
 * attachment for identical source bytes.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('SPEK_MATCHER_PACKAGE_FORMAT')) {
    define('SPEK_MATCHER_PACKAGE_FORMAT', 'spek-image-matcher-wordpress-v1');
}
if (!defined('SPEK_MATCHER_PACKAGE_TTL')) {
    define('SPEK_MATCHER_PACKAGE_TTL', DAY_IN_SECONDS);
}
if (!defined('SPEK_MATCHER_PACKAGE_MAX_ROWS')) {
    define('SPEK_MATCHER_PACKAGE_MAX_ROWS', 5000);
}

function spek_product_image_package_importer_menu(): void
{
    add_submenu_page(
        'edit.php?post_type=spek_product',
        __('SPEK Image Matcher ZIP', 'spek-theme'),
        __('Image Matcher ZIP', 'spek-theme'),
        'manage_options',
        'spek-image-matcher-package-importer',
        'spek_render_product_image_package_importer_page'
    );
}
add_action('admin_menu', 'spek_product_image_package_importer_menu', 12);

function spek_image_package_guard(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(
            ['message' => __('Δεν έχετε δικαίωμα για αυτή την ενέργεια.', 'spek-theme')],
            403
        );
    }

    check_ajax_referer('spek_image_matcher_package_ajax', 'nonce');
}

function spek_image_package_base_dir(): string
{
    $upload = wp_upload_dir();
    return trailingslashit($upload['basedir']) . 'spek-image-matcher-packages';
}

function spek_image_package_session_dir(string $token): string
{
    $token = preg_replace('/[^a-f0-9]/', '', strtolower($token));
    return trailingslashit(spek_image_package_base_dir()) . $token;
}

function spek_image_package_delete_tree(string $dir): void
{
    if (function_exists('spek_image_import_delete_tree')) {
        spek_image_import_delete_tree($dir);
        return;
    }

    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    if (!is_array($items)) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            spek_image_package_delete_tree($path);
        } else {
            @unlink($path);
        }
    }

    @rmdir($dir);
}

function spek_image_package_cleanup_old_sessions(): void
{
    $base = spek_image_package_base_dir();
    if (!is_dir($base)) {
        return;
    }

    $now = time();
    foreach ((array) glob(trailingslashit($base) . '*', GLOB_ONLYDIR) as $dir) {
        $mtime = @filemtime($dir);
        if ($mtime && ($now - $mtime) > (int) SPEK_MATCHER_PACKAGE_TTL) {
            spek_image_package_delete_tree($dir);
        }
    }
}

function spek_image_package_safe_zip_path(string $path): string
{
    if (strpos($path, "\0") !== false) {
        return '';
    }

    $path = str_replace('\\', '/', trim($path));
    $path = preg_replace('#/+#', '/', $path);
    $path = is_string($path) ? ltrim($path, '/') : '';

    if ($path === '' || preg_match('/^[A-Za-z]:\//', $path)) {
        return '';
    }

    $parts = explode('/', $path);
    foreach ($parts as $part) {
        if ($part === '' || $part === '.' || $part === '..') {
            return '';
        }
    }

    return implode('/', $parts);
}

function spek_image_package_image_entry(string $relative_path): string
{
    $relative_path = spek_image_package_safe_zip_path($relative_path);
    if ($relative_path === '') {
        return '';
    }

    $entry = spek_image_package_safe_zip_path('images/' . $relative_path);
    if ($entry === '' || strpos($entry, 'images/') !== 0) {
        return '';
    }

    $ext = strtolower((string) pathinfo($entry, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        return '';
    }

    return $entry;
}

function spek_image_package_open(string $zip_path)
{
    if (!class_exists('ZipArchive')) {
        return new WP_Error(
            'zip_extension_missing',
            __('Το PHP ZipArchive extension δεν είναι ενεργό στον server.', 'spek-theme')
        );
    }

    if (!is_file($zip_path)) {
        return new WP_Error('missing_zip', __('Το ZIP δεν βρέθηκε.', 'spek-theme'));
    }

    $zip = new ZipArchive();
    $result = $zip->open($zip_path, ZipArchive::RDONLY);

    if ($result !== true) {
        return new WP_Error(
            'invalid_zip',
            __('Το αρχείο δεν είναι έγκυρο ZIP ή δεν μπορεί να ανοιχτεί.', 'spek-theme')
        );
    }

    return $zip;
}

function spek_image_package_read_text(ZipArchive $zip, string $entry, int $max_bytes = 5242880)
{
    $stat = $zip->statName($entry, ZipArchive::FL_NOCASE);
    if (!is_array($stat)) {
        return new WP_Error(
            'missing_manifest_entry',
            sprintf(__('Λείπει το %s από το package.', 'spek-theme'), $entry)
        );
    }

    if ((int) ($stat['size'] ?? 0) > $max_bytes) {
        return new WP_Error(
            'manifest_too_large',
            sprintf(__('Το %s είναι υπερβολικά μεγάλο.', 'spek-theme'), $entry)
        );
    }

    $content = $zip->getFromName($entry, 0, ZipArchive::FL_NOCASE);
    if (!is_string($content)) {
        return new WP_Error(
            'manifest_read_failed',
            sprintf(__('Δεν ήταν δυνατή η ανάγνωση του %s.', 'spek-theme'), $entry)
        );
    }

    return $content;
}

function spek_image_package_manifest_from_csv(string $csv)
{
    $stream = fopen('php://temp', 'r+');
    if (!$stream) {
        return new WP_Error('csv_stream_failed', __('Αποτυχία ανάγνωσης manifest.csv.', 'spek-theme'));
    }

    fwrite($stream, $csv);
    rewind($stream);

    $headers = fgetcsv($stream);
    if (!is_array($headers)) {
        fclose($stream);
        return new WP_Error('invalid_csv', __('Το manifest.csv δεν έχει headers.', 'spek-theme'));
    }

    $headers = array_map(static function ($header) {
        $header = (string) $header;
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        return trim((string) $header);
    }, $headers);

    $rows = [];
    while (($values = fgetcsv($stream)) !== false) {
        if (!array_filter($values, static fn($value) => trim((string) $value) !== '')) {
            continue;
        }

        $values = array_pad($values, count($headers), '');
        $rows[] = array_combine($headers, array_slice($values, 0, count($headers)));

        if (count($rows) > (int) SPEK_MATCHER_PACKAGE_MAX_ROWS) {
            fclose($stream);
            return new WP_Error(
                'too_many_rows',
                __('Το package περιέχει υπερβολικά πολλές εγγραφές.', 'spek-theme')
            );
        }
    }

    fclose($stream);
    return $rows;
}

function spek_image_package_load_manifest(ZipArchive $zip)
{
    $package_info_raw = spek_image_package_read_text($zip, 'package-info.json', 1048576);
    if (is_wp_error($package_info_raw)) {
        return $package_info_raw;
    }

    $package_info = json_decode($package_info_raw, true);
    if (!is_array($package_info)) {
        return new WP_Error('invalid_package_info', __('Μη έγκυρο package-info.json.', 'spek-theme'));
    }

    if ((string) ($package_info['format'] ?? '') !== (string) SPEK_MATCHER_PACKAGE_FORMAT) {
        return new WP_Error(
            'unsupported_package_format',
            __('Το ZIP δεν είναι SPEK Image Matcher WordPress package v1.', 'spek-theme')
        );
    }

    if (!empty($package_info['post_type']) && (string) $package_info['post_type'] !== 'spek_product') {
        return new WP_Error('invalid_post_type', __('Το package δεν αφορά SPEK products.', 'spek-theme'));
    }

    if (!empty($package_info['sku_meta_key']) && (string) $package_info['sku_meta_key'] !== 'product_code') {
        return new WP_Error('invalid_sku_key', __('Το package χρησιμοποιεί άγνωστο SKU meta key.', 'spek-theme'));
    }

    $manifest_raw = spek_image_package_read_text($zip, 'manifest.json');
    if (!is_wp_error($manifest_raw)) {
        $rows = json_decode($manifest_raw, true);
        if (!is_array($rows)) {
            return new WP_Error('invalid_manifest_json', __('Μη έγκυρο manifest.json.', 'spek-theme'));
        }
    } else {
        $csv_raw = spek_image_package_read_text($zip, 'manifest.csv');
        if (is_wp_error($csv_raw)) {
            return $manifest_raw;
        }

        $rows = spek_image_package_manifest_from_csv($csv_raw);
        if (is_wp_error($rows)) {
            return $rows;
        }
    }

    if (count($rows) > (int) SPEK_MATCHER_PACKAGE_MAX_ROWS) {
        return new WP_Error(
            'too_many_rows',
            __('Το package περιέχει υπερβολικά πολλές εγγραφές.', 'spek-theme')
        );
    }

    return [
        'package_info' => $package_info,
        'rows' => array_values($rows),
    ];
}

function spek_image_package_hash_entry(ZipArchive $zip, string $entry)
{
    $index = $zip->locateName($entry, ZipArchive::FL_NOCASE);
    if ($index === false) {
        return new WP_Error('missing_image', __('Λείπει εικόνα από το ZIP.', 'spek-theme'));
    }

    $stat = $zip->statIndex((int) $index);
    if (!is_array($stat)) {
        return new WP_Error('missing_image', __('Λείπει εικόνα από το ZIP.', 'spek-theme'));
    }

    $max_bytes = defined('SPEK_IMAGE_IMPORT_MAX_FILE_BYTES')
        ? (int) SPEK_IMAGE_IMPORT_MAX_FILE_BYTES
        : 31457280;

    $size = (int) ($stat['size'] ?? 0);
    if ($size < 1 || $size > $max_bytes) {
        return new WP_Error(
            'invalid_image_size',
            sprintf(
                __('Η εικόνα %1$s είναι κενή ή υπερβαίνει το όριο των %2$s.', 'spek-theme'),
                wp_basename($entry),
                size_format($max_bytes)
            )
        );
    }

    $actual_name = (string) ($stat['name'] ?? $entry);
    $stream = $zip->getStream($actual_name);
    if (!is_resource($stream)) {
        return new WP_Error('image_stream_failed', __('Αποτυχία ανάγνωσης εικόνας από ZIP.', 'spek-theme'));
    }

    $hash = hash_init('sha256');
    while (!feof($stream)) {
        $chunk = fread($stream, 1048576);
        if ($chunk === false) {
            fclose($stream);
            return new WP_Error('image_read_failed', __('Αποτυχία ανάγνωσης εικόνας από ZIP.', 'spek-theme'));
        }
        if ($chunk !== '') {
            hash_update($hash, $chunk);
        }
    }
    fclose($stream);

    return hash_final($hash);
}

function spek_image_package_find_existing_attachment(string $sha256): int
{
    if ($sha256 === '') {
        return 0;
    }

    $ids = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'suppress_filters' => true,
        'meta_key' => '_spek_source_sha256',
        'meta_value' => $sha256,
    ]);

    return $ids ? (int) $ids[0] : 0;
}

function spek_image_package_product_identity(string $sku)
{
    if (function_exists('spek_import_product_identity')) {
        return spek_import_product_identity($sku);
    }

    $sku = trim($sku);
    if ($sku === '') {
        return 0;
    }

    $ids = get_posts([
        'post_type' => 'spek_product',
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
        'posts_per_page' => 3,
        'fields' => 'ids',
        'no_found_rows' => true,
        'suppress_filters' => true,
        'meta_key' => 'product_code',
        'meta_value' => $sku,
    ]);

    $ids = array_values(array_unique(array_map('intval', (array) $ids)));
    if (count($ids) > 1) {
        return new WP_Error(
            'duplicate_sku',
            sprintf(__('SKU %s: βρέθηκαν πολλαπλά προϊόντα.', 'spek-theme'), $sku),
            ['ids' => $ids]
        );
    }

    return $ids ? (int) $ids[0] : 0;
}

function spek_image_package_gallery_ids(int $product_id): array
{
    if (function_exists('spek_image_import_gallery_ids')) {
        return spek_image_import_gallery_ids($product_id);
    }

    $gallery = get_post_meta($product_id, 'product_gallery', true);
    if (!is_array($gallery)) {
        $gallery = is_string($gallery) && $gallery !== ''
            ? preg_split('/[\s,]+/', $gallery)
            : [];
    }

    return array_values(array_unique(array_filter(array_map('absint', (array) $gallery))));
}

function spek_image_package_plan_action(int $product_id, int $attachment_id, bool $is_primary): string
{
    $featured_id = (int) get_post_thumbnail_id($product_id);
    $gallery = spek_image_package_gallery_ids($product_id);

    if (
        $attachment_id > 0
        && ($attachment_id === $featured_id || in_array($attachment_id, $gallery, true))
    ) {
        return 'skip_existing';
    }

    if ($is_primary && $featured_id === 0) {
        return $attachment_id > 0 ? 'reuse_featured' : 'import_featured';
    }

    return $attachment_id > 0 ? 'reuse_gallery' : 'import_gallery';
}

function spek_image_package_analyze_row(array $row, ZipArchive $zip)
{
    $sku = trim((string) ($row['sku'] ?? ''));
    $relative = (string) ($row['image_relative_path'] ?? '');
    $filename = trim((string) ($row['image_filename'] ?? ''));
    $is_primary = !empty($row['is_primary']) && (string) $row['is_primary'] !== '0';

    $result = [
        'sku' => $sku,
        'image_filename' => $filename,
        'image_relative_path' => $relative,
        'is_primary' => $is_primary ? 1 : 0,
        'match_method' => sanitize_text_field((string) ($row['match_method'] ?? '')),
        'confidence' => (float) ($row['confidence'] ?? 0),
        'status' => 'error',
        'action' => 'invalid',
        'message' => '',
        'product_id' => 0,
        'product_title' => '',
        'sha256' => '',
        'zip_entry' => '',
        'attachment_id' => 0,
    ];

    if ($sku === '') {
        $result['message'] = __('Λείπει SKU.', 'spek-theme');
        return $result;
    }

    if (!empty($row['wp_post_type']) && (string) $row['wp_post_type'] !== 'spek_product') {
        $result['message'] = __('Μη αναμενόμενο post type.', 'spek-theme');
        return $result;
    }

    if (!empty($row['wp_sku_meta']) && (string) $row['wp_sku_meta'] !== 'product_code') {
        $result['message'] = __('Μη αναμενόμενο SKU meta key.', 'spek-theme');
        return $result;
    }

    $zip_entry = spek_image_package_image_entry($relative);
    if ($zip_entry === '') {
        $result['message'] = __('Μη ασφαλές ή μη υποστηριζόμενο image path.', 'spek-theme');
        return $result;
    }

    if ($zip->locateName($zip_entry, ZipArchive::FL_NOCASE) === false) {
        $result['status'] = 'missing_image';
        $result['action'] = 'missing_image';
        $result['message'] = sprintf(__('Λείπει το %s από το ZIP.', 'spek-theme'), $zip_entry);
        return $result;
    }

    $sha256 = spek_image_package_hash_entry($zip, $zip_entry);
    if (is_wp_error($sha256)) {
        $result['message'] = $sha256->get_error_message();
        return $result;
    }

    $product_id = spek_image_package_product_identity($sku);
    if (is_wp_error($product_id)) {
        $result['status'] = $product_id->get_error_code() === 'duplicate_sku'
            ? 'duplicate_sku'
            : 'product_error';
        $result['action'] = 'skip_product_error';
        $result['message'] = $product_id->get_error_message();
        return $result;
    }

    if ((int) $product_id < 1) {
        $result['status'] = 'missing_product';
        $result['action'] = 'skip_missing_product';
        $result['message'] = sprintf(__('Δεν βρέθηκε προϊόν με SKU %s.', 'spek-theme'), $sku);
        return $result;
    }

    $attachment_id = spek_image_package_find_existing_attachment((string) $sha256);
    $action = spek_image_package_plan_action((int) $product_id, $attachment_id, $is_primary);

    $result['status'] = $action === 'skip_existing' ? 'skipped' : 'ready';
    $result['action'] = $action;
    $result['product_id'] = (int) $product_id;
    $result['product_title'] = (string) get_the_title((int) $product_id);
    $result['sha256'] = (string) $sha256;
    $result['zip_entry'] = $zip_entry;
    $result['attachment_id'] = (int) $attachment_id;
    $result['message'] = $action === 'skip_existing'
        ? __('Η ίδια εικόνα είναι ήδη συνδεδεμένη στο προϊόν.', 'spek-theme')
        : '';

    return $result;
}

function spek_image_package_summary(array $entries): array
{
    $summary = [
        'total' => count($entries),
        'ready' => 0,
        'new_media' => 0,
        'reuse_media' => 0,
        'featured' => 0,
        'gallery' => 0,
        'skipped_existing' => 0,
        'missing_product' => 0,
        'duplicate_sku' => 0,
        'missing_image' => 0,
        'errors' => 0,
    ];

    foreach ($entries as $entry) {
        $action = (string) ($entry['action'] ?? 'invalid');
        $status = (string) ($entry['status'] ?? 'error');

        if ($status === 'ready') {
            $summary['ready']++;
        }
        if (in_array($action, ['import_featured', 'import_gallery'], true)) {
            $summary['new_media']++;
        }
        if (in_array($action, ['reuse_featured', 'reuse_gallery'], true)) {
            $summary['reuse_media']++;
        }
        if (in_array($action, ['import_featured', 'reuse_featured'], true)) {
            $summary['featured']++;
        }
        if (in_array($action, ['import_gallery', 'reuse_gallery'], true)) {
            $summary['gallery']++;
        }
        if ($action === 'skip_existing') {
            $summary['skipped_existing']++;
        }
        if ($status === 'missing_product') {
            $summary['missing_product']++;
        } elseif ($status === 'duplicate_sku') {
            $summary['duplicate_sku']++;
        } elseif ($status === 'missing_image') {
            $summary['missing_image']++;
        } elseif ($status === 'error' || $status === 'product_error') {
            $summary['errors']++;
        }
    }

    return $summary;
}

function spek_ajax_image_package_analyze(): void
{
    spek_image_package_guard();
    spek_image_package_cleanup_old_sessions();

    if (
        empty($_FILES['package_zip']['tmp_name'])
        || !is_uploaded_file($_FILES['package_zip']['tmp_name'])
        || (int) ($_FILES['package_zip']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    ) {
        wp_send_json_error(['message' => __('Δεν παραλήφθηκε έγκυρο ZIP.', 'spek-theme')], 400);
    }

    $original_name = wp_basename((string) ($_FILES['package_zip']['name'] ?? 'package.zip'));
    if (strtolower((string) pathinfo($original_name, PATHINFO_EXTENSION)) !== 'zip') {
        wp_send_json_error(['message' => __('Επιλέξτε αρχείο .zip.', 'spek-theme')], 400);
    }

    $size = (int) @filesize($_FILES['package_zip']['tmp_name']);
    $max_upload = (int) wp_max_upload_size();
    if ($size < 1 || ($max_upload > 0 && $size > $max_upload)) {
        wp_send_json_error(
            ['message' => sprintf(__('Το ZIP υπερβαίνει το διαθέσιμο upload limit (%s).', 'spek-theme'), size_format($max_upload))],
            400
        );
    }

    $base = spek_image_package_base_dir();
    if (!wp_mkdir_p($base)) {
        wp_send_json_error(['message' => __('Δεν δημιουργήθηκε προσωρινός φάκελος.', 'spek-theme')], 500);
    }

    try {
        $token = bin2hex(random_bytes(16));
    } catch (Throwable $e) {
        $token = strtolower(wp_generate_password(32, false, false));
    }

    $dir = spek_image_package_session_dir($token);
    if (!wp_mkdir_p($dir)) {
        wp_send_json_error(['message' => __('Δεν δημιουργήθηκε συνεδρία import.', 'spek-theme')], 500);
    }

    $zip_path = trailingslashit($dir) . 'package.zip';
    if (!@move_uploaded_file($_FILES['package_zip']['tmp_name'], $zip_path)) {
        spek_image_package_delete_tree($dir);
        wp_send_json_error(['message' => __('Αποτυχία αποθήκευσης ZIP.', 'spek-theme')], 500);
    }

    $zip = spek_image_package_open($zip_path);
    if (is_wp_error($zip)) {
        spek_image_package_delete_tree($dir);
        wp_send_json_error(['message' => $zip->get_error_message()], 400);
    }

    $manifest = spek_image_package_load_manifest($zip);
    if (is_wp_error($manifest)) {
        $zip->close();
        spek_image_package_delete_tree($dir);
        wp_send_json_error(['message' => $manifest->get_error_message()], 400);
    }

    $entries = [];
    $seen_product_hash = [];

    foreach ((array) $manifest['rows'] as $row) {
        if (!is_array($row)) {
            $entries[] = [
                'status' => 'error',
                'action' => 'invalid',
                'sku' => '',
                'image_filename' => '',
                'image_relative_path' => '',
                'is_primary' => 0,
                'message' => __('Μη έγκυρη γραμμή manifest.', 'spek-theme'),
            ];
            continue;
        }

        $entry = spek_image_package_analyze_row($row, $zip);

        if (
            ($entry['status'] ?? '') === 'ready'
            && !empty($entry['product_id'])
            && !empty($entry['sha256'])
        ) {
            $dedupe_key = (int) $entry['product_id'] . '|' . (string) $entry['sha256'];
            if (isset($seen_product_hash[$dedupe_key])) {
                $entry['status'] = 'skipped';
                $entry['action'] = 'skip_package_duplicate';
                $entry['message'] = __('Η ίδια εικόνα εμφανίζεται δεύτερη φορά για το ίδιο προϊόν μέσα στο package.', 'spek-theme');
            } else {
                $seen_product_hash[$dedupe_key] = true;
            }
        }

        $entries[] = $entry;
    }

    $zip->close();

    $analysis = [
        'format' => SPEK_MATCHER_PACKAGE_FORMAT,
        'package_name' => $original_name,
        'package_info' => $manifest['package_info'],
        'entries' => $entries,
        'summary' => spek_image_package_summary($entries),
        'created_at' => time(),
    ];

    file_put_contents(
        trailingslashit($dir) . 'analysis.json',
        wp_json_encode($analysis, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        LOCK_EX
    );

    wp_send_json_success([
        'token' => $token,
        'package_name' => $original_name,
        'summary' => $analysis['summary'],
        'entries' => $entries,
    ]);
}
add_action('wp_ajax_spek_image_package_analyze', 'spek_ajax_image_package_analyze');

function spek_image_package_extract_entry(ZipArchive $zip, string $entry, string $dir)
{
    $index = $zip->locateName($entry, ZipArchive::FL_NOCASE);
    if ($index === false) {
        return new WP_Error('missing_image', __('Λείπει εικόνα από το ZIP.', 'spek-theme'));
    }

    $stat = $zip->statIndex((int) $index);
    if (!is_array($stat)) {
        return new WP_Error('missing_image', __('Λείπει εικόνα από το ZIP.', 'spek-theme'));
    }

    $actual_name = (string) ($stat['name'] ?? $entry);
    $work = trailingslashit($dir) . 'work';
    if (!wp_mkdir_p($work)) {
        return new WP_Error('work_dir_failed', __('Δεν δημιουργήθηκε προσωρινός φάκελος εικόνας.', 'spek-theme'));
    }

    $stream = $zip->getStream($actual_name);
    if (!is_resource($stream)) {
        return new WP_Error('image_stream_failed', __('Αποτυχία ανάγνωσης εικόνας από ZIP.', 'spek-theme'));
    }

    $name = wp_basename($actual_name);
    $target = trailingslashit($work) . wp_generate_password(12, false, false) . '--' . sanitize_file_name($name);
    $out = fopen($target, 'wb');

    if (!$out) {
        fclose($stream);
        return new WP_Error('temp_file_failed', __('Αποτυχία δημιουργίας προσωρινού αρχείου.', 'spek-theme'));
    }

    $written = stream_copy_to_stream($stream, $out);
    fclose($stream);
    fclose($out);

    if ($written === false || $written < 1 || !is_file($target)) {
        @unlink($target);
        return new WP_Error('extract_failed', __('Αποτυχία εξαγωγής εικόνας από ZIP.', 'spek-theme'));
    }

    $info = @getimagesize($target);
    if (!$info || empty($info['mime']) || strpos((string) $info['mime'], 'image/') !== 0) {
        @unlink($target);
        return new WP_Error('invalid_image', __('Το ZIP περιέχει αρχείο που δεν αναγνωρίζεται ως εικόνα.', 'spek-theme'));
    }

    return $target;
}

function spek_image_package_import_media(array $entry, ZipArchive $zip, string $dir)
{
    $existing = spek_image_package_find_existing_attachment((string) ($entry['sha256'] ?? ''));
    if ($existing) {
        return $existing;
    }

    $source_path = spek_image_package_extract_entry($zip, (string) $entry['zip_entry'], $dir);
    if (is_wp_error($source_path)) {
        return $source_path;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    if (
        !function_exists('spek_product_image_normalize_file')
        || !function_exists('spek_product_image_insert_normalized_attachment')
    ) {
        @unlink($source_path);
        return new WP_Error(
            'normalizer_missing',
            __('Λείπουν οι SPEK image processing helpers.', 'spek-theme')
        );
    }

    $normalized = spek_product_image_normalize_file(
        $source_path,
        (string) ($entry['image_filename'] ?: wp_basename((string) $entry['zip_entry']))
    );
    @unlink($source_path);

    if (is_wp_error($normalized)) {
        return $normalized;
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

    $attachment_id = (int) $attachment_id;
    update_post_meta($attachment_id, '_spek_source_sha256', sanitize_text_field((string) $entry['sha256']));
    update_post_meta($attachment_id, '_spek_original_filename', sanitize_text_field((string) $entry['image_filename']));
    update_post_meta($attachment_id, '_spek_image_import_sku', sanitize_text_field((string) $entry['sku']));
    update_post_meta($attachment_id, '_spek_image_matcher_package_format', SPEK_MATCHER_PACKAGE_FORMAT);
    update_post_meta($attachment_id, '_spek_image_matcher_relative_path', sanitize_text_field((string) $entry['image_relative_path']));

    if (function_exists('spek_product_image_mark_normalized_attachment')) {
        spek_product_image_mark_normalized_attachment($attachment_id);
    }

    return $attachment_id;
}

function spek_image_package_link_attachment(int $product_id, int $attachment_id, bool $is_primary): string
{
    $action = spek_image_package_plan_action($product_id, $attachment_id, $is_primary);

    if ($action === 'skip_existing') {
        return 'skipped';
    }

    if ($is_primary && !has_post_thumbnail($product_id)) {
        set_post_thumbnail($product_id, $attachment_id);
        return 'featured';
    }

    $gallery = spek_image_package_gallery_ids($product_id);
    $featured_id = (int) get_post_thumbnail_id($product_id);

    if (
        $attachment_id !== $featured_id
        && !in_array($attachment_id, $gallery, true)
    ) {
        $gallery[] = $attachment_id;
        update_post_meta($product_id, 'product_gallery', array_values($gallery));
        return 'gallery';
    }

    return 'skipped';
}

function spek_ajax_image_package_commit_batch(): void
{
    spek_image_package_guard();

    if (function_exists('wp_raise_memory_limit')) {
        wp_raise_memory_limit('image');
    }
    if (function_exists('set_time_limit')) {
        @set_time_limit(120);
    }

    $token = sanitize_text_field(wp_unslash((string) ($_POST['token'] ?? '')));
    $offset = absint($_POST['offset'] ?? 0);
    $dir = spek_image_package_session_dir($token);
    $analysis_path = trailingslashit($dir) . 'analysis.json';
    $zip_path = trailingslashit($dir) . 'package.zip';

    if (!$token || !is_file($analysis_path) || !is_file($zip_path)) {
        wp_send_json_error(['message' => __('Δεν υπάρχει έγκυρο dry run για αυτό το package.', 'spek-theme')], 400);
    }

    $analysis = json_decode((string) file_get_contents($analysis_path), true);
    if (!is_array($analysis)) {
        wp_send_json_error(['message' => __('Το dry run είναι κατεστραμμένο.', 'spek-theme')], 400);
    }

    $ready = array_values(array_filter(
        (array) ($analysis['entries'] ?? []),
        static fn($entry) => ($entry['status'] ?? '') === 'ready'
    ));

    $batch = array_slice($ready, $offset, 1);
    $stats = [
        'new_media' => 0,
        'reused_media' => 0,
        'featured' => 0,
        'gallery' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    if ($batch) {
        $zip = spek_image_package_open($zip_path);
        if (is_wp_error($zip)) {
            wp_send_json_error(['message' => $zip->get_error_message()], 400);
        }

        foreach ($batch as $stored_entry) {
            $product_id = spek_image_package_product_identity((string) ($stored_entry['sku'] ?? ''));
            if (is_wp_error($product_id) || (int) $product_id < 1) {
                $stats['errors'][] = is_wp_error($product_id)
                    ? $product_id->get_error_message()
                    : sprintf(__('SKU %s: το προϊόν δεν υπάρχει πλέον.', 'spek-theme'), (string) ($stored_entry['sku'] ?? ''));
                continue;
            }

            $stored_entry['product_id'] = (int) $product_id;
            $stored_entry['product_title'] = (string) get_the_title((int) $product_id);

            $existing_attachment = spek_image_package_find_existing_attachment((string) ($stored_entry['sha256'] ?? ''));
            $current_action = spek_image_package_plan_action(
                (int) $product_id,
                $existing_attachment,
                !empty($stored_entry['is_primary'])
            );

            if ($current_action === 'skip_existing') {
                $stats['skipped']++;
                continue;
            }

            $attachment_id = $existing_attachment;
            if ($attachment_id > 0) {
                $stats['reused_media']++;
            } else {
                $attachment_id = spek_image_package_import_media($stored_entry, $zip, $dir);
                if (is_wp_error($attachment_id)) {
                    $stats['errors'][] = sprintf(
                        '%s: %s',
                        (string) ($stored_entry['image_filename'] ?? 'image'),
                        $attachment_id->get_error_message()
                    );
                    continue;
                }
                $stats['new_media']++;
            }

            $linked = spek_image_package_link_attachment(
                (int) $product_id,
                (int) $attachment_id,
                !empty($stored_entry['is_primary'])
            );

            if ($linked === 'featured') {
                $stats['featured']++;
            } elseif ($linked === 'gallery') {
                $stats['gallery']++;
            } else {
                $stats['skipped']++;
            }
        }

        $zip->close();
    }

    $next = $offset + count($batch);
    $done = $next >= count($ready);

    if ($done) {
        file_put_contents(
            trailingslashit($dir) . 'completed.txt',
            (string) time(),
            LOCK_EX
        );
    }

    wp_send_json_success([
        'offset' => $next,
        'total' => count($ready),
        'done' => $done,
        'stats' => $stats,
    ]);
}
add_action('wp_ajax_spek_image_package_commit_batch', 'spek_ajax_image_package_commit_batch');

function spek_render_product_image_package_importer_page(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη σελίδα.', 'spek-theme'));
    }

    spek_image_package_cleanup_old_sessions();
    $nonce = wp_create_nonce('spek_image_matcher_package_ajax');
    $max_upload = wp_max_upload_size();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('SPEK Image Matcher → WordPress', 'spek-theme'); ?></h1>

        <p style="max-width:980px">
            <?php esc_html_e(
                'Ανεβάστε το WordPress package ZIP που δημιουργεί το SPEK Image Matcher. Το WordPress δεν ξανακάνει matching: εφαρμόζει μόνο τις ήδη επιβεβαιωμένες αντιστοιχίσεις του manifest.',
                'spek-theme'
            ); ?>
        </p>

        <div class="notice notice-success inline" style="max-width:980px">
            <p><strong><?php esc_html_e('Incremental / ασφαλής λειτουργία:', 'spek-theme'); ?></strong></p>
            <ul style="margin:0 0 0 20px;list-style:disc">
                <li><?php esc_html_e('Δεν αντικαθίσταται ποτέ υπάρχουσα featured image.', 'spek-theme'); ?></li>
                <li><?php esc_html_e('Δεν καθαρίζεται ή αφαιρείται ποτέ υπάρχον gallery.', 'spek-theme'); ?></li>
                <li><?php esc_html_e('Ίδια εικόνα (ίδιο SHA-256) δεν ξαναδημιουργείται στη Media Library.', 'spek-theme'); ?></li>
                <li><?php esc_html_e('Νέα primary εικόνα σε προϊόν που έχει ήδη featured προστίθεται μόνο στο gallery.', 'spek-theme'); ?></li>
                <li><?php esc_html_e('SKU που λείπει ή είναι διπλό καταγράφεται στο dry run και δεν γίνεται guessing.', 'spek-theme'); ?></li>
            </ul>
        </div>

        <div class="card" style="max-width:980px;padding:20px;margin-top:18px">
            <h2 style="margin-top:0"><?php esc_html_e('1. Validate package', 'spek-theme'); ?></h2>
            <input type="file" id="spek_matcher_package_zip" accept="application/zip,.zip">
            <p class="description">
                <?php
                echo esc_html(sprintf(
                    __('Μέγιστο upload που επιτρέπει τώρα ο server: %s', 'spek-theme'),
                    size_format($max_upload)
                ));
                ?>
            </p>
            <p>
                <button type="button" class="button button-primary" id="spek_matcher_package_analyze">
                    <?php esc_html_e('Upload & Dry Run', 'spek-theme'); ?>
                </button>
            </p>
        </div>

        <div id="spek-matcher-package-progress" style="display:none;max-width:980px;margin-top:20px">
            <div style="height:18px;background:#dcdcde;border-radius:4px;overflow:hidden">
                <div id="spek-matcher-package-progress-bar" style="width:0;height:100%;background:#2271b1"></div>
            </div>
            <p id="spek-matcher-package-progress-text"></p>
        </div>

        <div id="spek-matcher-package-results" style="max-width:1180px;margin-top:22px"></div>

        <div id="spek-matcher-package-actions" class="card" style="display:none;max-width:980px;padding:20px;margin-top:18px">
            <h2 style="margin-top:0"><?php esc_html_e('2. Import μόνο νέων εικόνων', 'spek-theme'); ?></h2>
            <p><?php esc_html_e('Η εισαγωγή είναι additive και επανελέγχει κάθε γραμμή πριν γράψει οτιδήποτε.', 'spek-theme'); ?></p>
            <button type="button" class="button button-primary" id="spek_matcher_package_commit">
                <?php esc_html_e('Import New Images', 'spek-theme'); ?>
            </button>
        </div>
    </div>

    <script>
    (function(){
        'use strict';

        const ajaxUrl = window.ajaxurl;
        const nonce = <?php echo wp_json_encode($nonce); ?>;
        const fileInput = document.getElementById('spek_matcher_package_zip');
        const analyzeBtn = document.getElementById('spek_matcher_package_analyze');
        const commitBtn = document.getElementById('spek_matcher_package_commit');
        const results = document.getElementById('spek-matcher-package-results');
        const actions = document.getElementById('spek-matcher-package-actions');
        const progress = document.getElementById('spek-matcher-package-progress');
        const bar = document.getElementById('spek-matcher-package-progress-bar');
        const progressText = document.getElementById('spek-matcher-package-progress-text');
        let token = '';

        function esc(value) {
            const div = document.createElement('div');
            div.textContent = String(value == null ? '' : value);
            return div.innerHTML;
        }

        async function post(data) {
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: data,
                credentials: 'same-origin'
            });

            const raw = await response.text();
            let json;
            try {
                json = JSON.parse(raw);
            } catch (error) {
                throw new Error('Server error (' + response.status + '): ' + raw.replace(/<[^>]+>/g, ' ').slice(0, 400));
            }

            if (!response.ok || !json.success) {
                throw new Error(json && json.data && json.data.message ? json.data.message : 'Import error');
            }

            return json.data;
        }

        function dataFor(action) {
            const data = new FormData();
            data.set('action', action);
            data.set('nonce', nonce);
            if (token) data.set('token', token);
            return data;
        }

        function setProgress(done, total, label) {
            progress.style.display = 'block';
            const percent = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 0;
            bar.style.width = percent + '%';
            progressText.textContent = label + ': ' + done + ' / ' + total;
        }

        function actionLabel(action) {
            const labels = {
                import_featured: 'Νέα εικόνα → Featured',
                import_gallery: 'Νέα εικόνα → Gallery',
                reuse_featured: 'Υπάρχον media → Featured',
                reuse_gallery: 'Υπάρχον media → Gallery',
                skip_existing: 'Ήδη συνδεδεμένη · Skip',
                skip_package_duplicate: 'Duplicate μέσα στο ZIP · Skip',
                skip_missing_product: 'Product not found',
                skip_product_error: 'Product error',
                missing_image: 'Image missing',
                invalid: 'Invalid'
            };
            return labels[action] || action || '—';
        }

        function render(data) {
            const s = data.summary || {};
            let html = '';
            html += '<div class="notice notice-info inline"><p><strong>' + esc(data.package_name) + '</strong>'
                + ' · ' + esc(s.total || 0) + ' manifest rows'
                + ' · <strong>' + esc(s.ready || 0) + ' έτοιμες για import</strong>'
                + ' · ' + esc(s.new_media || 0) + ' νέα media'
                + ' · ' + esc(s.reuse_media || 0) + ' reuse'
                + ' · ' + esc(s.skipped_existing || 0) + ' ήδη υπάρχουσες'
                + ' · ' + esc(s.missing_product || 0) + ' missing SKU'
                + '</p></div>';

            html += '<table class="widefat striped"><thead><tr>'
                + '<th>Image</th><th>SKU</th><th>Product</th><th>Primary</th><th>Dry-run action</th><th>Info</th>'
                + '</tr></thead><tbody>';

            const entries = Array.isArray(data.entries) ? data.entries : [];
            const visible = entries.slice(0, 500);
            visible.forEach(entry => {
                html += '<tr>'
                    + '<td><strong>' + esc(entry.image_filename || entry.image_relative_path) + '</strong></td>'
                    + '<td>' + esc(entry.sku) + '</td>'
                    + '<td>' + esc(entry.product_title || '—') + '</td>'
                    + '<td>' + (entry.is_primary ? 'Ναι' : 'Όχι') + '</td>'
                    + '<td>' + esc(actionLabel(entry.action)) + '</td>'
                    + '<td>' + esc(entry.message || '') + '</td>'
                    + '</tr>';
            });
            html += '</tbody></table>';

            if (entries.length > visible.length) {
                html += '<p class="description">Εμφανίζονται οι πρώτες 500 από ' + esc(entries.length) + ' εγγραφές.</p>';
            }

            results.innerHTML = html;
            actions.style.display = Number(s.ready || 0) > 0 ? 'block' : 'none';
        }

        analyzeBtn.addEventListener('click', async function(){
            const file = fileInput.files && fileInput.files[0];
            if (!file) {
                alert('Επιλέξτε το WordPress package ZIP από το SPEK Image Matcher.');
                return;
            }

            analyzeBtn.disabled = true;
            actions.style.display = 'none';
            results.innerHTML = '';
            progress.style.display = 'block';
            bar.style.width = '15%';
            progressText.textContent = 'Upload & validation…';

            const data = dataFor('spek_image_package_analyze');
            data.set('package_zip', file, file.name);

            try {
                const response = await post(data);
                token = response.token;
                bar.style.width = '100%';
                progressText.textContent = 'Dry run ολοκληρώθηκε.';
                render(response);
            } catch (error) {
                results.innerHTML = '<div class="notice notice-error inline"><p>' + esc(error.message) + '</p></div>';
                progress.style.display = 'none';
            } finally {
                analyzeBtn.disabled = false;
            }
        });

        commitBtn.addEventListener('click', async function(){
            if (!token) return;
            if (!window.confirm('Να εισαχθούν μόνο οι νέες εικόνες; Καμία υπάρχουσα εικόνα δεν θα αντικατασταθεί ή διαγραφεί.')) {
                return;
            }

            commitBtn.disabled = true;
            let offset = 0;
            let total = 0;
            const aggregate = {
                new_media: 0,
                reused_media: 0,
                featured: 0,
                gallery: 0,
                skipped: 0,
                errors: []
            };

            try {
                while (true) {
                    const data = dataFor('spek_image_package_commit_batch');
                    data.set('offset', String(offset));
                    const response = await post(data);
                    offset = Number(response.offset || 0);
                    total = Number(response.total || 0);

                    const stats = response.stats || {};
                    aggregate.new_media += Number(stats.new_media || 0);
                    aggregate.reused_media += Number(stats.reused_media || 0);
                    aggregate.featured += Number(stats.featured || 0);
                    aggregate.gallery += Number(stats.gallery || 0);
                    aggregate.skipped += Number(stats.skipped || 0);
                    aggregate.errors = aggregate.errors.concat(stats.errors || []);

                    setProgress(offset, total, 'Import');
                    if (response.done) break;
                    await new Promise(resolve => setTimeout(resolve, 650));
                }

                results.insertAdjacentHTML('afterbegin',
                    '<div class="notice notice-success inline"><p><strong>Ολοκληρώθηκε.</strong> '
                    + esc(aggregate.new_media) + ' νέα media · '
                    + esc(aggregate.reused_media) + ' επαναχρησιμοποιήθηκαν · '
                    + esc(aggregate.featured) + ' νέα featured · '
                    + esc(aggregate.gallery) + ' νέες gallery · '
                    + esc(aggregate.skipped) + ' skipped · '
                    + esc(aggregate.errors.length) + ' σφάλματα.</p></div>'
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
