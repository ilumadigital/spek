<?php
/**
 * Product filters.
 *
 * Multi-select marketplace-style facets for the product archive.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$archive_url = get_post_type_archive_link('spek_product');
$archive_url = $archive_url ?: home_url('/products/');

if (!function_exists('spek_product_filters_normalize')) {
    function spek_product_filters_normalize(string $value): string
    {
        $value = remove_accents(wp_strip_all_tags($value));
        $value = function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);

        $value = preg_replace('/[^\\p{L}\\p{N}]+/u', ' ', $value);

        return trim((string) $value);
    }
}

if (!function_exists('spek_product_filter_icon_svg')) {
    function spek_product_filter_icon_svg(string $key): string
    {
        $icons = [
            'mixanismoi' => '<svg width="36" height="36" viewBox="0 0 48 48" aria-hidden="true"><rect x="9" y="9" width="18" height="30" rx="3" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M18 15v18M13 20h10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M29 16h10v8H29z" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M35 24v10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>',
            'floter' => '<svg width="36" height="36" viewBox="0 0 48 48" aria-hidden="true"><circle cx="17" cy="28" r="7" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M24 28h7V14h7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 14h4v4h-4zM37 11h4v4h-4z" fill="none" stroke="currentColor" stroke-width="2.2"/></svg>',
            'sifonia' => '<svg width="36" height="36" viewBox="0 0 48 48" aria-hidden="true"><path d="M16 11v12a6 6 0 0 0 6 6h8v7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 11h8M28 36h12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><rect x="10" y="8" width="8" height="6" rx="1.5" fill="none" stroke="currentColor" stroke-width="2.2"/></svg>',
            'kalymmata-lekanis' => '<svg width="36" height="36" viewBox="0 0 48 48" aria-hidden="true"><path d="M24 9c7.2 0 13 5.8 13 13v5c0 2.8-2.2 5-5 5H16c-2.8 0-5-2.2-5-5v-5c0-7.2 5.8-13 13-13Z" fill="none" stroke="currentColor" stroke-width="2.2"/><ellipse cx="24" cy="33.5" rx="12" ry="5.5" fill="none" stroke="currentColor" stroke-width="2.2"/></svg>',
            'kazanakia' => '<svg width="36" height="36" viewBox="0 0 48 48" aria-hidden="true"><rect x="10" y="11" width="24" height="18" rx="3" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M34 17h4a3 3 0 0 1 3 3v5a3 3 0 0 1-3 3h-4" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M18 11V8h8v3" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>',
            'banio' => '<svg width="36" height="36" viewBox="0 0 48 48" aria-hidden="true"><path d="M10 27h26a4 4 0 0 1-4 4H14a4 4 0 0 1-4-4Z" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M16 27V15a4 4 0 0 1 8 0v1" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M12 31l-2 6M34 31l2 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>',
            'lastixa' => '<svg width="36" height="36" viewBox="0 0 48 48" aria-hidden="true"><ellipse cx="18" cy="24" rx="8" ry="11" fill="none" stroke="currentColor" stroke-width="2.2"/><ellipse cx="31" cy="25" rx="6" ry="8" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M36 15c2 1 3 4 3 7 0 5-3 9-7 10" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>',
            'diafora' => '<svg width="36" height="36" viewBox="0 0 48 48" aria-hidden="true"><rect x="9" y="11" width="30" height="24" rx="3" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M16 9v6M32 9v6M9 19h30" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M18 25h12M18 29h8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>',
        ];

        return $icons[$key] ?? $icons['diafora'];
    }
}

$selected_application = function_exists('spek_product_filter_term_ids')
    ? spek_product_filter_term_ids('product_application')
    : [];

$selected_material = function_exists('spek_product_filter_term_ids')
    ? spek_product_filter_term_ids('product_material')
    : [];

$selected_series = function_exists('spek_product_filter_term_ids')
    ? spek_product_filter_term_ids('product_series')
    : [];

$legacy_selected_categories = function_exists('spek_product_filter_term_ids')
    ? spek_product_filter_term_ids('product_category')
    : [];

$product_search = isset($_GET['product_search']) && !is_array($_GET['product_search'])
    ? sanitize_text_field(wp_unslash($_GET['product_search']))
    : '';

$category_groups = function_exists('spek_home_category_group_definitions')
    ? spek_home_category_group_definitions()
    : [];

$selected_category_groups = function_exists('spek_product_filter_category_group_keys')
    ? spek_product_filter_category_group_keys()
    : [];

if (!$selected_category_groups && isset($_GET['home_category_group']) && !is_array($_GET['home_category_group'])) {
    $home_group = sanitize_key(wp_unslash((string) $_GET['home_category_group']));
    if (isset($category_groups[$home_group])) {
        $selected_category_groups[] = $home_group;
    }
}

if (!$selected_category_groups && $legacy_selected_categories && $category_groups) {
    foreach ($legacy_selected_categories as $term_id) {
        $matched = false;

        foreach ($category_groups as $group_key => $group_definition) {
            if ($group_key === 'diafora') {
                continue;
            }

            $group_ids = function_exists('spek_home_category_group_term_ids')
                ? spek_home_category_group_term_ids($group_key)
                : [];

            if (in_array((int) $term_id, array_map('intval', $group_ids), true)) {
                $selected_category_groups[] = $group_key;
                $matched = true;
                break;
            }
        }

        if (!$matched && isset($category_groups['diafora'])) {
            $selected_category_groups[] = 'diafora';
        }
    }

    $selected_category_groups = array_values(array_unique($selected_category_groups));
}

if (
    !$selected_category_groups
    && is_tax('product_category')
    && ($queried_category = get_queried_object()) instanceof WP_Term
    && $category_groups
) {
    $matched = false;

    foreach ($category_groups as $group_key => $group_definition) {
        if ($group_key === 'diafora') {
            continue;
        }

        $group_ids = function_exists('spek_home_category_group_term_ids')
            ? spek_home_category_group_term_ids($group_key)
            : [];

        if (in_array((int) $queried_category->term_id, array_map('intval', $group_ids), true)) {
            $selected_category_groups[] = $group_key;
            $matched = true;
            break;
        }
    }

    if (!$matched && isset($category_groups['diafora'])) {
        $selected_category_groups[] = 'diafora';
    }
}

$applications = get_terms(spek_language_args([
    'taxonomy' => 'product_application',
    'hide_empty' => true,
]));

$materials = get_terms(spek_language_args([
    'taxonomy' => 'product_material',
    'hide_empty' => true,
]));

$series = get_terms(spek_language_args([
    'taxonomy' => 'product_series',
    'hide_empty' => true,
]));

$applications = (!empty($applications) && !is_wp_error($applications)) ? array_values($applications) : [];
$materials = (!empty($materials) && !is_wp_error($materials)) ? array_values($materials) : [];
$series = (!empty($series) && !is_wp_error($series)) ? array_values($series) : [];

$category_icon_map = [
    'mixanismoi' => 'mixanismoi',
    'floter' => 'floter',
    'sifonia' => 'sifonia',
    'kalymmata-lekanis' => 'kalymmata-lekanis',
    'kazanakia' => 'kazanakia',
    'banio' => 'banio',
    'lastixa' => 'lastixa',
    'diafora' => 'diafora',
];

$category_counts = [];
$all_category_terms = get_terms(spek_language_args([
    'taxonomy' => 'product_category',
    'hide_empty' => true,
]));

$category_term_counts = [];
$all_category_term_ids = [];

if (!empty($all_category_terms) && !is_wp_error($all_category_terms)) {
    foreach ($all_category_terms as $term) {
        $term_id = (int) $term->term_id;
        $category_term_counts[$term_id] = (int) $term->count;
        $all_category_term_ids[] = $term_id;
    }
}

$primary_category_ids = [];

foreach (array_keys($category_groups) as $group_key) {
    if ($group_key === 'diafora') {
        continue;
    }

    $group_ids = function_exists('spek_home_category_group_term_ids')
        ? array_map('intval', spek_home_category_group_term_ids($group_key))
        : [];

    $primary_category_ids = array_merge($primary_category_ids, $group_ids);
    $category_counts[$group_key] = array_sum(array_map(
        static fn ($term_id) => $category_term_counts[(int) $term_id] ?? 0,
        $group_ids
    ));
}

$primary_category_ids = array_values(array_unique(array_filter(array_map('intval', $primary_category_ids))));

if (isset($category_groups['diafora'])) {
    $other_ids = array_values(array_diff($all_category_term_ids, $primary_category_ids));
    $category_counts['diafora'] = array_sum(array_map(
        static fn ($term_id) => $category_term_counts[(int) $term_id] ?? 0,
        $other_ids
    ));
}

$brand_specs = [
    'spek' => [
        'patterns' => ['spek'],
        'logo' => get_template_directory_uri() . '/assets/images/brand/spek_logo.png',
    ],
    'nemo' => [
        'patterns' => ['nemo'],
        'logo' => get_template_directory_uri() . '/assets/images/brand/nemo_logo.png',
    ],
    'idrospania' => [
        'patterns' => ['idrospania', 'idro spania', 'υδροσπανια', 'υδροσπάνια'],
        'logo' => 'https://idrospania.es/wp-content/uploads/2021/10/logotipo-idrospania.svg',
    ],
    'lbplast' => [
        'patterns' => ['lb plast', 'lbplast', 'lb-plast'],
        'logo' => 'https://lbplast.com/wp-content/uploads/2025/06/logo-LBPlast.svg',
    ],
    'mcalpine' => [
        'patterns' => ['mcalpine', 'mc alpine', 'mc-alpine', 'mc alpin'],
        'logo' => 'https://mcalpine.pl/wp-content/uploads/2024/10/cropped-Logo-na-Strone.png',
    ],
];

$brand_key_for_term = static function (WP_Term $term) use ($brand_specs): string {
    $haystack = spek_product_filters_normalize($term->name . ' ' . $term->slug);

    foreach ($brand_specs as $brand_key => $spec) {
        foreach ($spec['patterns'] as $pattern) {
            $pattern = spek_product_filters_normalize((string) $pattern);

            if ($pattern !== '' && strpos($haystack, $pattern) !== false) {
                return $brand_key;
            }
        }
    }

    return 'other';
};

$brand_order = [
    'spek' => 0,
    'nemo' => 1,
    'idrospania' => 2,
    'lbplast' => 3,
    'mcalpine' => 4,
    'other' => 5,
];

usort($series, static function (WP_Term $a, WP_Term $b) use ($brand_key_for_term, $brand_order): int {
    $a_key = $brand_key_for_term($a);
    $b_key = $brand_key_for_term($b);
    $a_weight = $brand_order[$a_key] ?? 99;
    $b_weight = $brand_order[$b_key] ?? 99;

    if ($a_weight === $b_weight) {
        return strnatcasecmp($a->name, $b->name);
    }

    return $a_weight <=> $b_weight;
});

$selected_filter_count = count($selected_category_groups)
    + count($selected_application)
    + count($selected_material)
    + count($selected_series)
    + ($product_search !== '' ? 1 : 0);

$render_compact_checkbox_group = static function (
    string $legend,
    string $name,
    array $terms,
    array $selected_ids
): void {
    if (!$terms) {
        return;
    }
    ?>
    <fieldset class="filter-check-group">
        <legend><?php echo esc_html($legend); ?></legend>

        <div class="filter-checkbox-list">
            <?php foreach ($terms as $term) : ?>
                <?php
                $term_id = (int) $term->term_id;
                $is_selected = in_array($term_id, $selected_ids, true);
                ?>
                <label class="filter-checkbox">
                    <input
                        type="checkbox" style="position:absolute;width:1px;height:1px;opacity:0;pointer-events:none"
                        name="<?php echo esc_attr($name); ?>[]"
                        value="<?php echo esc_attr((string) $term_id); ?>"
                        <?php checked($is_selected); ?>
                    >
                    <span class="filter-checkbox__control" aria-hidden="true"></span>
                    <span class="filter-checkbox__label"><?php echo esc_html($term->name); ?></span>
                    <span class="filter-checkbox__count"><?php echo esc_html(number_format_i18n((int) $term->count)); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>
    <?php
};
?>

<details class="product-filters-panel" data-product-filters-panel>
    <summary class="product-filters-toggle">
        <span class="product-filters-toggle__title">
            <?php esc_html_e('Φίλτρα', 'spek-theme'); ?>
            <?php if ($selected_filter_count > 0) : ?>
                <small><?php echo esc_html(number_format_i18n($selected_filter_count)); ?></small>
            <?php endif; ?>
        </span>

        <span class="product-filters-toggle__action">
            <?php esc_html_e('Επιλογές', 'spek-theme'); ?>
            <svg viewBox="0 0 12 8" aria-hidden="true"><path d="m1 1 5 5 5-5"/></svg>
        </span>
    </summary>

    <form class="product-filters" method="get" action="<?php echo esc_url($archive_url); ?>">
        <div class="product-filters__scroll">
        <div class="product-filters__head">
            <h2><?php esc_html_e('Φίλτρα', 'spek-theme'); ?></h2>

            <?php if ($selected_filter_count > 0) : ?>
                <a href="<?php echo esc_url($archive_url); ?>" class="product-filters__clear">
                    <?php esc_html_e('Καθαρισμός', 'spek-theme'); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="filter-search-group">
            <label for="product_search"><?php esc_html_e('Αναζήτηση', 'spek-theme'); ?></label>
            <input
                id="product_search"
                type="search"
                name="product_search"
                value="<?php echo esc_attr($product_search); ?>"
                placeholder="<?php esc_attr_e('Προϊόν ή κωδικός', 'spek-theme'); ?>"
            >
        </div>

        <?php if ($category_groups) : ?>
            <fieldset class="filter-check-group filter-check-group--categories">
                <legend><?php esc_html_e('Κατηγορίες', 'spek-theme'); ?></legend>

                <div class="filter-category-grid">
                    <?php foreach ($category_groups as $group_key => $group) : ?>
                        <?php
                        $is_selected = in_array($group_key, $selected_category_groups, true);
                        $icon = $category_icon_map[$group_key] ?? 'misc';
                        ?>
                        <label class="filter-category-card">
                            <input
                                type="checkbox" style="position:absolute;width:1px;height:1px;opacity:0;pointer-events:none"
                                name="filter_category_group[]"
                                value="<?php echo esc_attr($group_key); ?>"
                                <?php checked($is_selected); ?>
                            >

                            <span class="filter-category-card__check" aria-hidden="true"></span>

                            <span class="filter-category-card__icon" aria-hidden="true" style="display:grid;place-items:center;width:40px;height:40px;overflow:hidden">
                                <?php echo spek_product_filter_icon_svg($icon); ?>
                            </span>

                            <span class="filter-category-card__body">
                                <strong><?php echo esc_html((string) ($group['label'] ?? $group_key)); ?></strong>
                                <small><?php
                                    printf(
                                        esc_html(_n('%s προϊόν', '%s προϊόντα', (int) ($category_counts[$group_key] ?? 0), 'spek-theme')),
                                        esc_html(number_format_i18n((int) ($category_counts[$group_key] ?? 0)))
                                    );
                                ?></small>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endif; ?>

        <?php if ($series) : ?>
            <fieldset class="filter-check-group filter-check-group--brands">
                <legend><?php esc_html_e('Εταιρεία', 'spek-theme'); ?></legend>

                <div class="filter-brand-list">
                    <?php foreach ($series as $item) : ?>
                        <?php
                        $brand_key = $brand_key_for_term($item);
                        $is_selected = in_array((int) $item->term_id, $selected_series, true);
                        $logo = $brand_key !== 'other' ? ($brand_specs[$brand_key]['logo'] ?? '') : '';
                        ?>
                        <label class="filter-brand-item filter-brand-item--<?php echo esc_attr($brand_key); ?>">
                            <input
                                type="checkbox" style="position:absolute;width:1px;height:1px;opacity:0;pointer-events:none"
                                name="filter_series[]"
                                value="<?php echo esc_attr((string) $item->term_id); ?>"
                                <?php checked($is_selected); ?>
                            >

                            <span class="filter-brand-item__check" aria-hidden="true"></span>

                            <span class="filter-brand-item__logo">
                                <?php if ($logo !== '') : ?>
                                    <img
                                        src="<?php echo esc_url($logo); ?>"
                                        alt="<?php echo esc_attr($item->name); ?>"
                                        loading="lazy"
                                        width="118"
                                        height="28"
                                        style="display:block;width:auto;max-width:118px;height:auto;max-height:28px;object-fit:contain;object-position:left center"
                                    >
                                <?php else : ?>
                                    <strong><?php echo esc_html($item->name); ?></strong>
                                <?php endif; ?>
                            </span>

                            <span class="filter-brand-item__count"><?php echo esc_html(number_format_i18n((int) $item->count)); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endif; ?>

        <?php
        $render_compact_checkbox_group(
            __('Εφαρμογή', 'spek-theme'),
            'filter_application',
            $applications,
            $selected_application
        );

        $render_compact_checkbox_group(
            __('Υλικό', 'spek-theme'),
            'filter_material',
            $materials,
            $selected_material
        );
        ?>

        </div>

        <div class="product-filters__actions">
            <button type="submit" class="button button-primary button-full">
                <?php esc_html_e('Εφαρμογή φίλτρων', 'spek-theme'); ?>
            </button>
        </div>
    </form>
</details>
