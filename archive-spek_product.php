<?php
/**
 * Product archive template.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$products_archive_url = get_post_type_archive_link('spek_product');
$products_archive_url = $products_archive_url ?: home_url('/products/');
$home_category_group = isset($_GET['home_category_group']) ? sanitize_key(wp_unslash((string) $_GET['home_category_group'])) : '';
$home_category_group_label = ($home_category_group !== '' && function_exists('spek_home_category_group_label'))
    ? spek_home_category_group_label($home_category_group)
    : '';

?>

<main id="main" class="site-main products-archive">

    <section class="page-hero products-archive__hero">
        <div class="container products-archive__container">
            <span class="eyebrow"><?php esc_html_e('Product Catalogue', 'spek-theme'); ?></span>
            <h1><?php
                if ($home_category_group_label !== '') {
                    echo esc_html($home_category_group_label);
                } elseif (is_tax()) {
                    $archive_term = get_queried_object();
                    if ($archive_term instanceof WP_Term && function_exists('spek_i18n_translate_term_object')) {
                        $archive_term = spek_i18n_translate_term_object($archive_term);
                    }
                    echo esc_html($archive_term instanceof WP_Term ? $archive_term->name : single_term_title('', false));
                } else {
                    esc_html_e('Προϊόντα SPEK', 'spek-theme');
                }
            ?></h1>
            <p>
                <?php esc_html_e('Ανακαλύψτε τη γκάμα προϊόντων SPEK μέσα από κατηγορίες, τεχνικά χαρακτηριστικά και εύκολη αναζήτηση.', 'spek-theme'); ?>
            </p>
        </div>
    </section>

    <section class="section products-listing">
        <div class="container products-archive__container products-layout">

            <aside class="products-sidebar">
                <?php get_template_part('template-parts/products/product-filters'); ?>
            </aside>

            <div class="products-content">
                <div class="products-toolbar">
                    <div class="products-toolbar__copy">
                        <span><?php esc_html_e('Κατάλογος προϊόντων', 'spek-theme'); ?></span>
                        <strong>
                            <?php
                            printf(
                                esc_html(_n('%s προϊόν', '%s προϊόντα', $wp_query->found_posts, 'spek-theme')),
                                esc_html(number_format_i18n($wp_query->found_posts))
                            );
                            ?>
                        </strong>
                    </div>
                </div>

                <?php if (have_posts()) : ?>
                    <div class="products-grid">
                        <?php while (have_posts()) : the_post(); ?>
                            <?php get_template_part('template-parts/products/product-card'); ?>
                        <?php endwhile; ?>
                    </div>

                    <div class="pagination">
                        <?php
                        $pagination_args = [];

                        foreach (['product_search', 'filter_category_group', 'filter_category', 'filter_application', 'filter_material', 'filter_series', 'home_category_group'] as $param) {
                            if (!isset($_GET[$param]) || $_GET[$param] === '') {
                                continue;
                            }

                            $raw_value = wp_unslash($_GET[$param]);

                            if (is_array($raw_value)) {
                                if ($param === 'filter_category_group') {
                                    $clean_values = array_values(array_filter(array_map(
                                        static fn ($value) => is_array($value) ? '' : sanitize_key((string) $value),
                                        $raw_value
                                    )));
                                } else {
                                    $clean_values = array_values(array_filter(array_map('absint', $raw_value)));
                                }

                                if ($clean_values) {
                                    $pagination_args[$param] = $clean_values;
                                }
                                continue;
                            }

                            $pagination_args[$param] = $param === 'home_category_group'
                                ? sanitize_key((string) $raw_value)
                                : sanitize_text_field((string) $raw_value);
                        }

                        the_posts_pagination([
                            'mid_size' => 2,
                            'prev_text' => __('Προηγούμενα', 'spek-theme'),
                            'next_text' => __('Επόμενα', 'spek-theme'),
                            'add_args' => $pagination_args,
                        ]);
                        ?>
                    </div>
                <?php else : ?>
                    <div class="empty-state">
                        <h2><?php esc_html_e('Δεν βρέθηκαν προϊόντα.', 'spek-theme'); ?></h2>
                        <p><?php esc_html_e('Δοκιμάστε να αλλάξετε κατηγορία ή φίλτρα αναζήτησης.', 'spek-theme'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <?php get_template_part('template-parts/global/cta-strip'); ?>

</main>

<?php
get_footer();
