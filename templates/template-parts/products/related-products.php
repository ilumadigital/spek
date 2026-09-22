<?php
/** Related products constrained to the current language. @package SpekTheme */
if (!defined('ABSPATH')) { exit; }
$product_id = get_the_ID();
$manual = spek_get_product_field('related_products', $product_id);
$ids = [];
foreach (is_array($manual) ? $manual : [] as $item) {
    $id = is_object($item) ? (int) $item->ID : (int) $item;
    if ($id && $id !== (int) $product_id) { $ids[] = $id; }
}
$args = spek_language_args(['post_type' => 'spek_product', 'post_status' => 'publish', 'posts_per_page' => 3, 'post__not_in' => [$product_id]]);
if (function_exists('spek_is_english') && spek_is_english()) {
    $args['meta_query'] = [['key' => '_spek_title_en', 'value' => '', 'compare' => '!=']];
}
if ($ids) {
    $args['post__in'] = array_values(array_unique($ids));
    $args['orderby'] = 'post__in';
} else {
    $terms = get_the_terms($product_id, 'product_category');
    $args['tax_query'] = [['taxonomy' => 'product_category', 'field' => 'term_id', 'terms' => $terms && !is_wp_error($terms) ? wp_list_pluck($terms, 'term_id') : [0]]];
}
$related_query = new WP_Query($args);
?>
<section class="section related-products">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow"><?php esc_html_e('Related Products', 'spek-theme'); ?></span>
            <h2><?php esc_html_e('Σχετικά προϊόντα', 'spek-theme'); ?></h2>
        </div>
        <div class="products-grid">
            <?php if ($related_query->have_posts()) : ?>
                <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                    <?php get_template_part('template-parts/products/product-card'); ?>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php esc_html_e('Δεν υπάρχουν σχετικά προϊόντα αυτή τη στιγμή.', 'spek-theme'); ?></p>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </div>
</section>
