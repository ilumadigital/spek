<?php
/**
 * Single-record bilingual import primitives for SPEK custom i18n.
 *
 * One canonical Greek WordPress product is kept per SKU. English title/content,
 * translated product fields and taxonomy names are stored on the same record.
 *
 * @package SpekTheme
 */
if (!defined('ABSPATH')) { exit; }

function spek_import_product_identity(string $sku, string $lang = '') {
    $ids = get_posts([
        'post_type' => 'spek_product',
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
        'posts_per_page' => -1,
        'fields' => 'ids',
        'suppress_filters' => false,
        'meta_query' => [['key' => 'product_code', 'value' => $sku, 'compare' => '=']],
    ]);
    $matches = [];
    foreach ($ids as $id) {
        if ((string) get_post_meta($id, 'product_code', true) === $sku) { $matches[] = (int) $id; }
    }
    if (count($matches) > 1) {
        return new WP_Error('duplicate_sku', sprintf(__('SKU %s: υπάρχουν πολλαπλά προϊόντα. Απαιτείται χειροκίνητος έλεγχος.', 'spek-theme'), $sku));
    }
    if ($matches && get_post_status($matches[0]) === 'trash') {
        return new WP_Error('trashed_sku', sprintf(__('SKU %s: υπάρχει στον κάδο. Επαναφέρετέ το ή ελέγξτε το πριν την εισαγωγή.', 'spek-theme'), $sku));
    }
    return $matches ? $matches[0] : 0;
}

function spek_import_warning(array &$stats, string $message): void {
    $stats['warning_count'] = (int) ($stats['warning_count'] ?? 0) + 1;
    if (count($stats['warnings'] ?? []) < 100) { $stats['warnings'][] = $message; }
}

function spek_import_term_identity(string $name, string $taxonomy, int $parent, string $lang = '', bool $dry_run = false) {
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false, 'name' => $name, 'parent' => $parent]);
    if (is_wp_error($terms)) { return $terms; }
    $matches = array_values(array_filter($terms, static function ($term) use ($name) { return $term->name === $name; }));
    if (count($matches) > 1) { return new WP_Error('ambiguous_term', sprintf(__('Αμφίσημος όρος: %s', 'spek-theme'), $name)); }
    if ($matches) { return (int) $matches[0]->term_id; }
    if ($dry_run) { return 0; }
    $result = wp_insert_term($name, $taxonomy, ['parent' => $parent]);
    return is_wp_error($result) ? $result : (int) $result['term_id'];
}

function spek_import_term_paths(string $raw, string $taxonomy): array {
    $pattern = $taxonomy === 'product_category' ? '/\s*[|;,]\s*/u' : '/\s*\|\s*/u';
    $paths = preg_split($pattern, trim(wp_strip_all_tags($raw)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    return array_map(static function ($path) {
        return array_values(array_filter(array_map(static function ($name) {
            return sanitize_text_field(html_entity_decode(trim($name), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }, explode('>', $path)), 'strlen'));
    }, $paths);
}

/** Create/assign Greek terms and write English names into term meta by hierarchy position. */
function spek_import_i18n_taxonomy(int $post_id, string $raw, string $raw_en, string $taxonomy, bool $dry_run, array &$stats): void {
    $paths = spek_import_term_paths($raw, $taxonomy);
    $english_paths = spek_import_term_paths($raw_en, $taxonomy);
    $has_english = trim($raw_en) !== '';
    if ($has_english && (count($paths) !== count($english_paths) || array_map('count', $paths) !== array_map('count', $english_paths))) {
        spek_import_warning($stats, sprintf(__('Η δομή %1$s / %1$s_en διαφέρει. Οι αγγλικές ονομασίες όρων δεν ενημερώθηκαν.', 'spek-theme'), $taxonomy));
        $has_english = false;
    }

    $term_ids = [];
    foreach ($paths as $i => $levels) {
        $parent = 0;
        foreach ($levels as $j => $name) {
            $id = spek_import_term_identity($name, $taxonomy, $parent, '', $dry_run);
            if (is_wp_error($id)) {
                spek_product_import_add_error($stats, $id->get_error_message());
                $parent = 0;
                break;
            }
            if ($dry_run && !$id) { break; }
            $parent = (int) $id;
            if (!$dry_run && $has_english && isset($english_paths[$i][$j])) {
                $english_name = sanitize_text_field((string) $english_paths[$i][$j]);
                if ($english_name !== '') { update_term_meta($parent, '_spek_name_en', $english_name); }
            }
        }
        if ($parent) { $term_ids[] = $parent; }
    }

    if (!$dry_run && $post_id && $term_ids) {
        $result = wp_set_object_terms($post_id, array_values(array_unique($term_ids)), $taxonomy, false);
        if (is_wp_error($result)) { spek_product_import_add_error($stats, $result->get_error_message()); }
    }
}

function spek_import_multilingual_row(array $data, int $row_number, bool $dry_run, array &$stats): void {
    $sku = sanitize_text_field(trim((string) ($data['sku'] ?? '')));
    $title = trim((string) ($data['title'] ?? ''));
    $action = strtolower(trim((string) ($data['import_action'] ?? 'import')));
    $review = strtolower(trim((string) ($data['review_status'] ?? 'ready')));

    if ($action !== 'import' || $review !== 'ready') { $stats['skipped']++; return; }
    if ($sku === '' || $title === '') {
        $stats['skipped']++;
        spek_product_import_add_error($stats, sprintf(__('Γραμμή %d: λείπει Title ή SKU.', 'spek-theme'), $row_number));
        return;
    }

    $product_id = spek_import_product_identity($sku);
    if (is_wp_error($product_id)) {
        $stats['skipped']++;
        spek_product_import_add_error($stats, $product_id->get_error_message());
        return;
    }

    if ($dry_run) {
        $stats[$product_id ? 'updated' : 'created']++;
        return;
    }

    $postarr = ['post_type' => 'spek_product', 'post_title' => wp_strip_all_tags($title)];
    if (!empty($data['content'])) { $postarr['post_content'] = wp_kses_post($data['content']); }
    if ($product_id) { $postarr['ID'] = $product_id; } else { $postarr['post_status'] = 'publish'; }

    $result = isset($postarr['ID']) ? wp_update_post(wp_slash($postarr), true) : wp_insert_post(wp_slash($postarr), true);
    if (is_wp_error($result)) { spek_product_import_add_error($stats, $result->get_error_message()); return; }

    $stats[$product_id ? 'updated' : 'created']++;
    $product_id = (int) $result;
    update_post_meta($product_id, 'product_code', wp_slash($sku));

    foreach (array_values(array_unique(array_merge(spek_product_translated_meta_keys(), ['product_dimensions', 'product_weight', 'product_capacity']))) as $key) {
        if (isset($data[$key]) && trim((string) $data[$key]) !== '') {
            update_post_meta($product_id, $key, wp_slash(sanitize_textarea_field($data[$key])));
        }
    }

    /** Store supplied English values directly in the SPEK i18n fields. */
    if (isset($data['title_en']) && trim((string) $data['title_en']) !== '') {
        update_post_meta($product_id, spek_i18n_post_meta_key('title'), sanitize_text_field($data['title_en']));
    }
    if (isset($data['content_en']) && trim((string) $data['content_en']) !== '') {
        update_post_meta($product_id, spek_i18n_post_meta_key('content'), wp_slash(wp_kses_post($data['content_en'])));
    }
    foreach (spek_i18n_translatable_post_meta_keys() as $key) {
        $column = $key . '_en';
        if (isset($data[$column]) && trim((string) $data[$column]) !== '') {
            update_post_meta($product_id, spek_i18n_post_meta_key($key), wp_slash(sanitize_textarea_field($data[$column])));
        }
    }

    foreach (['catalogue_pages' => '_spek_catalogue_pages', 'source_excel_rows' => '_spek_source_excel_rows', 'review_status' => '_spek_review_status', 'review_notes' => '_spek_review_notes', 'id' => '_spek_legacy_import_id'] as $column => $key) {
        if (isset($data[$column]) && trim((string) $data[$column]) !== '') {
            update_post_meta($product_id, $key, wp_slash(sanitize_textarea_field($data[$column])));
        }
    }
    foreach (['product_datasheet', 'product_catalogue_pdf', 'product_installation_video'] as $key) {
        if (!empty($data[$key])) { update_post_meta($product_id, $key, esc_url_raw($data[$key])); }
    }
    if (!empty($data['image_url'])) {
        $image = spek_import_product_image($product_id, trim($data['image_url']), sanitize_text_field($data['image_alt'] ?? ''));
        if (is_wp_error($image)) { spek_product_import_add_error($stats, sprintf('SKU %s: %s', $sku, $image->get_error_message())); }
    }

    $taxonomies = [
        'categories' => 'product_category',
        'product_application_terms' => 'product_application',
        'product_material_terms' => 'product_material',
        'product_series_terms' => 'product_series',
    ];
    foreach ($taxonomies as $column => $taxonomy) {
        if (!empty($data[$column])) {
            spek_import_i18n_taxonomy(
                $product_id,
                (string) $data[$column],
                (string) ($data[$column . '_en'] ?? ''),
                $taxonomy,
                false,
                $stats
            );
        }
    }
}
