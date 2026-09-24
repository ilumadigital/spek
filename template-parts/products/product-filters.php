<?php
/**
 * Product filters.
 *
 * Uses custom filter_* GET parameters with numeric term IDs. This avoids
 * collisions with WordPress' native taxonomy query vars and works reliably
 * with Greek/non-Latin term slugs.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$selected_category = function_exists('spek_product_filter_term_id') ? spek_product_filter_term_id('product_category') : 0;
$selected_application = function_exists('spek_product_filter_term_id') ? spek_product_filter_term_id('product_application') : 0;
$selected_material = function_exists('spek_product_filter_term_id') ? spek_product_filter_term_id('product_material') : 0;
$selected_series = function_exists('spek_product_filter_term_id') ? spek_product_filter_term_id('product_series') : 0;

if (is_tax(['product_category', 'product_application', 'product_material', 'product_series'])) {
    $term = get_queried_object();
    if ($term instanceof WP_Term) {
        if ($term->taxonomy === 'product_category' && !$selected_category) { $selected_category = (int) $term->term_id; }
        if ($term->taxonomy === 'product_application' && !$selected_application) { $selected_application = (int) $term->term_id; }
        if ($term->taxonomy === 'product_material' && !$selected_material) { $selected_material = (int) $term->term_id; }
        if ($term->taxonomy === 'product_series' && !$selected_series) { $selected_series = (int) $term->term_id; }
    }
}

$product_search = isset($_GET['product_search']) ? sanitize_text_field(wp_unslash($_GET['product_search'])) : '';
$home_category_group = isset($_GET['home_category_group'])
    ? sanitize_key(wp_unslash((string) $_GET['home_category_group']))
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
?>

<form class="product-filters" method="get" action="<?php echo esc_url($archive_url); ?>">
    <?php if ($home_category_group !== '') : ?>
        <input type="hidden" name="home_category_group" value="<?php echo esc_attr($home_category_group); ?>">
    <?php endif; ?>
    <h2><?php esc_html_e('Φίλτρα', 'spek-theme'); ?></h2>

    <div class="filter-group">
        <label for="product_search"><?php esc_html_e('Αναζήτηση', 'spek-theme'); ?></label>
        <input
            id="product_search"
            type="search"
            name="product_search"
            value="<?php echo esc_attr($product_search); ?>"
            placeholder="<?php esc_attr_e('Αναζήτηση προϊόντος ή κωδικού', 'spek-theme'); ?>"
        >
    </div>

    <div class="filter-group">
        <label for="filter-category"><?php esc_html_e('Κατηγορία', 'spek-theme'); ?></label>
        <select id="filter-category" name="filter_category">
            <option value=""><?php esc_html_e('Όλες οι κατηγορίες', 'spek-theme'); ?></option>

            <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?php echo esc_attr((string) $category->term_id); ?>" <?php selected($selected_category, (int) $category->term_id); ?>>
                        <?php echo esc_html($category->name); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="filter-group">
        <label for="filter-application"><?php esc_html_e('Εφαρμογή', 'spek-theme'); ?></label>
        <select id="filter-application" name="filter_application">
            <option value=""><?php esc_html_e('Όλες οι εφαρμογές', 'spek-theme'); ?></option>

            <?php if (!empty($applications) && !is_wp_error($applications)) : ?>
                <?php foreach ($applications as $application) : ?>
                    <option value="<?php echo esc_attr((string) $application->term_id); ?>" <?php selected($selected_application, (int) $application->term_id); ?>>
                        <?php echo esc_html($application->name); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="filter-group">
        <label for="filter-material"><?php esc_html_e('Υλικό', 'spek-theme'); ?></label>
        <select id="filter-material" name="filter_material">
            <option value=""><?php esc_html_e('Όλα τα υλικά', 'spek-theme'); ?></option>

            <?php if (!empty($materials) && !is_wp_error($materials)) : ?>
                <?php foreach ($materials as $material) : ?>
                    <option value="<?php echo esc_attr((string) $material->term_id); ?>" <?php selected($selected_material, (int) $material->term_id); ?>>
                        <?php echo esc_html($material->name); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="filter-group">
        <label for="filter-series"><?php esc_html_e('Σειρά', 'spek-theme'); ?></label>
        <select id="filter-series" name="filter_series">
            <option value=""><?php esc_html_e('Όλες οι σειρές', 'spek-theme'); ?></option>

            <?php if (!empty($series) && !is_wp_error($series)) : ?>
                <?php foreach ($series as $item) : ?>
                    <option value="<?php echo esc_attr((string) $item->term_id); ?>" <?php selected($selected_series, (int) $item->term_id); ?>>
                        <?php echo esc_html($item->name); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <button type="submit" class="button button-primary button-full">
        <?php esc_html_e('Εφαρμογή φίλτρων', 'spek-theme'); ?>
    </button>

    <?php if ($selected_category || $selected_application || $selected_material || $selected_series || $product_search || $home_category_group) : ?>
        <a href="<?php echo esc_url($archive_url); ?>" class="filters-reset">
            <?php esc_html_e('Καθαρισμός φίλτρων', 'spek-theme'); ?>
        </a>
    <?php endif; ?>
</form>
