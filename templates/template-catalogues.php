<?php
/**
 * Template Name: Catalogues
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$catalogues = new WP_Query(spek_language_args([
    'post_type' => 'spek_catalogue',
    'posts_per_page' => -1,
    'post_status' => 'publish',
]));
?>

<main id="main" class="site-main catalogues-page">

    <section class="page-hero">
        <div class="container">
            <span class="eyebrow"><?php esc_html_e('Catalogues', 'spek-theme'); ?></span>
            <h1><?php esc_html_e('Κατάλογοι προϊόντων', 'spek-theme'); ?></h1>
            <p><?php esc_html_e('Δείτε ή κατεβάστε τους επίσημους καταλόγους SPEK.', 'spek-theme'); ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container catalogue-grid">

            <?php if ($catalogues->have_posts()) : ?>
                <?php while ($catalogues->have_posts()) : $catalogues->the_post(); ?>
                    <article class="catalogue-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="catalogue-card__image">
                                <?php the_post_thumbnail('spek_card_wide'); ?>
                            </div>
                        <?php endif; ?>

                        <div class="catalogue-card__content">
                            <h2><?php the_title(); ?></h2>
                            <?php if (has_excerpt()) : ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>

                            <a href="<?php the_permalink(); ?>" class="button button-primary">
                                <?php esc_html_e('Προβολή καταλόγου', 'spek-theme'); ?>
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p><?php esc_html_e('Δεν υπάρχουν διαθέσιμοι κατάλογοι αυτή τη στιγμή.', 'spek-theme'); ?></p>
            <?php endif; ?>

        </div>
    </section>

</main>

<?php
get_footer();