<?php
/**
 * Single-record bilingual import primitives for SPEK custom i18n.
 *
 * One canonical WordPress product is kept per SKU. English title/content,
 * translated product fields and taxonomy names are stored on the same record.
 *
 * @package SpekTheme
 */
if (!defined('ABSPATH')) { exit; }

/** Normalize importer mode. */
function spek_import_mode(string $mode): string {
    return $mode === 'full' ? 'full' : 'translations';
}

/** Phase B importer build marker shown in wp-admin diagnostics. */
if (!defined('SPEK_IMPORTER_BUILD')) { define('SPEK_IMPORTER_BUILD', 'Phase B2 · SKU index 2026-09-21'); }

/** Normalize SKU values from CSV and legacy WordPress meta deterministically. */
function spek_import_normalize_sku($value): string {
    $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = str_replace(["\xC2\xA0", "\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D", "\xEF\xBB\xBF"], '', $value);
    $value = preg_replace('/\s+/u', '', trim($value));
    $value = is_string($value) ? $value : '';

    // Excel/CSV exports sometimes turn integer product codes into 40204.0.
    if (preg_match('/^([0-9]+)\.0+$/', $value, $m)) { $value = $m[1]; }

    return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
}

/**
 * Build a raw database SKU index once per request.
 *
 * No WP_Query is used, therefore Polylang/TranslatePress/theme query filters
 * cannot hide products. Several historical SKU keys are accepted, but
 * product_code remains the canonical SPEK key.
 */
function spek_import_product_sku_index(): array {
    static $cache = null;
    if (is_array($cache)) { return $cache; }

    global $wpdb;
    $known_keys = ['product_code', '_product_code', 'sku', '_sku', 'product_sku'];
    $allowed_statuses = ['publish', 'draft', 'pending', 'private', 'future', 'trash'];
    $status_sql = implode(',', array_fill(0, count($allowed_statuses), '%s'));
    $key_sql = implode(',', array_fill(0, count($known_keys), '%s'));

    $sql = "SELECT p.ID, p.post_status, pm.meta_key, pm.meta_value
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
            WHERE p.post_type = 'spek_product'
              AND p.post_status IN ({$status_sql})
              AND pm.meta_key IN ({$key_sql})
            ORDER BY p.ID ASC";
    $params = array_merge($allowed_statuses, $known_keys);
    $rows = (array) $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);

    $index = [];
    $id_status = [];
    $key_counts = array_fill_keys($known_keys, 0);
    $indexed_ids = [];

    foreach ($rows as $row) {
        $id = (int) ($row['ID'] ?? 0);
        $key = (string) ($row['meta_key'] ?? '');
        $normalized = spek_import_normalize_sku($row['meta_value'] ?? '');
        if ($id < 1 || $normalized === '') { continue; }
        $index[$normalized][$id] = true;
        $id_status[$id] = (string) ($row['post_status'] ?? '');
        $indexed_ids[$id] = true;
        if (isset($key_counts[$key])) { $key_counts[$key]++; }
    }

    foreach ($index as $sku => $ids) {
        $index[$sku] = array_values(array_map('intval', array_keys($ids)));
        sort($index[$sku], SORT_NUMERIC);
    }

    $product_count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ({$status_sql})",
        array_merge(['spek_product'], $allowed_statuses)
    ));

    // Diagnostics only: discover any unexpected legacy meta keys that look SKU-related.
    $candidate_rows = (array) $wpdb->get_results(
        "SELECT pm.meta_key, COUNT(*) AS qty
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
         WHERE p.post_type = 'spek_product'
           AND (pm.meta_key LIKE '%sku%' OR pm.meta_key LIKE '%code%')
         GROUP BY pm.meta_key
         ORDER BY qty DESC, pm.meta_key ASC",
        ARRAY_A
    );
    $candidate_keys = [];
    foreach ($candidate_rows as $row) {
        $candidate_keys[(string) $row['meta_key']] = (int) $row['qty'];
    }

    $cache = [
        'index' => $index,
        'id_status' => $id_status,
        'product_count' => $product_count,
        'indexed_product_count' => count($indexed_ids),
        'unique_sku_count' => count($index),
        'key_counts' => $key_counts,
        'candidate_keys' => $candidate_keys,
    ];
    return $cache;
}

/** Read-only importer diagnostics for the admin screen. */
function spek_import_product_sku_diagnostics(): array {
    $data = spek_import_product_sku_index();
    $probe = $data['index'][spek_import_normalize_sku('40204')] ?? [];
    return [
        'build' => defined('SPEK_IMPORTER_BUILD') ? SPEK_IMPORTER_BUILD : 'unknown',
        'product_count' => (int) ($data['product_count'] ?? 0),
        'indexed_product_count' => (int) ($data['indexed_product_count'] ?? 0),
        'unique_sku_count' => (int) ($data['unique_sku_count'] ?? 0),
        'key_counts' => (array) ($data['key_counts'] ?? []),
        'candidate_keys' => (array) ($data['candidate_keys'] ?? []),
        'probe_40204' => array_values(array_map('intval', (array) $probe)),
    ];
}

/** Return one canonical product id for an SKU, or a safe error for ambiguity. */
function spek_import_product_identity(string $sku, string $lang = '') {
    $normalized = spek_import_normalize_sku($sku);
    if ($normalized === '') { return 0; }

    $data = spek_import_product_sku_index();
    $matches = array_values(array_unique(array_map('intval', (array) ($data['index'][$normalized] ?? []))));

    if (count($matches) > 1) {
        return new WP_Error(
            'duplicate_sku',
            sprintf(__('SKU %s: υπάρχουν πολλαπλά προϊόντα. Δεν έγινε καμία αλλαγή.', 'spek-theme'), $sku),
            ['ids' => $matches]
        );
    }

    if ($matches) {
        $status = (string) (($data['id_status'][$matches[0]] ?? '') ?: get_post_status($matches[0]));
        if ($status === 'trash') {
            return new WP_Error(
                'trashed_sku',
                sprintf(__('SKU %s: υπάρχει στον κάδο. Επαναφέρετέ το ή ελέγξτε το πριν την εισαγωγή.', 'spek-theme'), $sku),
                ['ids' => $matches]
            );
        }
        return (int) $matches[0];
    }

    return 0;
}

function spek_import_warning(array &$stats, string $message): void {
    $stats['warning_count'] = (int) ($stats['warning_count'] ?? 0) + 1;
    if (count($stats['warnings'] ?? []) < 100) { $stats['warnings'][] = $message; }
}

/** Find/create a Greek taxonomy term without ever guessing between duplicates. */
function spek_import_term_identity(
    string $name,
    string $taxonomy,
    int $parent,
    string $lang = '',
    bool $dry_run = false,
    bool $allow_create = true
) {
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'name' => $name,
        'parent' => $parent,
        'suppress_filter' => true,
    ]);
    if (is_wp_error($terms)) { return $terms; }

    $matches = array_values(array_filter($terms, static function ($term) use ($name) {
        return $term instanceof WP_Term && $term->name === $name;
    }));

    if (count($matches) > 1) {
        /*
         * Legacy Polylang/import runs may have left exact duplicate terms in the
         * same taxonomy + parent. For the custom i18n catalogue these records
         * represent the same canonical Greek concept, so resolve them
         * deterministically instead of skipping every affected product.
         *
         * Resolution order:
         *  1. A single term whose stored English name matches the catalogue map.
         *  2. A single term with the canonical WordPress slug for the Greek name.
         *  3. If all duplicate records have no conflicting English metadata,
         *     select the oldest (lowest term_id) as the canonical record.
         *
         * We still refuse to guess when duplicates contain conflicting English
         * meanings; that remains an ambiguous_term error.
         */
        $expected_en = function_exists('spek_import_default_taxonomy_translation')
            ? spek_import_default_taxonomy_translation($name, $taxonomy)
            : '';

        if ($expected_en !== '') {
            $english_matches = array_values(array_filter($matches, static function ($term) use ($expected_en) {
                return (string) get_term_meta((int) $term->term_id, '_spek_name_en', true) === $expected_en;
            }));
            if (count($english_matches) === 1) {
                return (int) $english_matches[0]->term_id;
            }
        }

        $canonical_slug = sanitize_title($name);
        $slug_matches = array_values(array_filter($matches, static function ($term) use ($canonical_slug) {
            return (string) $term->slug === $canonical_slug;
        }));
        if (count($slug_matches) === 1) {
            return (int) $slug_matches[0]->term_id;
        }

        $has_conflict = false;
        foreach ($matches as $term) {
            $stored_en = trim((string) get_term_meta((int) $term->term_id, '_spek_name_en', true));
            if ($stored_en === '') { continue; }
            if ($expected_en === '' || $stored_en !== $expected_en) {
                $has_conflict = true;
                break;
            }
        }

        if (!$has_conflict) {
            usort($matches, static function ($a, $b) {
                return ((int) $a->term_id) <=> ((int) $b->term_id);
            });
            return (int) $matches[0]->term_id;
        }

        return new WP_Error('ambiguous_term', sprintf(__('Αμφίσημος όρος %1$s (%2$s).', 'spek-theme'), $name, $taxonomy));
    }
    if ($matches) { return (int) $matches[0]->term_id; }
    if ($dry_run || !$allow_create) { return 0; }

    $result = wp_insert_term($name, $taxonomy, ['parent' => $parent]);
    return is_wp_error($result) ? $result : (int) $result['term_id'];
}

/** Parse multiple terms and optional > hierarchy paths. */
function spek_import_term_paths(string $raw, string $taxonomy): array {
    $pattern = $taxonomy === 'product_category' ? '/\s*[|;,]\s*/u' : '/\s*\|\s*/u';
    $paths = preg_split($pattern, trim(wp_strip_all_tags($raw)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

    return array_map(static function ($path) {
        return array_values(array_filter(array_map(static function ($name) {
            return sanitize_text_field(html_entity_decode(trim($name), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }, explode('>', $path)), 'strlen'));
    }, $paths);
}

/** Curated term names for the 2026 SPEK catalogue when the CSV has no *_en taxonomy columns. */
function spek_import_default_taxonomy_translation(string $name, string $taxonomy): string {
    $maps = [
        'product_category' => [
            'Μηχανισμοί' => 'Flush Mechanisms',
            'Φλοτέρ' => 'Fill Valves',
            'Σιφόνια' => 'Traps',
            'Λάστιχα' => 'Rubber Seals',
            'Καζανάκια' => 'Cisterns',
            'Καλύμματα λεκάνης' => 'Toilet Seats',
            'Μπάνιο' => 'Bathroom',
            'Διάφορα' => 'Miscellaneous',
            'Nemo' => 'Nemo',
            'SPEK' => 'SPEK',
            'Lamaplast' => 'Lamaplast',
        ],
        'product_application' => [
            'WC' => 'WC',
            'Νιπτήρας' => 'Washbasin',
            'Μπάνιο' => 'Bathroom',
            'Κουζίνα' => 'Kitchen',
            'Βρύση' => 'Tap',
            'Πλυντήριο' => 'Washing Machine',
            'Εγκαταστάσεις αερίου' => 'Gas Installations',
            'Ηλιακός θερμοσίφωνας' => 'Solar Water Heater',
            'Μπαλκόνι' => 'Balcony',
            'ταράτσα' => 'Roof / Terrace',
            'Επαγγελματικός χώρος' => 'Commercial Space',
            'Αντικατάσταση' => 'Replacement',
            'Νέα εγκατάσταση' => 'New Installation',
        ],
        'product_material' => [
            'Πλαστικό' => 'Plastic',
            'Μεταλλικό' => 'Metal',
            'Ελαστικό' => 'Rubber',
            'Μικτό' => 'Mixed',
        ],
        'product_series' => [
            'SPEK' => 'SPEK',
            'Nemo' => 'Nemo',
            'Lamaplast' => 'Lamaplast',
        ],
    ];

    return isset($maps[$taxonomy][$name]) ? (string) $maps[$taxonomy][$name] : '';
}

/** Track one unique term translation update in dry-run and real imports. */
function spek_import_track_term_translation(
    int $term_id,
    string $taxonomy,
    string $english_name,
    bool $dry_run,
    array &$stats
): void {
    if ($term_id < 1 || $english_name === '') { return; }

    $current = (string) get_term_meta($term_id, '_spek_name_en', true);
    if ($current === $english_name) { return; }

    $signature = $taxonomy . ':' . $term_id . ':' . md5($english_name);
    if (isset($stats['_term_updates'][$signature])) { return; }

    $stats['_term_updates'][$signature] = 1;
    $stats['taxonomy_updates'] = (int) ($stats['taxonomy_updates'] ?? 0) + 1;

    if (!$dry_run) {
        update_term_meta($term_id, '_spek_name_en', $english_name);
    }
}

/**
 * Translate taxonomy terms on the same Greek term records.
 *
 * In translations-only mode terms are never created or reassigned. In full mode
 * the existing importer behaviour is retained: missing Greek terms may be created
 * and the canonical product receives those Greek terms.
 */
function spek_import_i18n_taxonomy(
    int $post_id,
    string $raw,
    string $raw_en,
    string $taxonomy,
    bool $dry_run,
    array &$stats,
    bool $allow_create = true,
    bool $assign_terms = true
): void {
    $paths = spek_import_term_paths($raw, $taxonomy);
    $english_paths = spek_import_term_paths($raw_en, $taxonomy);
    $explicit_english = trim($raw_en) !== '';

    if ($explicit_english && (count($paths) !== count($english_paths) || array_map('count', $paths) !== array_map('count', $english_paths))) {
        spek_import_warning($stats, sprintf(__('Η δομή %1$s / %1$s_en διαφέρει. Χρησιμοποιούνται μόνο ασφαλείς αντιστοιχίσεις.', 'spek-theme'), $taxonomy));
        $explicit_english = false;
    }

    $term_ids = [];
    foreach ($paths as $i => $levels) {
        $parent = 0;
        foreach ($levels as $j => $name) {
            $id = spek_import_term_identity($name, $taxonomy, $parent, '', $dry_run, $allow_create);
            if (is_wp_error($id)) {
                spek_import_warning($stats, $id->get_error_message());
                $parent = 0;
                break;
            }

            if (!$id) {
                if (!$allow_create) {
                    spek_import_warning($stats, sprintf(__('Δεν βρέθηκε υπάρχων όρος %1$s στο %2$s. Δεν δημιουργήθηκε.', 'spek-theme'), $name, $taxonomy));
                }
                $parent = 0;
                break;
            }

            $parent = (int) $id;
            $english_name = '';
            if ($explicit_english && isset($english_paths[$i][$j])) {
                $english_name = sanitize_text_field((string) $english_paths[$i][$j]);
            }
            if ($english_name === '') {
                $english_name = spek_import_default_taxonomy_translation($name, $taxonomy);
            }
            spek_import_track_term_translation($parent, $taxonomy, $english_name, $dry_run, $stats);
        }
        if ($parent) { $term_ids[] = $parent; }
    }

    if (!$dry_run && $assign_terms && $post_id && $term_ids) {
        $result = wp_set_object_terms($post_id, array_values(array_unique($term_ids)), $taxonomy, false);
        if (is_wp_error($result)) { spek_product_import_add_error($stats, $result->get_error_message()); }
    }
}

/** Extract translated specification values from the structured English HTML description. */
function spek_import_extract_english_specs(string $html): array {
    if (trim($html) === '') { return []; }

    $label_map = [
        'dimensions / connection' => 'product_dimensions',
        'dimensions' => 'product_dimensions',
        'material / construction' => 'product_material',
        'material' => 'product_material',
        'compatibility' => 'product_compatibility',
        'packaging' => 'product_packaging',
        'weight' => 'product_weight',
        'capacity' => 'product_capacity',
        'application' => 'product_application_text',
        'installation type' => 'product_installation_type',
        'color' => 'product_color',
        'colour' => 'product_color',
        'features' => 'product_features',
    ];

    $result = [];
    if (preg_match_all('#<li\b[^>]*>(.*?)</li>#is', $html, $matches)) {
        foreach ($matches[1] as $item_html) {
            $text = trim(html_entity_decode(wp_strip_all_tags((string) $item_html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (!preg_match('/^([^:]{1,60}):\s*(.+)$/u', $text, $parts)) { continue; }
            $label = strtolower(trim((string) $parts[1]));
            if (!isset($label_map[$label])) { continue; }
            $value = sanitize_textarea_field(trim((string) $parts[2]));
            if ($value !== '') { $result[$label_map[$label]] = $value; }
        }
    }

    return $result;
}

/** Safe dictionary translations for short structured values not represented as English bullets. */
function spek_import_translate_structured_value(string $value, string $field): string {
    $value = trim($value);
    if ($value === '') { return ''; }

    $maps = [
        'product_color' => [
            'Χρωμέ' => 'Chrome', 'Λευκό' => 'White', 'Διάφανο' => 'Transparent', 'Μπλε' => 'Blue',
            'Νίκελ' => 'Nickel', 'Γκρι' => 'Grey', 'Κόκκινο' => 'Red', 'Μπρονζέ' => 'Bronze',
            'Κίτρινο' => 'Yellow', 'Μαύρο' => 'Black', 'Μπουτόν: Λευκό' => 'Button: White',
            'Μπουτόν: Χρωμέ' => 'Button: Chrome', 'Σχαράκι: Μαύρο' => 'Grille: Black',
            'Μαύρο / Μπλε' => 'Black / Blue', 'Χρωμέ / Μαύρο' => 'Chrome / Black',
        ],
        'product_application_text' => [
            'WC' => 'WC', 'Μπάνιο' => 'Bathroom', 'Πλυντήριο' => 'Washing Machine', 'Βρύση' => 'Tap',
            'Κουζίνα' => 'Kitchen', 'Νιπτήρας' => 'Washbasin', 'Εγκαταστάσεις αερίου' => 'Gas Installations',
            'Μπαλκόνι / ταράτσα' => 'Balcony / Roof', 'Ηλιακός θερμοσίφωνας' => 'Solar Water Heater',
        ],
        'product_installation_type' => [
            'Πλαϊνή παροχή' => 'Side inlet',
            'Πλαϊνή ή πυθμένος παροχή' => 'Side or bottom inlet',
            'Πυθμένος παροχή' => 'Bottom inlet',
            'Εντοιχιζόμενη' => 'Concealed',
            'Επιτοίχια' => 'Wall-mounted',
            'Επικαθήμενη' => 'Countertop',
            'Τοίχου ή οροφής' => 'Wall or ceiling mounted',
            'Γωνιακή' => 'Corner installation',
        ],
        'product_material' => [
            'Ελαστικό' => 'Rubber', 'Πλαστικό' => 'Plastic', 'Μέταλλο' => 'Metal', 'Μεταλλικό' => 'Metal',
            'Ανοξείδωτος χάλυβας' => 'Stainless steel', 'Χάλυβας' => 'Steel', 'Ορείχαλκος' => 'Brass',
            'Πολυουρεθάνη' => 'Polyurethane', 'Σιλικόνη' => 'Silicone', 'Αλουμίνιο' => 'Aluminium',
            'Μέταλλο / πλαστικό' => 'Metal / plastic', 'Πλαστικά παξιμάδια' => 'Plastic nuts',
            'Μεταλλικό παξιμάδι' => 'Metal nut', 'Πλαστικό παξιμάδι' => 'Plastic nut',
            'Πλαστική βίδα' => 'Plastic screw', 'Μεταλλική βίδα' => 'Metal screw',
            'Πλαστικός πείρος' => 'Plastic pin', 'Μεταλλικός πείρος' => 'Metal pin',
            'Πλαστική έξοδος' => 'Plastic outlet', 'Μεταλλική έξοδος' => 'Metal outlet',
            'Ανοξίδωτη έδρα' => 'Stainless steel seat', 'Ανοξίδωτο κάλυμμα' => 'Stainless steel cover',
            'Γαλβανισμένη λαμαρίνα' => 'Galvanised sheet steel', 'Πλαστική ουρά' => 'Plastic tailpiece',
            'ΡΗΤΙΝΗ' => 'Resin', 'Φίμπερ' => 'Fibre',
        ],
    ];

    if (isset($maps[$field][$value])) { return (string) $maps[$field][$value]; }

    // Brand/material tokens and numeric/Latin values are already language-neutral.
    if (!preg_match('/[Α-Ωα-ωΆΈΉΊΌΎΏάέήίόύώ]/u', $value)) { return $value; }

    return '';
}

/** Build all English values supplied or safely derivable from one CSV row. */
function spek_import_build_english_values(array $data): array {
    $values = [];

    if (isset($data['title_en']) && trim((string) $data['title_en']) !== '') {
        $values['title'] = sanitize_text_field((string) $data['title_en']);
    }
    if (isset($data['content_en']) && trim((string) $data['content_en']) !== '') {
        $values['content'] = wp_kses_post((string) $data['content_en']);
    }
    if (isset($data['product_short_description_en']) && trim((string) $data['product_short_description_en']) !== '') {
        $values['product_short_description'] = sanitize_textarea_field((string) $data['product_short_description_en']);
    }

    $extracted = spek_import_extract_english_specs((string) ($data['content_en'] ?? ''));

    foreach (spek_product_translated_meta_keys() as $field) {
        if ($field === 'product_short_description') { continue; }
        $column = $field . '_en';
        $translated = isset($data[$column]) ? trim((string) $data[$column]) : '';
        if ($translated === '' && isset($extracted[$field])) { $translated = (string) $extracted[$field]; }
        if ($translated === '' && isset($data[$field])) {
            $translated = spek_import_translate_structured_value((string) $data[$field], $field);
        }
        if ($translated !== '') { $values[$field] = sanitize_textarea_field($translated); }
    }

    return $values;
}

/** Compare/write English values. Blanks never delete existing translations. */
function spek_import_apply_english_values(int $post_id, array $values, bool $dry_run, array &$stats): bool {
    $changed = false;

    foreach ($values as $field => $value) {
        if ($value === '') { continue; }
        $key = spek_i18n_post_meta_key((string) $field);
        $current = (string) get_metadata_raw('post', $post_id, $key, true);
        $normalized_current = $field === 'content' ? trim(wp_kses_post($current)) : trim($current);
        $normalized_new = $field === 'content' ? trim(wp_kses_post((string) $value)) : trim((string) $value);
        if ($normalized_current === $normalized_new) { continue; }
        $changed = true;
        if (!$dry_run) {
            update_post_meta($post_id, $key, wp_slash((string) $value));
        }
    }

    return $changed;
}

/** Translate existing product + term records only; no Greek/product structure writes. */
function spek_import_translation_only_row(array $data, int $row_number, bool $dry_run, array &$stats): void {
    $sku = sanitize_text_field(trim((string) ($data['sku'] ?? '')));
    if ($sku === '') {
        $stats['skipped']++;
        spek_product_import_add_error($stats, sprintf(__('Γραμμή %d: λείπει SKU.', 'spek-theme'), $row_number));
        return;
    }

    $product_id = spek_import_product_identity($sku);
    if (is_wp_error($product_id)) {
        $stats['skipped']++;
        $stats['ambiguous'] = (int) ($stats['ambiguous'] ?? 0) + 1;
        spek_import_warning($stats, $product_id->get_error_message());
        return;
    }
    if (!$product_id) {
        $stats['skipped']++;
        $stats['missing_product'] = (int) ($stats['missing_product'] ?? 0) + 1;
        if (count($stats['missing_skus'] ?? []) < 100) { $stats['missing_skus'][] = $sku; }
        return;
    }

    $stats['matched'] = (int) ($stats['matched'] ?? 0) + 1;
    $english = spek_import_build_english_values($data);
    if (empty($english['title'])) {
        $stats['skipped']++;
        $stats['missing_english'] = (int) ($stats['missing_english'] ?? 0) + 1;
        spek_import_warning($stats, sprintf(__('SKU %s: λείπει title_en. Δεν εκτέθηκε στην αγγλική έκδοση.', 'spek-theme'), $sku));
        return;
    }

    $changed = spek_import_apply_english_values((int) $product_id, $english, $dry_run, $stats);

    $taxonomies = [
        'categories' => 'product_category',
        'product_application_terms' => 'product_application',
        'product_material_terms' => 'product_material',
        'product_series_terms' => 'product_series',
    ];
    foreach ($taxonomies as $column => $taxonomy) {
        if (!empty($data[$column])) {
            spek_import_i18n_taxonomy(
                (int) $product_id,
                (string) $data[$column],
                (string) ($data[$column . '_en'] ?? ''),
                $taxonomy,
                $dry_run,
                $stats,
                false,
                false
            );
        }
    }

    if ($changed) {
        $stats['translated'] = (int) ($stats['translated'] ?? 0) + 1;
        $stats['updated'] = (int) ($stats['updated'] ?? 0) + 1;
        if (!$dry_run) {
            update_post_meta((int) $product_id, '_spek_i18n_last_imported_at', current_time('mysql'));
        }
    } else {
        $stats['unchanged'] = (int) ($stats['unchanged'] ?? 0) + 1;
    }
}

/** Full canonical product sync plus English values. */
function spek_import_full_row(array $data, int $row_number, bool $dry_run, array &$stats): void {
    $sku = sanitize_text_field(trim((string) ($data['sku'] ?? '')));
    $title = trim((string) ($data['title'] ?? ''));

    if ($sku === '' || $title === '') {
        $stats['skipped']++;
        spek_product_import_add_error($stats, sprintf(__('Γραμμή %d: λείπει Title ή SKU.', 'spek-theme'), $row_number));
        return;
    }

    $product_id = spek_import_product_identity($sku);
    if (is_wp_error($product_id)) {
        $stats['skipped']++;
        $stats['ambiguous'] = (int) ($stats['ambiguous'] ?? 0) + 1;
        spek_import_warning($stats, $product_id->get_error_message());
        return;
    }

    if ($product_id) { $stats['matched'] = (int) ($stats['matched'] ?? 0) + 1; }

    if ($dry_run) {
        $stats[$product_id ? 'updated' : 'created']++;
        if (!empty($data['title_en'])) { $stats['translated'] = (int) ($stats['translated'] ?? 0) + 1; }

        // Dry-run taxonomy translation preview against existing terms only. Newly
        // created terms are naturally counted during the real full sync.
        $taxonomies = [
            'categories' => 'product_category',
            'product_application_terms' => 'product_application',
            'product_material_terms' => 'product_material',
            'product_series_terms' => 'product_series',
        ];
        foreach ($taxonomies as $column => $taxonomy) {
            if (!empty($data[$column])) {
                spek_import_i18n_taxonomy(0, (string) $data[$column], (string) ($data[$column . '_en'] ?? ''), $taxonomy, true, $stats, false, false);
            }
        }
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

    $english = spek_import_build_english_values($data);
    if (!empty($english['title'])) {
        if (spek_import_apply_english_values($product_id, $english, false, $stats)) {
            $stats['translated'] = (int) ($stats['translated'] ?? 0) + 1;
        }
    } else {
        $stats['missing_english'] = (int) ($stats['missing_english'] ?? 0) + 1;
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
                $stats,
                true,
                true
            );
        }
    }

    update_post_meta($product_id, '_spek_i18n_last_imported_at', current_time('mysql'));
}

/** Main row dispatcher. */
function spek_import_multilingual_row(array $data, int $row_number, bool $dry_run, array &$stats): void {
    $action = strtolower(trim((string) ($data['import_action'] ?? 'import')));
    $review = strtolower(trim((string) ($data['review_status'] ?? 'ready')));

    if ($action !== 'import' || $review !== 'ready') {
        $stats['skipped']++;
        $stats['not_ready'] = (int) ($stats['not_ready'] ?? 0) + 1;
        return;
    }

    $mode = spek_import_mode((string) ($stats['mode'] ?? 'translations'));
    if ($mode === 'full') {
        spek_import_full_row($data, $row_number, $dry_run, $stats);
        return;
    }

    spek_import_translation_only_row($data, $row_number, $dry_run, $stats);
}
