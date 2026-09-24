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
    'post_type'      => 'spek_catalogue',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]));
?>

<main id="main" class="site-main catalogues-page catalogues-library">

    <section class="catalogues-library__hero">
        <div class="container">
            <span class="eyebrow"><?php esc_html_e('Κατάλογος', 'spek-theme'); ?></span>
            <h1><?php esc_html_e('Κατάλογος Προϊόντων', 'spek-theme'); ?></h1>
            <p><?php esc_html_e('Ο επίσημος κατάλογος SPEK, διαθέσιμος για online προβολή και λήψη PDF.', 'spek-theme'); ?></p>
        </div>
    </section>

    <section class="catalogues-library__section">
        <div class="container">

            <?php if ($catalogues->have_posts()) : ?>
                <div class="catalogues-library__top" data-reveal>
                    <span><?php esc_html_e('Κατάλογος SPEK', 'spek-theme'); ?></span>
                    <strong>
                        <?php
                        printf(
                            esc_html__('%d κατάλογος', 'spek-theme'),
                            (int) $catalogues->post_count
                        );
                        ?>
                    </strong>
                </div>

                <div class="catalogue-grid catalogue-grid--covers">
                    <?php while ($catalogues->have_posts()) : $catalogues->the_post(); ?>
                        <?php
                        $catalogue_id = get_the_ID();
                        $pdf_url = function_exists('spek_get_catalogue_pdf_url')
                            ? spek_get_catalogue_pdf_url($catalogue_id)
                            : '';
                        ?>
                        <article class="catalogue-book-card" data-reveal>
                            <a href="<?php the_permalink(); ?>" class="catalogue-book-card__link">

                                <div class="catalogue-book-card__preview">
                                    <div
                                        class="catalogue-paper<?php echo $pdf_url !== '' ? ' has-pdf-preview' : ''; ?>"
                                        <?php if ($pdf_url !== '') : ?>
                                            data-catalogue-cover
                                            data-pdf-url="<?php echo esc_url($pdf_url); ?>"
                                            data-pdfjs-url="https://cdn.jsdelivr.net/npm/pdfjs-dist@6.3.289/build/pdf.min.mjs"
                                            data-pdf-worker-url="https://cdn.jsdelivr.net/npm/pdfjs-dist@6.3.289/build/pdf.worker.min.mjs"
                                        <?php endif; ?>
                                    >
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="catalogue-paper__fallback">
                                                <?php the_post_thumbnail('large', ['loading' => 'lazy']); ?>
                                            </div>
                                        <?php else : ?>
                                            <div class="catalogue-paper__fallback catalogue-paper__fallback--empty">
                                                <img
                                                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                                                    alt=""
                                                    loading="lazy"
                                                >
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($pdf_url !== '') : ?>
                                            <canvas class="catalogue-paper__canvas" data-catalogue-cover-canvas aria-hidden="true"></canvas>
                                            <div class="catalogue-paper__loading" data-catalogue-cover-loading>
                                                <span></span>
                                                <small><?php esc_html_e('Προεπισκόπηση PDF', 'spek-theme'); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <span class="catalogue-book-card__corner" aria-hidden="true"></span>

                                    <span class="catalogue-book-card__view">
                                        <?php esc_html_e('Άνοιγμα καταλόγου', 'spek-theme'); ?>
                                        <i aria-hidden="true">↗</i>
                                    </span>
                                </div>

                                <div class="catalogue-book-card__content">
                                    <div class="catalogue-book-card__meta">
                                        <span><?php esc_html_e('PDF Catalogue', 'spek-theme'); ?></span>
                                        <i aria-hidden="true"></i>
                                        <span><?php esc_html_e('A4 Preview', 'spek-theme'); ?></span>
                                    </div>

                                    <h2><?php the_title(); ?></h2>

                                    <?php if (has_excerpt()) : ?>
                                        <p><?php echo esc_html(get_the_excerpt()); ?></p>
                                    <?php endif; ?>

                                    <span class="catalogue-book-card__cta">
                                        <?php esc_html_e('Προβολή καταλόγου', 'spek-theme'); ?>
                                        <span aria-hidden="true">→</span>
                                    </span>
                                </div>

                            </a>
                        </article>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            <?php else : ?>
                <div class="catalogues-library__empty" data-reveal>
                    <p><?php esc_html_e('Δεν υπάρχει διαθέσιμος κατάλογος αυτή τη στιγμή.', 'spek-theme'); ?></p>
                </div>
            <?php endif; ?>

        </div>
    </section>

</main>

<?php
get_footer();
