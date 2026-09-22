<?php
/**
 * Default page template.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main" class="site-main default-page">

    <?php while (have_posts()) : the_post(); ?>

        <section class="page-hero">
            <div class="container">
                <span class="eyebrow"><?php esc_html_e('SPEK', 'spek-theme'); ?></span>
                <h1><?php the_title(); ?></h1>
            </div>
        </section>

        <section class="section">
            <div class="container content-area">
                <?php the_content(); ?>
            </div>
        </section>

    <?php endwhile; ?>

</main>

<?php
get_footer();