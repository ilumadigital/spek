<?php
/** Language-filtered WordPress search results. @package SpekTheme */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="main" class="site-main">
    <section class="page-hero"><div class="container">
        <h1><?php printf(esc_html__('Αποτελέσματα αναζήτησης: %s', 'spek-theme'), esc_html(get_search_query())); ?></h1>
        <?php get_search_form(); ?>
    </div></section>
    <section class="section"><div class="container">
        <?php if (have_posts()) : ?>
            <div class="products-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <?php if (get_post_type() === 'spek_product') : ?>
                        <?php get_template_part('template-parts/products/product-card'); ?>
                    <?php else : ?>
                        <article><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(['prev_text' => __('Προηγούμενα', 'spek-theme'), 'next_text' => __('Επόμενα', 'spek-theme')]); ?>
        <?php else : ?>
            <p><?php esc_html_e('Δεν βρέθηκε περιεχόμενο.', 'spek-theme'); ?></p>
        <?php endif; ?>
    </div></section>
</main>
<?php get_footer(); ?>
