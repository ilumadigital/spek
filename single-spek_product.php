<?php
/**
 * Single product template.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$product_id = get_the_ID();
$product_code = function_exists('spek_get_product_field')
    ? spek_get_product_field('product_code', $product_id)
    : get_post_meta($product_id, 'product_code', true);
$product_categories = get_the_terms($product_id, 'product_category');
$products_url = get_post_type_archive_link('spek_product') ?: spek_page_url('products/');
$short_description = function_exists('spek_get_product_field')
    ? spek_get_product_field('product_short_description', $product_id)
    : get_post_meta($product_id, 'product_short_description', true);

$product_title = get_the_title($product_id);
$contact_args = [
    'product_id' => $product_id,
    'product'    => $product_title,
];

if (!spek_product_value_is_empty($product_code)) {
    $contact_args['product_code'] = spek_product_value_to_text($product_code);
}

$contact_url = add_query_arg($contact_args, spek_page_url('contact/')) . '#contact-form';
$primary_category = (!empty($product_categories) && !is_wp_error($product_categories))
    ? reset($product_categories)
    : null;

$product_manufacturers = get_the_terms($product_id, 'product_series');
$manufacturer_name = 'SPEK';
$manufacturer_key = 'spek';
$manufacturer_logo = get_template_directory_uri() . '/assets/images/brand/spek_logo.png';

$manufacturer_specs = [
    'spek' => [
        'patterns' => ['spek'],
        'logo' => get_template_directory_uri() . '/assets/images/brand/spek_logo.png',
    ],
    'nemo' => [
        'patterns' => ['nemo'],
        'logo' => get_template_directory_uri() . '/assets/images/brand/nemo_logo.svg',
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

$normalize_manufacturer = static function (string $value): string {
    $value = remove_accents(wp_strip_all_tags($value));
    $value = function_exists('mb_strtolower')
        ? mb_strtolower($value, 'UTF-8')
        : strtolower($value);

    $value = preg_replace('/[^\\p{L}\\p{N}]+/u', ' ', $value);

    return trim((string) $value);
};

if (!empty($product_manufacturers) && !is_wp_error($product_manufacturers)) {
    $fallback_manufacturer = reset($product_manufacturers);

    if ($fallback_manufacturer instanceof WP_Term) {
        $manufacturer_name = $fallback_manufacturer->name;
        $manufacturer_key = 'other';
        $manufacturer_logo = '';
    }

    foreach ($product_manufacturers as $manufacturer_term) {
        $haystack = $normalize_manufacturer($manufacturer_term->name . ' ' . $manufacturer_term->slug);

        foreach ($manufacturer_specs as $brand_key => $spec) {
            foreach ($spec['patterns'] as $pattern) {
                $pattern = $normalize_manufacturer((string) $pattern);

                if ($pattern !== '' && strpos($haystack, $pattern) !== false) {
                    $manufacturer_name = $manufacturer_term->name;
                    $manufacturer_key = $brand_key;
                    $manufacturer_logo = $spec['logo'];
                    break 3;
                }
            }
        }
    }
}
?>

<main id="main" class="site-main single-product-page">

    <?php while (have_posts()) : the_post(); ?>

        <section class="single-product-hero single-product-hero--premium">
            <div class="container">

                <nav class="single-product-breadcrumbs" aria-label="<?php esc_attr_e('Πλοήγηση προϊόντος', 'spek-theme'); ?>">
                    <a href="<?php echo esc_url($products_url); ?>">
                        <?php esc_html_e('Προϊόντα', 'spek-theme'); ?>
                    </a>

                    <?php if ($primary_category instanceof WP_Term) : ?>
                        <span aria-hidden="true">/</span>
                        <a href="<?php echo esc_url(get_term_link($primary_category)); ?>">
                            <?php echo esc_html($primary_category->name); ?>
                        </a>
                    <?php endif; ?>

                    <span aria-hidden="true">/</span>
                    <strong><?php the_title(); ?></strong>
                </nav>

                <div class="single-product-hero__grid">

                    <div class="single-product-visual">
                        <?php get_template_part('template-parts/products/product-gallery'); ?>
                    </div>

                    <div class="single-product-summary">
                        <span
                            class="eyebrow single-product-brand single-product-manufacturer"
                            aria-label="<?php echo esc_attr(sprintf(__('Κατασκευαστής: %s', 'spek-theme'), $manufacturer_name)); ?>"
                        >
                            <?php if ($manufacturer_logo !== '') : ?>
                                <img
                                    src="<?php echo esc_url($manufacturer_logo); ?>"
                                    alt="<?php echo esc_attr($manufacturer_name); ?>"
                                    class="single-product-manufacturer__logo single-product-manufacturer__logo--<?php echo esc_attr($manufacturer_key); ?>"
                                >
                            <?php else : ?>
                                <span class="single-product-manufacturer__name">
                                    <?php echo esc_html($manufacturer_name); ?>
                                </span>
                            <?php endif; ?>
                        </span>

                        <?php if (!empty($product_categories) && !is_wp_error($product_categories)) : ?>
                            <div class="product-taxonomy-links product-taxonomy-links--hero" aria-label="<?php esc_attr_e('Κατηγορίες προϊόντος', 'spek-theme'); ?>">
                                <?php foreach ($product_categories as $category) : ?>
                                    <a class="product-taxonomy-link" href="<?php echo esc_url(get_term_link($category)); ?>">
                                        <?php echo esc_html($category->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <h1><?php the_title(); ?></h1>

                        <?php if (!spek_product_value_is_empty($product_code)) : ?>
                            <div class="product-identity-card">
                                <span class="product-identity-card__label">
                                    <?php esc_html_e('Κωδικός προϊόντος', 'spek-theme'); ?>
                                </span>

                                <a
                                    class="product-identity-card__value"
                                    href="<?php echo esc_url(add_query_arg('product_search', (string) $product_code, $products_url)); ?>"
                                >
                                    <?php echo esc_html(spek_product_value_to_text($product_code)); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (!spek_product_value_is_empty($short_description)) : ?>
                            <p class="single-product-summary__excerpt">
                                <?php echo esc_html(spek_product_value_to_text($short_description)); ?>
                            </p>
                        <?php endif; ?>

                        <div class="single-product-actions">
                            <a href="<?php echo esc_url($contact_url); ?>" class="button button-primary single-product-action single-product-action--primary">
                                <span><?php esc_html_e('Επικοινωνήστε μαζί μας', 'spek-theme'); ?></span>
                                <span class="single-product-action__icon" aria-hidden="true">→</span>
                            </a>

                            <a href="<?php echo esc_url(spek_page_url('partners/')); ?>" class="button button-secondary single-product-action">
                                <span><?php esc_html_e('Πού θα το βρείτε', 'spek-theme'); ?></span>
                                <span class="single-product-action__icon" aria-hidden="true">↗</span>
                            </a>
                        </div>

                        <div class="single-product-summary__footnote">
                            <span aria-hidden="true"></span>
                            <p><?php esc_html_e('Για τεχνικές πληροφορίες, διαθεσιμότητα ή εμπορική υποστήριξη, επικοινωνήστε με την ομάδα της SPEK.', 'spek-theme'); ?></p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section class="section product-main-content">
            <div class="container">

                <header class="product-main-content__header">
                    <div>
                        <span class="eyebrow"><?php esc_html_e('Στοιχεία προϊόντος', 'spek-theme'); ?></span>
                        <h2><?php esc_html_e('Πληροφορίες προϊόντος', 'spek-theme'); ?></h2>
                    </div>

                    <?php if (!spek_product_value_is_empty($product_code)) : ?>
                        <p class="product-main-content__reference">
                            <span><?php esc_html_e('Κωδικός', 'spek-theme'); ?></span>
                            <strong><?php echo esc_html(spek_product_value_to_text($product_code)); ?></strong>
                        </p>
                    <?php endif; ?>
                </header>

                <div class="product-main-content__grid">
                    <article class="product-description">
                        <div class="product-description__header">
                            <span class="eyebrow"><?php esc_html_e('Περιγραφή', 'spek-theme'); ?></span>
                            <h3><?php esc_html_e('Περιγραφή προϊόντος', 'spek-theme'); ?></h3>
                        </div>

                        <div class="content-area">
                            <?php if (trim((string) get_the_content()) !== '') : ?>
                                <?php the_content(); ?>
                            <?php else : ?>
                                <p><?php esc_html_e('Δεν έχει καταχωρηθεί αναλυτική περιγραφή για αυτό το προϊόν.', 'spek-theme'); ?></p>
                            <?php endif; ?>
                        </div>
                    </article>

                    <?php get_template_part('template-parts/products/product-specs'); ?>
                </div>

            </div>
        </section>

        <?php get_template_part('template-parts/products/product-media'); ?>

        <?php get_template_part('template-parts/products/product-downloads'); ?>

        <?php get_template_part('template-parts/products/related-products'); ?>

    <?php endwhile; ?>

</main>

<?php
get_footer();
