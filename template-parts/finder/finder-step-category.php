<?php
/**
 * Product finder category step.
 *
 * The public catalogue is organised in the same eight curated category groups
 * used by the homepage and Products filters. The finder must use those groups
 * too — especially "Διάφορα", which is a virtual group rather than one native
 * taxonomy term.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$category_groups = function_exists('spek_home_category_group_definitions')
    ? spek_home_category_group_definitions()
    : [];

$category_counts = [];

foreach ($category_groups as $group_key => $group) {
    $term_ids = function_exists('spek_product_category_group_term_ids')
        ? spek_product_category_group_term_ids([$group_key])
        : [];

    if (!$term_ids) {
        $category_counts[$group_key] = 0;
        continue;
    }

    $query = new WP_Query([
        'post_type' => 'spek_product',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => false,
        'suppress_filters' => false,
        'tax_query' => [
            [
                'taxonomy' => 'product_category',
                'field' => 'term_id',
                'terms' => array_values(array_map('intval', $term_ids)),
                'operator' => 'IN',
            ],
        ],
    ]);

    $category_counts[$group_key] = (int) $query->found_posts;
}
?>

<div class="spek-finder-panel" data-step="category">
    <div class="spek-finder__head">
        <div class="spek-finder__step-label">
            <span>01</span>
            <small><?php esc_html_e('Βήμα 1 από 3', 'spek-theme'); ?></small>
        </div>
        <h2><?php esc_html_e('Τι προϊόν ψάχνετε;', 'spek-theme'); ?></h2>
        <p><?php esc_html_e('Επιλέξτε τη βασική κατηγορία. Εμφανίζονται μόνο κατηγορίες που έχουν διαθέσιμα προϊόντα.', 'spek-theme'); ?></p>
    </div>

    <div class="spek-finder-options spek-finder-options--cards">
        <?php
        $visible_index = 0;
        foreach ($category_groups as $group_key => $group) :
            $count = (int) ($category_counts[$group_key] ?? 0);
            if ($count < 1) {
                continue;
            }
            $visible_index++;
            $is_seat_cover = $group_key === 'kalymmata-lekanis';
            $label = (string) ($group['label'] ?? $group_key);
            ?>
            <label class="spek-finder-option">
                <input
                    type="radio"
                    name="product_category"
                    value="<?php echo esc_attr($group_key); ?>"
                    data-label="<?php echo esc_attr($label); ?>"
                    data-category-group="1"
                    data-seat-cover="<?php echo $is_seat_cover ? '1' : '0'; ?>"
                >
                <span>
                    <i class="spek-finder-option__index"><?php echo esc_html(str_pad((string) $visible_index, 2, '0', STR_PAD_LEFT)); ?></i>
                    <span class="spek-finder-option__check" aria-hidden="true">✓</span>
                    <strong><?php echo esc_html($label); ?></strong>
                    <em><?php echo esc_html(sprintf(_n('%d προϊόν', '%d προϊόντα', $count, 'spek-theme'), $count)); ?></em>
                </span>
            </label>
        <?php endforeach; ?>

        <?php if ($visible_index === 0) : ?>
            <p class="spek-finder-empty"><?php esc_html_e('Δεν υπάρχουν ακόμη κατηγορίες προϊόντων.', 'spek-theme'); ?></p>
        <?php endif; ?>
    </div>

    <div class="spek-finder-actions">
        <button class="button button-secondary spek-finder-prev" type="button" data-prev-step="start">
            <span aria-hidden="true">←</span><?php esc_html_e('Πίσω', 'spek-theme'); ?>
        </button>
        <button class="button button-primary spek-finder-next" type="button" data-next-step="application" data-requires-selection="product_category" data-category-next disabled>
            <?php esc_html_e('Συνέχεια', 'spek-theme'); ?><span aria-hidden="true">→</span>
        </button>
    </div>
</div>
