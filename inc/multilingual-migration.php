<?php
/**
 * Conservative Polylang legacy-language migration and diagnostics.
 *
 * Assigns `el` only to unassigned legacy products/terms after an explicit dry-run.
 * Never creates translations, changes slugs, deletes content, or modifies meta/attachments.
 *
 * @package SpekTheme
 */
if (!defined('ABSPATH')) { exit; }

function spek_ml_migration_taxonomies(): array {
    return ['product_category', 'product_application', 'product_material', 'product_series'];
}

function spek_ml_has_translation_pair(string $kind, int $id): bool {
    $translations = [];
    if ($kind === 'post' && function_exists('pll_get_post_translations')) {
        $translations = (array) pll_get_post_translations($id);
    } elseif ($kind === 'term' && function_exists('pll_get_term_translations')) {
        $translations = (array) pll_get_term_translations($id);
    }
    $ids = array_values(array_unique(array_filter(array_map('intval', $translations))));
    foreach ($ids as $translation_id) {
        if ($translation_id !== $id) { return true; }
    }
    return count($ids) > 1;
}

function spek_ml_all_product_ids(): array {
    return array_map('intval', get_posts([
        'post_type' => 'spek_product',
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
        'suppress_filters' => true,
    ]));
}

function spek_ml_duplicate_sku_ids(array $ids): array {
    $by_sku = [];
    foreach ($ids as $id) {
        $sku = trim((string) get_post_meta($id, 'product_code', true));
        if ($sku !== '') { $by_sku[$sku][] = (int) $id; }
    }
    $duplicates = [];
    foreach ($by_sku as $sku => $sku_ids) {
        if (count($sku_ids) > 1) {
            foreach ($sku_ids as $id) { $duplicates[$id] = $sku; }
        }
    }
    return $duplicates;
}

function spek_ml_scan_products(): array {
    $ids = spek_ml_all_product_ids();
    $duplicate_skus = spek_ml_duplicate_sku_ids($ids);
    $report = [
        'total' => count($ids), 'candidate' => [], 'el' => [], 'en' => [],
        'pair' => [], 'ambiguous' => [], 'other_language' => [],
    ];
    foreach ($ids as $id) {
        $lang = function_exists('pll_get_post_language') ? (string) pll_get_post_language($id, 'slug') : '';
        if (spek_ml_has_translation_pair('post', $id)) { $report['pair'][] = $id; continue; }
        if ($lang === 'el') { $report['el'][] = $id; continue; }
        if ($lang === 'en') { $report['en'][] = $id; continue; }
        if ($lang !== '') { $report['other_language'][] = $id; continue; }
        if (isset($duplicate_skus[$id])) { $report['ambiguous'][] = $id; continue; }
        $report['candidate'][] = $id;
    }
    return $report;
}

function spek_ml_term_parent_conflict(WP_Term $term): bool {
    if (!$term->parent) { return false; }
    $parent_lang = function_exists('pll_get_term_language') ? (string) pll_get_term_language((int) $term->parent, 'slug') : '';
    if ($parent_lang !== '' && $parent_lang !== 'el') { return true; }
    return spek_ml_has_translation_pair('term', (int) $term->parent) && $parent_lang !== 'el';
}

function spek_ml_scan_terms(): array {
    $all = [];
    foreach (spek_ml_migration_taxonomies() as $taxonomy) {
        $report = ['total' => 0, 'candidate' => [], 'el' => [], 'en' => [], 'pair' => [], 'ambiguous' => [], 'other_language' => []];
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false, 'lang' => '']);
        if (is_wp_error($terms)) { $report['error'] = $terms->get_error_message(); $all[$taxonomy] = $report; continue; }
        $report['total'] = count($terms);
        foreach ($terms as $term) {
            $id = (int) $term->term_id;
            $lang = function_exists('pll_get_term_language') ? (string) pll_get_term_language($id, 'slug') : '';
            if (spek_ml_has_translation_pair('term', $id)) { $report['pair'][] = $id; continue; }
            if ($lang === 'el') { $report['el'][] = $id; continue; }
            if ($lang === 'en') { $report['en'][] = $id; continue; }
            if ($lang !== '') { $report['other_language'][] = $id; continue; }
            if (spek_ml_term_parent_conflict($term)) { $report['ambiguous'][] = $id; continue; }
            $report['candidate'][] = $id;
        }
        usort($report['candidate'], static function ($a, $b) use ($taxonomy) {
            $da = count(get_ancestors($a, $taxonomy, 'taxonomy'));
            $db = count(get_ancestors($b, $taxonomy, 'taxonomy'));
            return $da === $db ? $a <=> $b : $da <=> $db;
        });
        $all[$taxonomy] = $report;
    }
    return $all;
}

function spek_ml_scan(): array {
    $products = spek_ml_scan_products();
    $terms = spek_ml_scan_terms();
    $candidate_terms = 0;
    foreach ($terms as $report) { $candidate_terms += count($report['candidate'] ?? []); }
    $languages = function_exists('pll_languages_list') ? (array) pll_languages_list(['fields' => 'slug']) : [];
    $data = [
        'polylang' => function_exists('pll_set_post_language') && function_exists('pll_set_term_language'),
        'languages' => $languages,
        'greek_ready' => in_array('el', $languages, true),
        'products' => $products,
        'terms' => $terms,
        'candidate_products' => count($products['candidate']),
        'candidate_terms' => $candidate_terms,
    ];
    $data['signature'] = hash('sha256', wp_json_encode([
        'languages' => $languages,
        'products' => $products,
        'terms' => $terms,
    ]));
    return $data;
}

function spek_multilingual_migration_has_pending(): bool {
    if (!function_exists('pll_get_post_language')) { return false; }
    $scan = spek_ml_scan();
    return !empty($scan['candidate_products']) || !empty($scan['candidate_terms']);
}

function spek_ml_dry_run_key(): string { return 'spek_ml_dry_run_' . get_current_user_id(); }
function spek_ml_store_approved_scan(array $scan): void {
    set_transient(spek_ml_dry_run_key(), ['signature' => $scan['signature'], 'approved_at' => time()], 30 * MINUTE_IN_SECONDS);
}
function spek_ml_scan_is_approved(array $scan): bool {
    $saved = get_transient(spek_ml_dry_run_key());
    return is_array($saved) && !empty($saved['signature']) && hash_equals((string) $saved['signature'], (string) $scan['signature']);
}

function spek_ml_acquire_lock() {
    $key = 'spek_ml_migration_lock';
    $existing = get_option($key);
    if ($existing) {
        $decoded = json_decode((string) $existing, true);
        if (is_array($decoded) && !empty($decoded['time']) && time() - (int) $decoded['time'] > 300) { delete_option($key); }
    }
    $token = wp_generate_password(24, false, false);
    $value = wp_json_encode(['token' => $token, 'time' => time(), 'user' => get_current_user_id()]);
    return add_option($key, $value, '', 'no') ? $token : false;
}
function spek_ml_release_lock(string $token): void {
    $key = 'spek_ml_migration_lock';
    $decoded = json_decode((string) get_option($key), true);
    if (is_array($decoded) && isset($decoded['token']) && hash_equals((string) $decoded['token'], $token)) { delete_option($key); }
}

function spek_ml_post_still_safe(int $id): bool {
    if (get_post_type($id) !== 'spek_product') { return false; }
    if ((string) pll_get_post_language($id, 'slug') !== '' || spek_ml_has_translation_pair('post', $id)) { return false; }
    $sku = trim((string) get_post_meta($id, 'product_code', true));
    if ($sku !== '') {
        $same = get_posts(['post_type' => 'spek_product', 'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'], 'fields' => 'ids', 'posts_per_page' => 2,
            'suppress_filters' => true, 'meta_query' => [['key' => 'product_code', 'value' => $sku, 'compare' => '=']]]);
        if (count($same) > 1) { return false; }
    }
    return true;
}
function spek_ml_term_still_safe(int $id, string $taxonomy): bool {
    if (!in_array($taxonomy, spek_ml_migration_taxonomies(), true)) { return false; }
    $term = get_term($id, $taxonomy);
    return $term && !is_wp_error($term) && (string) pll_get_term_language($id, 'slug') === ''
        && !spek_ml_has_translation_pair('term', $id) && !spek_ml_term_parent_conflict($term);
}

function spek_ml_execute_batch(array $scan, int $limit = 50): array {
    $result = ['posts' => 0, 'terms' => 0, 'skipped' => []];
    $remaining = max(1, min(200, $limit));
    foreach (array_slice($scan['products']['candidate'], 0, $remaining) as $id) {
        if (!spek_ml_post_still_safe((int) $id)) { $result['skipped'][] = 'post:' . (int) $id; continue; }
        pll_set_post_language((int) $id, 'el'); $result['posts']++; $remaining--;
        if ($remaining < 1) { break; }
    }
    if ($remaining > 0) {
        foreach (spek_ml_migration_taxonomies() as $taxonomy) {
            foreach (array_slice($scan['terms'][$taxonomy]['candidate'] ?? [], 0, $remaining) as $id) {
                if (!spek_ml_term_still_safe((int) $id, $taxonomy)) { $result['skipped'][] = $taxonomy . ':' . (int) $id; continue; }
                pll_set_term_language((int) $id, 'el'); $result['terms']++; $remaining--;
                if ($remaining < 1) { break 2; }
            }
        }
    }
    return $result;
}

function spek_ml_home_diagnostics(): array {
    $front_id = (int) get_option('page_on_front');
    $translations = ($front_id && function_exists('pll_get_post_translations')) ? (array) pll_get_post_translations($front_id) : [];
    return [
        'show_on_front' => (string) get_option('show_on_front'),
        'front_id' => $front_id,
        'front_title' => $front_id ? get_the_title($front_id) : '',
        'front_lang' => $front_id && function_exists('pll_get_post_language') ? (string) pll_get_post_language($front_id, 'slug') : '',
        'translations' => $translations,
        'el_home' => function_exists('pll_home_url') ? (string) pll_home_url('el') : home_url('/'),
        'en_home' => function_exists('pll_home_url') ? (string) pll_home_url('en') : '',
    ];
}

function spek_ml_url_diagnostics(): array {
    $options = (array) get_option('polylang', []);
    $keys = ['force_lang', 'hide_default', 'rewrite', 'redirect_lang', 'browser'];
    $out = [];
    foreach ($keys as $key) { $out[$key] = array_key_exists($key, $options) ? $options[$key] : null; }
    return $out;
}

function spek_ml_menu_diagnostics(): array {
    $locations = (array) get_theme_mod('nav_menu_locations', []);
    $registered = (array) get_registered_nav_menus();
    $rows = [];

    foreach ($registered as $location => $label) {
        $base = (string) $location;
        $language = '';

        // Polylang classic menus expose translated locations as base___lang.
        if (strpos($base, '___') !== false) {
            [$base_location, $suffix] = array_pad(explode('___', $base, 2), 2, '');
            $base = (string) $base_location;
            $language = (string) $suffix;
        }

        $menu_id = isset($locations[$location]) ? (int) $locations[$location] : 0;
        $menu = $menu_id ? wp_get_nav_menu_object($menu_id) : false;
        $rows[] = [
            'location' => (string) $location,
            'base' => $base,
            'label' => (string) $label,
            'menu_id' => $menu_id,
            'menu_name' => $menu ? (string) $menu->name : '',
            'language' => $language !== '' ? $language : 'default',
        ];
    }
    return $rows;
}

function spek_ml_ambiguous_product_details(array $ids): array {
    $rows = [];
    foreach (array_map('intval', $ids) as $id) {
        if ($id < 1) { continue; }
        $sku = trim((string) get_post_meta($id, 'product_code', true));
        $rows[] = [
            'id' => $id,
            'title' => (string) get_the_title($id),
            'sku' => $sku,
            'status' => (string) get_post_status($id),
            'reason' => $sku !== '' ? 'duplicate SKU / product_code' : 'ambiguous legacy state',
        ];
    }
    return $rows;
}

function spek_ml_admin_menu(): void {
    add_submenu_page('edit.php?post_type=spek_product', __('Polylang Migration', 'spek-theme'), __('Polylang Migration', 'spek-theme'),
        'manage_options', 'spek-polylang-migration', 'spek_ml_render_admin_page');
}
add_action('admin_menu', 'spek_ml_admin_menu');

function spek_ml_handle_admin_action(): void {
    if (!is_admin() || !current_user_can('manage_options')) { return; }
    if (empty($_POST['spek_ml_action'])) { return; }
    check_admin_referer('spek_ml_migration_action', 'spek_ml_nonce');
    $action = sanitize_key(wp_unslash($_POST['spek_ml_action']));
    $scan = spek_ml_scan();
    if ($action === 'dry_run') {
        spek_ml_store_approved_scan($scan);
        add_settings_error('spek_ml', 'dry_run_ok', sprintf(__('Dry-run εγκρίθηκε: %1$d προϊόντα και %2$d terms είναι ασφαλείς υποψήφιοι για `el`.', 'spek-theme'), $scan['candidate_products'], $scan['candidate_terms']), 'success');
        return;
    }
    if ($action !== 'execute_batch') { return; }
    if (!$scan['polylang'] || !$scan['greek_ready']) { add_settings_error('spek_ml', 'no_polylang', __('Το Polylang ή η γλώσσα `el` δεν είναι διαθέσιμα. Δεν έγινε καμία αλλαγή.', 'spek-theme'), 'error'); return; }
    if (!spek_ml_scan_is_approved($scan)) { add_settings_error('spek_ml', 'stale', __('Η κατάσταση άλλαξε ή δεν έχει εγκριθεί dry-run. Εκτέλεσε ξανά dry-run πριν από write batch.', 'spek-theme'), 'error'); return; }
    $lock = spek_ml_acquire_lock();
    if (!$lock) { add_settings_error('spek_ml', 'locked', __('Υπάρχει ήδη migration batch σε εξέλιξη. Δεν έγινε αλλαγή.', 'spek-theme'), 'error'); return; }
    try {
        $result = spek_ml_execute_batch($scan, 50);
        $after = spek_ml_scan();
        spek_ml_store_approved_scan($after);
        add_settings_error('spek_ml', 'batch_ok', sprintf(__('Batch ολοκληρώθηκε: %1$d προϊόντα + %2$d terms πήραν γλώσσα `el`. Παραλείφθηκαν %3$d που δεν ήταν πλέον ασφαλή.', 'spek-theme'), $result['posts'], $result['terms'], count($result['skipped'])), 'success');
    } finally { spek_ml_release_lock($lock); }
}
add_action('admin_init', 'spek_ml_handle_admin_action');

function spek_ml_count_row(array $r, string $key): int { return isset($r[$key]) && is_array($r[$key]) ? count($r[$key]) : 0; }
function spek_ml_render_ids(array $ids): string {
    $ids = array_slice(array_map('intval', $ids), 0, 20);
    return $ids ? implode(', ', $ids) . (count($ids) >= 20 ? ' …' : '') : '—';
}

function spek_ml_render_admin_page(): void {
    if (!current_user_can('manage_options')) { wp_die(esc_html__('Δεν έχετε δικαίωμα πρόσβασης.', 'spek-theme')); }
    $scan = spek_ml_scan(); $approved = spek_ml_scan_is_approved($scan);
    $home = spek_ml_home_diagnostics(); $url = spek_ml_url_diagnostics(); $menus = spek_ml_menu_diagnostics();
    settings_errors('spek_ml');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('SPEK · Polylang Migration & Diagnostics', 'spek-theme'); ?></h1>
        <p><?php esc_html_e('Read-only preview πρώτα. Το write batch αλλάζει αποκλειστικά το Polylang language σε `el` για ασφαλείς legacy εγγραφές χωρίς γλώσσα.', 'spek-theme'); ?></p>
        <?php if (!$scan['polylang'] || !$scan['greek_ready']) : ?><div class="notice notice-error inline"><p><?php esc_html_e('Το write migration είναι κλειδωμένο: απαιτείται ενεργό Polylang και language slug `el`.', 'spek-theme'); ?></p></div><?php endif; ?>

        <h2><?php esc_html_e('0. Registered content model', 'spek-theme'); ?></h2>
        <table class="widefat striped" style="max-width:1000px"><thead><tr><th>Object</th><th>Registered</th></tr></thead><tbody>
            <tr><td><code>spek_product</code> CPT</td><td><?php echo post_type_exists('spek_product') ? 'yes' : 'NO'; ?></td></tr>
            <?php foreach (spek_ml_migration_taxonomies() as $taxonomy) : ?><tr><td><code><?php echo esc_html($taxonomy); ?></code></td><td><?php echo taxonomy_exists($taxonomy) ? 'yes' : 'NO'; ?></td></tr><?php endforeach; ?>
        </tbody></table>

        <h2><?php esc_html_e('1. Dry-run προϊόντων', 'spek-theme'); ?></h2>
        <table class="widefat striped" style="max-width:1000px"><thead><tr><th>Total</th><th>Safe → el</th><th>Greek</th><th>English</th><th>Translation pair</th><th>Ambiguous</th><th>Other lang</th></tr></thead><tbody><tr>
            <td><?php echo (int) $scan['products']['total']; ?></td><td><strong><?php echo (int) $scan['candidate_products']; ?></strong></td>
            <td><?php echo spek_ml_count_row($scan['products'], 'el'); ?></td><td><?php echo spek_ml_count_row($scan['products'], 'en'); ?></td><td><?php echo spek_ml_count_row($scan['products'], 'pair'); ?></td><td><?php echo spek_ml_count_row($scan['products'], 'ambiguous'); ?></td><td><?php echo spek_ml_count_row($scan['products'], 'other_language'); ?></td>
        </tr></tbody></table>
        <p><strong>Candidate IDs:</strong> <?php echo esc_html(spek_ml_render_ids($scan['products']['candidate'])); ?></p>
        <?php $ambiguous_products = spek_ml_ambiguous_product_details($scan['products']['ambiguous']); ?>
        <?php if ($ambiguous_products) : ?>
            <h3><?php esc_html_e('Ambiguous προϊόντα — δεν θα αλλαχθούν', 'spek-theme'); ?></h3>
            <table class="widefat striped" style="max-width:1000px"><thead><tr><th>ID</th><th>Title</th><th>SKU</th><th>Status</th><th>Reason</th></tr></thead><tbody>
            <?php foreach ($ambiguous_products as $row) : ?><tr><td><?php echo (int) $row['id']; ?></td><td><?php echo esc_html($row['title']); ?></td><td><code><?php echo esc_html($row['sku'] !== '' ? $row['sku'] : '—'); ?></code></td><td><code><?php echo esc_html($row['status']); ?></code></td><td><?php echo esc_html($row['reason']); ?></td></tr><?php endforeach; ?>
            </tbody></table>
        <?php endif; ?>

        <h2><?php esc_html_e('2. Dry-run product taxonomies', 'spek-theme'); ?></h2>
        <table class="widefat striped" style="max-width:1000px"><thead><tr><th>Taxonomy</th><th>Total</th><th>Safe → el</th><th>Greek</th><th>English</th><th>Pair</th><th>Ambiguous</th></tr></thead><tbody>
        <?php foreach ($scan['terms'] as $taxonomy => $r) : ?><tr><td><code><?php echo esc_html($taxonomy); ?></code></td><td><?php echo (int) $r['total']; ?></td><td><strong><?php echo spek_ml_count_row($r, 'candidate'); ?></strong></td><td><?php echo spek_ml_count_row($r, 'el'); ?></td><td><?php echo spek_ml_count_row($r, 'en'); ?></td><td><?php echo spek_ml_count_row($r, 'pair'); ?></td><td><?php echo spek_ml_count_row($r, 'ambiguous'); ?></td></tr><?php endforeach; ?>
        </tbody></table>

        <form method="post" style="margin:20px 0"><?php wp_nonce_field('spek_ml_migration_action', 'spek_ml_nonce'); ?>
            <button class="button button-secondary" name="spek_ml_action" value="dry_run"><?php esc_html_e('Run / Refresh Dry-run', 'spek-theme'); ?></button>
            <button class="button button-primary" name="spek_ml_action" value="execute_batch" <?php disabled(!$approved || !$scan['greek_ready'] || (!$scan['candidate_products'] && !$scan['candidate_terms'])); ?>><?php esc_html_e('Execute next safe batch (max 50)', 'spek-theme'); ?></button>
            <span style="margin-left:10px"><strong><?php echo $approved ? esc_html__('Dry-run approved for current state.', 'spek-theme') : esc_html__('Write locked until dry-run is approved.', 'spek-theme'); ?></strong></span>
        </form>

        <h2><?php esc_html_e('3. Homepage / Polylang diagnostics', 'spek-theme'); ?></h2>
        <table class="widefat striped" style="max-width:1000px"><tbody>
        <tr><th>show_on_front</th><td><code><?php echo esc_html($home['show_on_front']); ?></code></td></tr>
        <tr><th>Static front page</th><td>#<?php echo (int) $home['front_id']; ?> · <?php echo esc_html($home['front_title']); ?> · lang=<code><?php echo esc_html($home['front_lang'] ?: 'none'); ?></code></td></tr>
        <tr><th>Translation map</th><td><code><?php echo esc_html(wp_json_encode($home['translations'])); ?></code></td></tr>
        <tr><th>Greek home URL</th><td><code><?php echo esc_html($home['el_home']); ?></code></td></tr>
        <tr><th>English home URL</th><td><code><?php echo esc_html($home['en_home']); ?></code></td></tr>
        <tr><th>Polylang URL settings</th><td><code><?php echo esc_html(wp_json_encode($url)); ?></code></td></tr>
        </tbody></table>
        <p><?php esc_html_e('Το εργαλείο δεν συνδέει αυτόματα Home pages. Αν το translation map δεν έχει `el` + `en`, σύνδεσέ τες από το Polylang metabox των σελίδων αφού επιβεβαιώσεις ποια είναι η σωστή English Home.', 'spek-theme'); ?></p>

        <h2><?php esc_html_e('4. Menu assignments', 'spek-theme'); ?></h2>
        <table class="widefat striped" style="max-width:1000px"><thead><tr><th>Logical location</th><th>Polylang location</th><th>Language</th><th>Menu</th></tr></thead><tbody>
        <?php foreach ($menus as $row) : ?><tr><td><code><?php echo esc_html($row['base']); ?></code></td><td><code><?php echo esc_html($row['location']); ?></code></td><td><code><?php echo esc_html($row['language']); ?></code></td><td><?php echo $row['menu_id'] ? '#' . (int) $row['menu_id'] . ' · ' . esc_html($row['menu_name']) : '—'; ?></td></tr><?php endforeach; ?>
        </tbody></table>
        <p><?php esc_html_e('Το theme δηλώνει μόνο τις βασικές θέσεις menu. Το Polylang δημιουργεί αυτόματα ξεχωριστή θέση ανά γλώσσα (π.χ. primary___en). Στο Appearance → Menus ανάθεσε το Greek menu στο Primary Menu Ελληνικά και το English menu στο Primary Menu English. Μέχρι να οριστεί English menu, το theme εμφανίζει ασφαλές προσωρινό fallback: χρησιμοποιεί πραγματικές μεταφράσεις όπου υπάρχουν και, όπου δεν υπάρχουν ακόμη, κρατά το υπαρκτό source URL ώστε να μην οδηγεί σε 404 ή άσχετη αρχική.', 'spek-theme'); ?></p>
    </div>
    <?php
}
