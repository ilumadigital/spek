<?php
/**
 * Partner locator filters.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$partner_count = new WP_Query(spek_language_args(['post_type' => 'spek_partner', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids']));
$published_count = (int) $partner_count->found_posts;
?>

<div class="partner-locator" data-partner-locator>
    <div class="partner-locator__intro">
        <span class="eyebrow"><?php esc_html_e('Σημεία Πώλησης', 'spek-theme'); ?></span>
        <h2><?php esc_html_e('Βρείτε κατάστημα', 'spek-theme'); ?></h2>
        <p>
            <?php
            printf(
                esc_html__('Αναζήτηση ανά περιοχή ή ΤΚ σε %d καταχωρημένα σημεία.', 'spek-theme'),
                $published_count
            );
            ?>
        </p>
    </div>

    <div class="partner-locator__form">
        <div class="partner-locator__field partner-locator__field--search">
            <label for="partner-location-search"><?php esc_html_e('Περιοχή ή ΤΚ', 'spek-theme'); ?></label>
            <input
                id="partner-location-search"
                type="search"
                data-partner-search
                placeholder="<?php esc_attr_e('π.χ. Αθήνα, Αγ. Δημήτριος, 12242', 'spek-theme'); ?>"
                autocomplete="postal-code"
            >
        </div>

        <div class="partner-locator__field partner-locator__field--radius">
            <label for="partner-radius"><?php esc_html_e('Ακτίνα', 'spek-theme'); ?></label>
            <select id="partner-radius" data-partner-radius>
                <option value="5">5 km</option>
                <option value="10" selected>10 km</option>
                <option value="25">25 km</option>
                <option value="50">50 km</option>
                <option value="100">100 km</option>
            </select>
        </div>

        <div class="partner-locator__field partner-locator__field--button">
            <button type="button" class="button button-primary partner-locator__submit" data-partner-submit>
                <?php esc_html_e('Αναζήτηση', 'spek-theme'); ?>
            </button>
        </div>
    </div>

    <div class="partner-locator__quick-actions">
        <button type="button" class="partner-locator__link-button" data-partner-use-location>
            <?php esc_html_e('Χρήση της τοποθεσίας μου', 'spek-theme'); ?>
        </button>

        <button type="button" class="partner-locator__link-button" data-partner-reset>
            <?php esc_html_e('Καθαρισμός', 'spek-theme'); ?>
        </button>
    </div>

    <p class="partner-locator__status" data-partner-status>
        <?php esc_html_e('Συμπληρώστε περιοχή ή ΤΚ για να εμφανιστούν αποτελέσματα.', 'spek-theme'); ?>
    </p>
</div>