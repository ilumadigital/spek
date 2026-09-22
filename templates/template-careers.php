<?php
/**
 * Template Name: Careers
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

    <section class="page-hero">
        <div class="container">
            <span class="eyebrow"><?php esc_html_e('Careers', 'spek-theme'); ?></span>
            <h1><?php esc_html_e('Καριέρα στη SPEK', 'spek-theme'); ?></h1>
            <p><?php esc_html_e('Ανακαλύψτε διαθέσιμες θέσεις εργασίας και στείλτε μας το βιογραφικό σας.', 'spek-theme'); ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container careers-grid">

            <?php if ($careers->have_posts()) : ?>
                <?php while ($careers->have_posts()) : $careers->the_post(); ?>
                    <?php get_template_part('template-parts/careers/career-card'); ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="empty-state">
                    <h2><?php esc_html_e('Δεν υπάρχουν ανοιχτές θέσεις αυτή τη στιγμή.', 'spek-theme'); ?></h2>
                    <p><?php esc_html_e('Μπορείτε να μας στείλετε το βιογραφικό σας για μελλοντική αξιολόγηση.', 'spek-theme'); ?></p>
                </div>
            <?php endif; ?>

            <?php get_template_part('template-parts/careers/application-form'); ?>

        </div>
    </section>

</main>

<?php
get_footer();