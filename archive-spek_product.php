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

$archive_category_groups = function_exists('spek_home_category_group_definitions')
    ? spek_home_category_group_definitions()
    : [];

$archive_category_icons = [
    'mixanismoi' => 'mechanisms',
    'floter' => 'float',
    'sifonia' => 'siphon',
    'kalymmata-lekanis' => 'seat',
    'kazanakia' => 'cistern',
    'banio' => 'bathroom',
    'lastixa' => 'seals',
    'diafora' => 'misc',
];
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

    <?php if (!empty($archive_category_groups)) : ?>
        <section class="product-category-nav" aria-label="<?php esc_attr_e('Κατηγορίες προϊόντων', 'spek-theme'); ?>">
            <div class="container products-archive__container product-category-nav__shell">
                <div class="product-category-nav__head">
                    <div>
                        <span><?php esc_html_e('Προϊόντα', 'spek-theme'); ?></span>
                        <strong><?php esc_html_e('Κατηγορίες προϊόντων', 'spek-theme'); ?></strong>
                    </div>

                    <a
                        href="<?php echo esc_url($products_archive_url); ?>"
                        class="product-category-all <?php echo $home_category_group === '' && !is_tax() ? 'is-active' : ''; ?>"
                        <?php echo $home_category_group === '' && !is_tax() ? 'aria-current="page"' : ''; ?>
                    >
                        <?php esc_html_e('Όλα τα προϊόντα', 'spek-theme'); ?>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="product-category-nav__grid">
                    <?php foreach ($archive_category_groups as $group_key => $group) : ?>
                        <?php
                        $category_url = add_query_arg('home_category_group', $group_key, $products_archive_url);
                        $is_active_group = $home_category_group === $group_key;
                        $icon = $archive_category_icons[$group_key] ?? 'misc';
                        ?>

                        <a
                            href="<?php echo esc_url($category_url); ?>"
                            class="product-category-tile <?php echo $is_active_group ? 'is-active' : ''; ?>"
                            <?php echo $is_active_group ? 'aria-current="page"' : ''; ?>
                        >
                            <span class="product-category-tile__icon" aria-hidden="true">
                                <?php if (function_exists('spek_header_product_icon')) : ?>
                                    <?php spek_header_product_icon($icon); ?>
                                <?php else : ?>
                                    <span>•</span>
                                <?php endif; ?>
                            </span>
                            <span class="product-category-tile__label"><?php echo esc_html((string) ($group['label'] ?? $group_key)); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

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
                        foreach (['product_search', 'filter_category', 'filter_application', 'filter_material', 'filter_series', 'home_category_group'] as $param) {
                            if (!isset($_GET[$param]) || $_GET[$param] === '') { continue; }
                            $pagination_args[$param] = sanitize_text_field(wp_unslash((string) $_GET[$param]));
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
