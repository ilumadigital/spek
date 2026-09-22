<?php
/**
 * Main fallback template.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main" class="site-main">

    <section class="page-hero">
        <div class="container">
            <span class="eyebrow"><?php esc_html_e('SPEK', 'spek-theme'); ?></span>
            <h1><?php bloginfo('name'); ?></h1>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article>
                        <h2>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h2>

                        <?php the_excerpt(); ?>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php esc_html_e('Δεν βρέθηκε περιεχόμενο.', 'spek-theme'); ?></p>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php
get_footer();