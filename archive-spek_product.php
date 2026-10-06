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

$product_search = isset($_GET['product_search']) && !is_array($_GET['product_search'])
    ? sanitize_text_field(wp_unslash((string) $_GET['product_search']))
    : '';

$product_sort = function_exists('spek_product_sort_key') ? spek_product_sort_key() : 'relevance';
$product_sort_options = function_exists('spek_product_sort_options') ? spek_product_sort_options() : [];
$product_per_page = function_exists('spek_product_per_page') ? spek_product_per_page() : 25;
$product_per_page_options = [25, 50, 75, 100];
$product_toolbar_is_english = function_exists('spek_is_english') && spek_is_english();

?>

<main id="main" class="site-main products-archive">

    <section class="page-hero products-archive__hero">
        <div class="container products-archive__container products-archive__hero-inner">
            <div class="products-archive__hero-copy">
            <span class="eyebrow"><?php echo esc_html(function_exists('spek_is_english') && spek_is_english() ? 'Product Catalogue' : 'Κατάλογος Προϊόντων'); ?></span>
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
                    echo esc_html(function_exists('spek_is_english') && spek_is_english() ? 'Products' : 'Προϊόντα');
                }
            ?></h1>
            <p>
                <?php
                echo esc_html(
                    function_exists('spek_is_english') && spek_is_english()
                        ? 'Explore the product range through categories, technical characteristics and easy search.'
                        : 'Ανακαλύψτε τη γκάμα προϊόντων μέσα από κατηγορίες, τεχνικά χαρακτηριστικά και εύκολη αναζήτηση.'
                );
                ?>
            </p>
            </div>

        <?php
        $finder_is_english = function_exists('spek_is_english') && spek_is_english();
        $finder_url = spek_page_url('product-finder/');
        ?>
        <a class="products-hero-finder" href="<?php echo esc_url($finder_url); ?>">
            <span class="products-hero-finder__eyebrow">
                <?php echo esc_html($finder_is_english ? 'Looking for something specific?' : 'Ψάχνετε κάτι συγκεκριμένο;'); ?>
            </span>

            <span class="products-hero-finder__icon" aria-hidden="true">
                <svg viewBox="0 0 48 48">
                    <circle cx="21" cy="21" r="11"></circle>
                    <path d="m29 29 9 9"></path>
                    <path d="M17 21h8M21 17v8"></path>
                </svg>
            </span>

            <span class="products-hero-finder__content">
                <strong><?php echo esc_html($finder_is_english ? 'Guided Product Finder' : 'Καθοδηγούμενη Εύρεση Προϊόντος'); ?></strong>
                <small>
                    <?php
                    echo esc_html(
                        $finder_is_english
                            ? 'Answer a few quick questions and we will narrow the catalogue down to products that actually fit your needs.'
                            : 'Απαντήστε σε λίγες σύντομες ερωτήσεις και θα περιορίσουμε τον κατάλογο μόνο στα προϊόντα που ταιριάζουν πραγματικά στις ανάγκες σας.'
                    );
                    ?>
                </small>
            </span>

            <span class="products-hero-finder__cta">
                <?php echo esc_html($finder_is_english ? 'Start guided finder' : 'Ξεκινήστε την καθοδήγηση'); ?>
                <span aria-hidden="true">→</span>
            </span>
        </a>
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

                    <div class="products-toolbar__controls">
                        <label class="products-toolbar__search" for="product_search">
                            <span class="products-toolbar__search-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="6.5"></circle>
                                    <path d="m16 16 4 4"></path>
                                </svg>
                            </span>
                            <input
                                id="product_search"
                                type="search"
                                name="product_search"
                                form="product-filters-form"
                                value="<?php echo esc_attr($product_search); ?>"
                                placeholder="<?php echo esc_attr($product_toolbar_is_english ? 'Search product or code' : 'Αναζήτηση προϊόντος ή κωδικού'); ?>"
                                autocomplete="off"
                                enterkeyhint="search"
                            >
                        </label>

                        <label class="products-toolbar__field products-toolbar__field--sort">
                            <span><?php echo esc_html($product_toolbar_is_english ? 'Sort' : 'Ταξινόμηση'); ?></span>
                            <select
                                name="product_sort"
                                form="product-filters-form"
                                data-product-sort
                                aria-label="<?php echo esc_attr($product_toolbar_is_english ? 'Sort products' : 'Ταξινόμηση προϊόντων'); ?>"
                            >
                                <?php foreach ($product_sort_options as $sort_key => $sort_label) : ?>
                                    <option value="<?php echo esc_attr($sort_key); ?>" <?php selected($product_sort, $sort_key); ?>>
                                        <?php echo esc_html($sort_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="products-toolbar__field products-toolbar__field--per-page">
                            <span><?php echo esc_html($product_toolbar_is_english ? 'Per page' : 'Ανά σελίδα'); ?></span>
                            <select
                                name="per_page"
                                form="product-filters-form"
                                data-product-per-page
                                aria-label="<?php echo esc_attr($product_toolbar_is_english ? 'Products per page' : 'Προϊόντα ανά σελίδα'); ?>"
                            >
                                <?php foreach ($product_per_page_options as $page_size) : ?>
                                    <option value="<?php echo esc_attr((string) $page_size); ?>" <?php selected($product_per_page, $page_size); ?>>
                                        <?php echo esc_html(number_format_i18n($page_size)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
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

                        foreach (['product_search', 'product_sort', 'per_page', 'filter_category_group', 'filter_category', 'filter_application', 'filter_material', 'filter_series', 'home_category_group'] as $param) {
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

                            if ($param === 'home_category_group' || $param === 'product_sort') {
                                $pagination_args[$param] = sanitize_key((string) $raw_value);
                            } elseif ($param === 'per_page') {
                                $pagination_args[$param] = absint($raw_value);
                            } else {
                                $pagination_args[$param] = sanitize_text_field((string) $raw_value);
                            }
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
