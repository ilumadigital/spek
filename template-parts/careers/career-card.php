<?php
/**
 * Career position card.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$position_id = get_the_ID();
$apply_url = add_query_arg('career_position', $position_id, spek_page_url('careers/')) . '#career-application';
$summary = has_excerpt()
    ? get_the_excerpt()
    : wp_trim_words(wp_strip_all_tags(get_the_content()), 34);
?>

<article class="career-position-card" data-reveal>
    <div class="career-position-card__meta">
        <span><?php esc_html_e('Ανοιχτή θέση', 'spek-theme'); ?></span>
        <span aria-hidden="true">·</span>
        <span>SPEK</span>
    </div>

    <h2><?php the_title(); ?></h2>

    <?php if ($summary !== '') : ?>
        <p><?php echo esc_html($summary); ?></p>
    <?php endif; ?>

    <div class="career-position-card__actions">
        <a class="button button-primary" href="<?php echo esc_url($apply_url); ?>">
            <?php esc_html_e('Υποβολή αίτησης', 'spek-theme'); ?>
        </a>
    </div>
</article>
