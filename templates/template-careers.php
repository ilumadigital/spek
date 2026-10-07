<?php
/**
 * Template Name: Καριέρα
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$careers = new WP_Query(spek_language_args([
    'post_type' => 'spek_career',
    'posts_per_page' => -1,
    'post_status' => 'publish',
]));
?>

<main id="main" class="site-main careers-page">

    <section class="page-hero careers-hero">
        <div class="container">
            <span class="eyebrow"><?php esc_html_e('Καριέρα', 'spek-theme'); ?></span>
            <h1><?php esc_html_e('Καριέρα στη SPEK', 'spek-theme'); ?></h1>
            <p><?php esc_html_e('Ανακαλύψτε διαθέσιμες θέσεις εργασίας ή στείλτε μας το βιογραφικό σας για μελλοντικές ευκαιρίες.', 'spek-theme'); ?></p>
        </div>
    </section>

    <section class="section careers-positions-section">
        <div class="container">
            <div class="section-heading careers-positions-heading" data-reveal>
                <span class="eyebrow"><?php esc_html_e('Ανοιχτές θέσεις', 'spek-theme'); ?></span>
                <h2><?php esc_html_e('Ευκαιρίες για να γίνετε μέλος της ομάδας μας.', 'spek-theme'); ?></h2>
                <p><?php esc_html_e('Δείτε τις διαθέσιμες θέσεις ή προχωρήστε σε γενική αίτηση αν δεν υπάρχει αυτή τη στιγμή ρόλος που να ταιριάζει στο προφίλ σας.', 'spek-theme'); ?></p>
            </div>

            <?php if ($careers->have_posts()) : ?>
                <div class="careers-position-grid">
                    <?php while ($careers->have_posts()) : $careers->the_post(); ?>
                        <?php get_template_part('template-parts/careers/career-card'); ?>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="career-empty-state" data-reveal>
                    <span><?php esc_html_e('Αυτή τη στιγμή', 'spek-theme'); ?></span>
                    <h2><?php esc_html_e('Δεν υπάρχουν ανοιχτές θέσεις αυτή τη στιγμή.', 'spek-theme'); ?></h2>
                    <p><?php esc_html_e('Μπορείτε όμως να μας στείλετε το βιογραφικό σας μέσω της γενικής αίτησης για μελλοντική αξιολόγηση.', 'spek-theme'); ?></p>
                    <a class="button button-primary" href="#career-application"><?php esc_html_e('Γενική αίτηση', 'spek-theme'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="section careers-application-section">
        <div class="container">
            <?php get_template_part('template-parts/careers/application-form'); ?>
        </div>
    </section>

</main>

<?php
get_footer();
