<?php
/**
 * Product specs and product properties.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$product_id = get_the_ID();
$products_url = get_post_type_archive_link('spek_product') ?: spek_page_url('products/');

$taxonomy_rows = [
    'product_category' => __('Κατηγορία', 'spek-theme'),
    'product_application' => __('Εφαρμογή', 'spek-theme'),
    'product_material' => __('Υλικό', 'spek-theme'),
    'product_series' => __('Σειρά', 'spek-theme'),
];

$fields = [
    'product_code' => __('SKU / Κωδικός', 'spek-theme'),
    'product_dimensions' => __('Διαστάσεις', 'spek-theme'),
    'product_material' => __('Υλικό', 'spek-theme'),
    'product_color' => __('Χρώμα', 'spek-theme'),
    'product_application_text' => __('Εφαρμογή', 'spek-theme'),
    'product_compatibility' => __('Συμβατότητα', 'spek-theme'),
    'product_installation_type' => __('Τύπος εγκατάστασης', 'spek-theme'),
    'product_packaging' => __('Συσκευασία', 'spek-theme'),
    'product_weight' => __('Βάρος', 'spek-theme'),
    'product_capacity' => __('Χωρητικότητα / περιεχόμενο', 'spek-theme'),
    'product_features' => __('Πρόσθετα χαρακτηριστικά', 'spek-theme'),

];

$excluded_dynamic_meta = array_fill_keys(array_merge(array_keys($fields), [
    'product_short_description',
    'product_installation_video',
    'product_video',
    'product_videos',
    'product_datasheet',
    'product_datasheets',
    'product_catalogue_pdf',
    'product_catalogue',
    'product_catalogues',
    'product_gallery',
    'related_products',
]), true);

$has_specs = false;

foreach ($taxonomy_rows as $taxonomy => $label) {
    $terms = get_the_terms($product_id, $taxonomy);
    if (!empty($terms) && !is_wp_error($terms)) {
        $has_specs = true;
        break;
    }
}

if (!$has_specs) {
    foreach ($fields as $field_key => $label) {
        $value = spek_get_product_field($field_key, $product_id);
        if (!spek_product_value_is_empty($value)) {
            $has_specs = true;
            break;
        }
    }
}

$dynamic_rows = [];
$all_meta = get_post_meta($product_id);

foreach ($all_meta as $meta_key => $raw_values) {
    if (strpos((string) $meta_key, '_') === 0 || isset($excluded_dynamic_meta[$meta_key])) {
        continue;
    }

    $value = spek_get_product_field((string) $meta_key, $product_id);
    $text = spek_product_value_to_text($value);

    if ($text === '') {
        continue;
    }

    $label = '';
    if (function_exists('get_field_object')) {
        $field_object = get_field_object((string) $meta_key, $product_id, false, false);
        if (is_array($field_object) && !empty($field_object['label'])) {
            $label = (string) $field_object['label'];
        }
    }

    if ($label === '') {
        $label = ucwords(str_replace(['product_', '_', '-'], ['', ' ', ' '], (string) $meta_key));
    }

    $label = apply_filters('spek_product_meta_label', $label, (string) $meta_key, $product_id);
    $dynamic_rows[] = [
        'label' => $label,
        'value' => $text,
    ];
    $has_specs = true;
}
?>

<aside class="product-specs" id="technical-specs">
    <span class="eyebrow"><?php esc_html_e('Technical Data', 'spek-theme'); ?></span>
    <h2><?php esc_html_e('Χαρακτηριστικά & ιδιότητες', 'spek-theme'); ?></h2>

    <?php if ($has_specs) : ?>
        <dl class="specs-list">
            <?php foreach ($taxonomy_rows as $taxonomy => $label) : ?>
                <?php $terms = get_the_terms($product_id, $taxonomy); ?>
                <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
                    <div class="specs-list__row">
                        <dt><?php echo esc_html($label); ?></dt>
                        <dd>
                            <span class="specs-taxonomy-links">
                                <?php foreach ($terms as $term) : ?>
                                    <a href="<?php echo esc_url(get_term_link($term)); ?>">
                                        <?php echo esc_html($term->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </span>
                        </dd>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php foreach ($fields as $field_key => $label) : ?>
                <?php $value = spek_get_product_field($field_key, $product_id); ?>
                <?php if (!spek_product_value_is_empty($value)) : ?>
                    <div class="specs-list__row">
                        <dt><?php echo esc_html($label); ?></dt>
                        <dd>
                            <?php if ($field_key === 'product_code') : ?>
                                <a class="spec-value-link" href="<?php echo esc_url(add_query_arg('product_search', (string) $value, $products_url)); ?>">
                                    <?php echo esc_html(spek_product_value_to_text($value)); ?>
                                </a>
                            <?php else : ?>
                                <?php echo esc_html(spek_product_value_to_text($value)); ?>
                            <?php endif; ?>
                        </dd>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php foreach ($dynamic_rows as $row) : ?>
                <div class="specs-list__row specs-list__row--extra">
                    <dt><?php echo esc_html($row['label']); ?></dt>
                    <dd><?php echo esc_html($row['value']); ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    <?php else : ?>
        <p><?php esc_html_e('Δεν έχουν καταχωρηθεί ακόμη τεχνικά χαρακτηριστικά για αυτό το προϊόν.', 'spek-theme'); ?></p>
    <?php endif; ?>
</aside>
