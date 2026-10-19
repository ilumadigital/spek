<?php
/**
 * Template Name: SPEK Legal Pages
 * Accessible two-column corporate layout for editable WP page drafts.
 */
if (!defined('ABSPATH')) { exit; }

get_header();
$lang = function_exists('spek_current_language') ? spek_current_language() : 'el';
$is_english = $lang === 'en';
$slug = get_post_field('post_name', get_queried_object_id());
$definitions = function_exists('spek_legal_page_definitions') ? spek_legal_page_definitions() : [];
$definition = $definitions[$slug] ?? [];
$heading = $definition[$is_english ? 'en' : 'el'] ?? get_the_title();
$eyebrow = $definition['eyebrow'] ?? 'LEGAL INFORMATION';
$intro = $is_english
    ? 'Clear information about our website, your personal data and your rights.'
    : 'Σαφείς πληροφορίες για τον ιστότοπο, τα προσωπικά σας δεδομένα και τα δικαιώματά σας.';
?>
<main id="main" class="site-main spek-legal-page">
    <section class="spek-legal-hero" aria-labelledby="spek-legal-title">
        <div class="container">
            <div class="spek-legal-hero__inner">
                <span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
                <h1 id="spek-legal-title"><?php echo esc_html($heading); ?></h1>
                <p><?php echo esc_html($intro); ?></p>
            </div>
        </div>
    </section>
    <div class="container spek-legal-layout">
        <aside class="spek-legal-sidebar" aria-label="<?php echo esc_attr($is_english ? 'Legal pages' : 'Νομικές σελίδες'); ?>">
            <span class="spek-legal-sidebar__label"><?php echo esc_html($is_english ? 'LEGAL INFORMATION' : 'ΝΟΜΙΚΕΣ ΠΛΗΡΟΦΟΡΙΕΣ'); ?></span>
            <nav class="spek-legal-links">
                <?php foreach ($definitions as $link_slug => $titles) :
                    $url = spek_legal_published_page_url($link_slug);
                    // When previewing drafts, only the current page is previewable.
                    if (!$url && $slug !== $link_slug) { continue; }
                    $url = $url ?: get_preview_post_link(get_queried_object_id());
                    ?>
                    <a class="<?php echo $link_slug === $slug ? 'is-current' : ''; ?>"
                       <?php if ($link_slug === $slug) : ?>aria-current="page"<?php endif; ?>
                       href="<?php echo esc_url($url); ?>">
                        <?php echo esc_html($titles[$is_english ? 'en' : 'el']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <a class="spek-legal-contact" href="<?php echo esc_url(spek_page_url('contact/')); ?>">
                <?php echo esc_html($is_english ? 'Contact our team' : 'Επικοινωνήστε μαζί μας'); ?> <span aria-hidden="true">↗</span>
            </a>
        </aside>
        <article class="spek-legal-article">
            <?php while (have_posts()) : the_post(); ?>
                <?php if (get_post_meta(get_the_ID(), '_spek_legal_review_required', true) === '1' && current_user_can('edit_post', get_the_ID())) : ?>
                    <p class="spek-legal-review-note" role="status"><?php echo esc_html($is_english
                        ? 'REVIEW DRAFT — Verify all flagged details before publishing.'
                        : 'ΠΡΟΣΧΕΔΙΟ ΠΡΟΣ ΕΛΕΓΧΟ — Επιβεβαιώστε όλα τα σημειωμένα στοιχεία πριν από τη δημοσίευση.'); ?></p>
                <?php endif; ?>
                <div class="spek-legal-article__content"><?php the_content(); ?></div>
            <?php endwhile; ?>
            <div class="spek-legal-article__end">
                <span aria-hidden="true">SPEK /</span>
                <a href="<?php echo esc_url(spek_page_url('contact/')); ?>">
                    <?php echo esc_html($is_english ? 'Questions? Get in touch' : 'Έχετε απορίες; Επικοινωνήστε μαζί μας'); ?> ↗
                </a>
            </div>
        </article>
    </div>
</main>
<?php get_footer(); ?>
