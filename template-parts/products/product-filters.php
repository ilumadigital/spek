<?php
/**
 * Product filters.
 *
 * Multi-select taxonomy filters use numeric term IDs so Greek/non-Latin term
 * slugs never collide with WordPress native taxonomy query vars.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$selected_category = function_exists('spek_product_filter_term_ids') ? spek_product_filter_term_ids('product_category') : [];
$selected_application = function_exists('spek_product_filter_term_ids') ? spek_product_filter_term_ids('product_application') : [];
$selected_material = function_exists('spek_product_filter_term_ids') ? spek_product_filter_term_ids('product_material') : [];
$selected_series = function_exists('spek_product_filter_term_ids') ? spek_product_filter_term_ids('product_series') : [];

if (is_tax(['product_category', 'product_application', 'product_material', 'product_series'])) {
    $term = get_queried_object();

    if ($term instanceof WP_Term) {
        $taxonomy_selected_map = [
            'product_category' => &$selected_category,
            'product_application' => &$selected_application,
            'product_material' => &$selected_material,
            'product_series' => &$selected_series,
        ];

        if (isset($taxonomy_selected_map[$term->taxonomy]) && !in_array((int) $term->term_id, $taxonomy_selected_map[$term->taxonomy], true)) {
            $taxonomy_selected_map[$term->taxonomy][] = (int) $term->term_id;
        }

        unset($taxonomy_selected_map);
    }
}

$product_search = isset($_GET['product_search']) && !is_array($_GET['product_search'])
    ? sanitize_text_field(wp_unslash($_GET['product_search']))
    : '';

$categories = get_terms(spek_language_args([
    'taxonomy' => 'product_category',
    'hide_empty' => true,
]));

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

$archive_url = get_post_type_archive_link('spek_product');
$archive_url = $archive_url ?: home_url('/products/');

$selected_filter_count = count($selected_category)
    + count($selected_application)
    + count($selected_material)
    + count($selected_series)
    + ($product_search !== '' ? 1 : 0);

$render_checkbox_group = static function (
    string $legend,
    string $name,
    $terms,
    array $selected_ids
): void {
    if (empty($terms) || is_wp_error($terms)) {
        return;
    }
    ?>
    <fieldset class="filter-check-group">
        <legend><?php echo esc_html($legend); ?></legend>

        <div class="filter-checkbox-list">
            <?php foreach ($terms as $term) : ?>
                <?php $term_id = (int) $term->term_id; ?>
                <label class="filter-checkbox">
                    <input
                        type="checkbox"
                        name="<?php echo esc_attr($name); ?>[]"
                        value="<?php echo esc_attr((string) $term_id); ?>"
                        <?php checked(in_array($term_id, $selected_ids, true)); ?>
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

<details class="product-filters-panel">
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
        <div class="product-filters__head">
            <div>
                <span><?php esc_html_e('Κατάλογος', 'spek-theme'); ?></span>
                <h2><?php esc_html_e('Φίλτρα', 'spek-theme'); ?></h2>
            </div>

            <?php if ($selected_filter_count > 0) : ?>
                <span class="product-filters__selected">
                    <?php
                    printf(
                        esc_html(_n('%s επιλογή', '%s επιλογές', $selected_filter_count, 'spek-theme')),
                        esc_html(number_format_i18n($selected_filter_count))
                    );
                    ?>
                </span>
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

        <?php $render_checkbox_group(__('Κατηγορία', 'spek-theme'), 'filter_category', $categories, $selected_category); ?>
        <?php $render_checkbox_group(__('Εφαρμογή', 'spek-theme'), 'filter_application', $applications, $selected_application); ?>
        <?php $render_checkbox_group(__('Υλικό', 'spek-theme'), 'filter_material', $materials, $selected_material); ?>
        <?php $render_checkbox_group(__('Μάρκα / Σειρά', 'spek-theme'), 'filter_series', $series, $selected_series); ?>

        <div class="product-filters__actions">
            <button type="submit" class="button button-primary button-full">
                <?php esc_html_e('Εφαρμογή φίλτρων', 'spek-theme'); ?>
            </button>

            <?php if ($selected_filter_count > 0) : ?>
                <a href="<?php echo esc_url($archive_url); ?>" class="filters-reset">
                    <?php esc_html_e('Καθαρισμός όλων', 'spek-theme'); ?>
                </a>
            <?php endif; ?>
        </div>
    </form>
</details>
