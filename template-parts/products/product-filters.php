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

$category_counts = function_exists('spek_product_filter_category_group_counts')
    ? spek_product_filter_category_group_counts(array_keys($category_groups))
    : [];

$application_counts = function_exists('spek_product_filter_term_counts')
    ? spek_product_filter_term_counts(
        'product_application',
        array_map(static fn (WP_Term $term) => (int) $term->term_id, $applications)
    )
    : [];

$material_counts = function_exists('spek_product_filter_term_counts')
    ? spek_product_filter_term_counts(
        'product_material',
        array_map(static fn (WP_Term $term) => (int) $term->term_id, $materials)
    )
    : [];

$series_counts = function_exists('spek_product_filter_term_counts')
    ? spek_product_filter_term_counts(
        'product_series',
        array_map(static fn (WP_Term $term) => (int) $term->term_id, $series)
    )
    : [];

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
    array $selected_ids,
    array $facet_counts
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
                    <span class="filter-checkbox__count"><?php echo esc_html(number_format_i18n((int) ($facet_counts[$term_id] ?? 0))); ?></span>
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

    <form id="product-filters-form" class="product-filters" method="get" action="<?php echo esc_url($archive_url); ?>" data-auto-filters>
        <div class="product-filters__scroll">
        <div class="product-filters__head">
            <h2><?php esc_html_e('Φίλτρα', 'spek-theme'); ?></h2>

            <?php if ($selected_filter_count > 0) : ?>
                <a href="<?php echo esc_url($archive_url); ?>" class="product-filters__clear">
                    <?php esc_html_e('Καθαρισμός', 'spek-theme'); ?>
                </a>
            <?php endif; ?>
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

                            <span class="filter-category-card__icon" aria-hidden="true">
                                <?php spek_render_product_category_icon($icon, 'spek-category-icon filter-category-card__asset'); ?>
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

                            <span class="filter-brand-item__count"><?php echo esc_html(number_format_i18n((int) ($series_counts[(int) $item->term_id] ?? 0))); ?></span>
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
            $selected_application,
            $application_counts
        );

        $render_compact_checkbox_group(
            __('Υλικό', 'spek-theme'),
            'filter_material',
            $materials,
            $selected_material,
            $material_counts
        );
        ?>

        </div>

    </form>
</details>
