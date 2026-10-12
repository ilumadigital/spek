<?php
/**
 * Partner searchable cards.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$partners_query = new WP_Query(spek_language_args([
    'post_type'      => 'spek_partner',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
]));
?>

<div class="partner-results" data-partner-results>
    <div class="partner-results__empty" data-partner-empty>
        <h3><?php esc_html_e('Ξεκινήστε αναζήτηση', 'spek-theme'); ?></h3>
        <p><?php esc_html_e('Πληκτρολογήστε περιοχή ή ΤΚ και επιλέξτε ακτίνα για να εμφανιστούν μόνο τα σχετικά σημεία πώλησης.', 'spek-theme'); ?></p>
    </div>

    <?php if ($partners_query->have_posts()) : ?>
        <?php while ($partners_query->have_posts()) : ?>
            <?php
            $partners_query->the_post();

            $partner_id = get_the_ID();

            $address        = get_post_meta($partner_id, 'partner_address', true);
            $area           = get_post_meta($partner_id, 'partner_area', true);
            $postcode       = get_post_meta($partner_id, 'partner_postcode', true);
            $phone          = get_post_meta($partner_id, 'partner_phone', true);
            $mobile         = get_post_meta($partner_id, 'partner_mobile', true);
            $email          = get_post_meta($partner_id, 'partner_email', true);
            $website        = get_post_meta($partner_id, 'partner_website', true);
            $contact_person = get_post_meta($partner_id, 'partner_contact_person', true);
            $maps_url       = get_post_meta($partner_id, 'partner_maps_url', true);
            $latitude       = get_post_meta($partner_id, 'partner_latitude', true);
            $longitude      = get_post_meta($partner_id, 'partner_longitude', true);
            $google_place_id = get_post_meta($partner_id, '_spek_partner_google_place_id', true);

            $phone_values = preg_split('/\s*(?:\/|\||,|;)\s*/u', (string) $phone);
            $phone_values = array_values(array_filter(array_map('trim', is_array($phone_values) ? $phone_values : [])));

            /*
             * Fallback:
             * If lat/lng are empty but the Google Maps URL contains coordinates,
             * extract them and save them automatically.
             */
            if ((!$latitude || !$longitude) && $maps_url && function_exists('spek_extract_lat_lng_from_maps_url')) {
                $coordinates = spek_extract_lat_lng_from_maps_url($maps_url);

                if (!empty($coordinates['latitude']) && !empty($coordinates['longitude'])) {
                    $latitude  = $coordinates['latitude'];
                    $longitude = $coordinates['longitude'];
                }
            }

            $partner_types   = get_the_terms($partner_id, 'partner_type');
            $partner_regions = get_the_terms($partner_id, 'partner_region');

            $region_names = [];
            $type_names   = [];

            if ($partner_regions && !is_wp_error($partner_regions)) {
                $region_names = wp_list_pluck($partner_regions, 'name');
            }

            if ($partner_types && !is_wp_error($partner_types)) {
                $type_names = wp_list_pluck($partner_types, 'name');
            }

            $search_text = implode(' ', array_filter([
                get_the_title(),
                $address,
                $area,
                $postcode,
                $phone,
                $mobile,
                $email,
                $website,
                $maps_url,
                implode(' ', $region_names),
                implode(' ', $type_names),
            ]));
            ?>

            <article
                class="partner-card"
                data-partner-card
                data-partner-id="<?php echo esc_attr((string) $partner_id); ?>"
                data-title="<?php echo esc_attr(get_the_title()); ?>"
                data-area="<?php echo esc_attr($area); ?>"
                data-postcode="<?php echo esc_attr($postcode); ?>"
                data-address="<?php echo esc_attr($address); ?>"
                data-lat="<?php echo esc_attr($latitude); ?>"
                data-lng="<?php echo esc_attr($longitude); ?>"
                data-maps-url="<?php echo esc_url($maps_url); ?>"
                data-place-id="<?php echo esc_attr($google_place_id); ?>"
                data-search="<?php echo esc_attr($search_text); ?>"
            >
                <div class="partner-card__header">
                    <div>
                        <h2 class="partner-card__title"><?php the_title(); ?></h2>

                        <?php if (!empty($type_names)) : ?>
                            <div class="partner-card__tags">
                                <?php foreach ($type_names as $type_name) : ?>
                                    <span><?php echo esc_html($type_name); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($area) : ?>
                        <span class="partner-card__area"><?php echo esc_html($area); ?></span>
                    <?php endif; ?>
                </div>

                <div class="partner-card__details">
                    <?php if ($address || $postcode || $area) : ?>
                        <div class="partner-card__detail">
                            <strong><?php esc_html_e('Διεύθυνση', 'spek-theme'); ?></strong>
                            <span>
                                <?php echo esc_html(implode(', ', array_filter([$address, $postcode, $area]))); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($phone_values)) : ?>
                        <div class="partner-card__detail">
                            <strong><?php esc_html_e('Τηλέφωνο', 'spek-theme'); ?></strong>
                            <span>
                                <?php foreach ($phone_values as $phone_index => $phone_value) : ?>
                                    <?php if ($phone_index > 0) : ?> / <?php endif; ?>
                                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_value)); ?>">
                                        <?php echo esc_html($phone_value); ?>
                                    </a>
                                <?php endforeach; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if ($mobile) : ?>
                        <div class="partner-card__detail">
                            <strong><?php esc_html_e('Κινητό', 'spek-theme'); ?></strong>
                            <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $mobile)); ?>">
                                <?php echo esc_html($mobile); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($email) : ?>
                        <div class="partner-card__detail">
                            <strong><?php esc_html_e('Ηλ. ταχυδρομείο', 'spek-theme'); ?></strong>
                            <a href="mailto:<?php echo esc_attr($email); ?>">
                                <?php echo esc_html($email); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($contact_person) : ?>
                        <div class="partner-card__detail">
                            <strong><?php esc_html_e('Υπεύθυνος', 'spek-theme'); ?></strong>
                            <span><?php echo esc_html($contact_person); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($maps_url || $website) : ?>
                    <div class="partner-card__actions">
                        <?php if ($maps_url) : ?>
                            <a href="<?php echo esc_url($maps_url); ?>" target="_blank" rel="noopener" class="button button-small button-primary">
                                <?php esc_html_e('Οδηγίες', 'spek-theme'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($website) : ?>
                            <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener" class="button button-small button-secondary">
                                <?php esc_html_e('Ιστοσελίδα', 'spek-theme'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endwhile; ?>

        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <div class="empty-state">
            <h2><?php esc_html_e('Δεν υπάρχουν ακόμα καταχωρημένα σημεία πώλησης.', 'spek-theme'); ?></h2>
            <p><?php esc_html_e('Προσθέστε συνεργάτες από τη διαχείριση WordPress ώστε να εμφανιστούν εδώ.', 'spek-theme'); ?></p>
        </div>
    <?php endif; ?>
</div>