<?php
/**
 * Partner admin metaboxes.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Disable block editor for partners.
 */
function spek_disable_block_editor_for_partners($use_block_editor, $post_type)
{
    if ($post_type === 'spek_partner') {
        return false;
    }

    return $use_block_editor;
}

add_filter('use_block_editor_for_post_type', 'spek_disable_block_editor_for_partners', 10, 2);

/**
 * Register partner data metabox.
 */
function spek_add_partner_metaboxes(): void
{
    add_meta_box(
        'spek_partner_data',
        __('Στοιχεία συνεργάτη / σημείου πώλησης', 'spek-theme'),
        'spek_render_partner_data_metabox',
        'spek_partner',
        'normal',
        'high'
    );
}

add_action('add_meta_boxes', 'spek_add_partner_metaboxes');

/**
 * Render partner fields.
 */
function spek_render_partner_data_metabox($post): void
{
    wp_nonce_field('spek_save_partner_data', 'spek_partner_data_nonce');

    $fields = [
        'partner_address' => [
            'label'       => __('Διεύθυνση', 'spek-theme'),
            'type'        => 'text',
            'placeholder' => __('π.χ. Λεωφ. Κηφισίας 100', 'spek-theme'),
        ],
        'partner_area' => [
            'label'       => __('Περιοχή / Πόλη', 'spek-theme'),
            'type'        => 'text',
            'placeholder' => __('π.χ. Αθήνα, Θεσσαλονίκη, Πάτρα', 'spek-theme'),
        ],
        'partner_postcode' => [
            'label'       => __('Ταχυδρομικός Κώδικας', 'spek-theme'),
            'type'        => 'text',
            'placeholder' => __('π.χ. 11526', 'spek-theme'),
        ],
        'partner_phone' => [
            'label'       => __('Τηλέφωνο', 'spek-theme'),
            'type'        => 'text',
            'placeholder' => __('π.χ. 210 0000000', 'spek-theme'),
        ],
        'partner_mobile' => [
            'label'       => __('Κινητό', 'spek-theme'),
            'type'        => 'text',
            'placeholder' => __('π.χ. 6900 000000', 'spek-theme'),
        ],
        'partner_email' => [
            'label'       => __('Email', 'spek-theme'),
            'type'        => 'email',
            'placeholder' => __('π.χ. info@example.gr', 'spek-theme'),
        ],
        'partner_website' => [
            'label'       => __('Website', 'spek-theme'),
            'type'        => 'url',
            'placeholder' => __('π.χ. https://example.gr', 'spek-theme'),
        ],
        'partner_contact_person' => [
            'label'       => __('Υπεύθυνος επικοινωνίας', 'spek-theme'),
            'type'        => 'text',
            'placeholder' => __('π.χ. Γιώργος Παπαδόπουλος', 'spek-theme'),
        ],
        'partner_maps_url' => [
            'label'       => __('Google Maps URL', 'spek-theme'),
            'type'        => 'url',
            'placeholder' => __('Κάντε paste το full link από Google Maps', 'spek-theme'),
        ],
        'partner_latitude' => [
            'label'       => __('Latitude', 'spek-theme'),
            'type'        => 'text',
            'placeholder' => __('Συμπληρώνεται αυτόματα από το Google Maps URL', 'spek-theme'),
        ],
        'partner_longitude' => [
            'label'       => __('Longitude', 'spek-theme'),
            'type'        => 'text',
            'placeholder' => __('Συμπληρώνεται αυτόματα από το Google Maps URL', 'spek-theme'),
        ],
    ];
    ?>

    <div class="spek-product-admin">
        <p class="spek-product-admin__intro">
            <?php esc_html_e('Το πεδίο τίτλου επάνω χρησιμοποιείται ως', 'spek-theme'); ?> <strong><?php esc_html_e('Επωνυμία', 'spek-theme'); ?></strong><?php esc_html_e('. Εδώ συμπληρώνετε τα στοιχεία του συνεργάτη ή του σημείου πώλησης.', 'spek-theme'); ?>
        </p>

        <?php
        $suggested_maps_url = (string) get_post_meta($post->ID, '_spek_partner_suggested_maps_url', true);
        if ($suggested_maps_url !== '') :
        ?>
            <div class="notice notice-warning inline" style="padding:12px 16px;margin:12px 0;">
                <p><strong><?php esc_html_e('Google Maps: χρειάζεται επιβεβαίωση σημείου', 'spek-theme'); ?></strong></p>
                <p><?php esc_html_e('Το link του Excel ενδέχεται να αφορά άλλη επιχείρηση. Ελέγξτε το και, αν είναι σωστό, επικολλήστε το στο πεδίο Google Maps URL παρακάτω και αποθηκεύστε.', 'spek-theme'); ?></p>
                <p><a href="<?php echo esc_url($suggested_maps_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Άνοιγμα προτεινόμενου σημείου στο Google Maps', 'spek-theme'); ?></a></p>
            </div>
        <?php endif; ?>

        <div class="spek-product-admin__grid">
            <?php foreach ($fields as $key => $field) : ?>
                <?php $value = get_post_meta($post->ID, $key, true); ?>

                <div class="spek-product-admin__field">
                    <label for="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($field['label']); ?>
                    </label>

                    <input
                        type="<?php echo esc_attr($field['type']); ?>"
                        id="<?php echo esc_attr($key); ?>"
                        name="<?php echo esc_attr($key); ?>"
                        value="<?php echo esc_attr($value); ?>"
                        placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                    >
                </div>
            <?php endforeach; ?>
        </div>

        <div class="spek-product-admin__field spek-product-admin__field--full">
            <label for="partner_notes">
                <?php esc_html_e('Εσωτερικές σημειώσεις', 'spek-theme'); ?>
            </label>

            <textarea
                id="partner_notes"
                name="partner_notes"
                rows="4"
                placeholder="<?php esc_attr_e('Προαιρετικές σημειώσεις για χρήση από την ομάδα.', 'spek-theme'); ?>"
            ><?php echo esc_textarea(get_post_meta($post->ID, 'partner_notes', true)); ?></textarea>
        </div>
    </div>

    <?php
}

/**
 * Extract embedded coordinates from a Google Maps URL, without API calls.
 * Prefer /@latitude,longitude exactly as requested for the SPEK workflow.
 * This is normally the map camera position (not necessarily the store pin).
 * Fallback: actual POI /!3d...!4d..., then explicit query coordinates.
 */
function spek_extract_lat_lng_from_maps_url(string $url): array
{
    if ($url === '') {
        return [];
    }
    $decoded_url = $url;
    for ($i = 0; $i < 3; $i++) {
        $decoded_url = rawurldecode($decoded_url);
    }
    $number = '(-?(?:[0-9]+(?:\.[0-9]+)?|\.[0-9]+))';
    $patterns = [
        'map_center' => '~@' . $number . ',' . $number . '(?=,|/|\?|&|$)~i',
        'place' => '~!3d' . $number . '!4d' . $number . '~i',
        'query' => '~[?&](?:q|query|ll|destination)=' . $number . ',\s*' . $number . '(?=&|$)~i',
    ];
    foreach ($patterns as $source => $pattern) {
        if (!preg_match($pattern, $decoded_url, $matches)) {
            continue;
        }
        $lat = (float) $matches[1];
        $lng = (float) $matches[2];
        if (!is_finite($lat) || !is_finite($lng)
            || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            continue;
        }
        return [
            'latitude' => (string) $matches[1],
            'longitude' => (string) $matches[2],
            'source' => $source,
        ];
    }
    return [];
}

/**
 * Store coordinates from a pasted Maps URL, with a country plausibility check.
 * Never rewrite existing manual coordinates unless the URL changed.
 */
function spek_partner_save_coords_from_maps_url(int $post_id, string $maps_url, bool $force = false): bool
{
    $parsed = spek_extract_lat_lng_from_maps_url($maps_url);
    if (!$parsed) {
        return false;
    }
    if (function_exists('spek_partner_import_coords_plausible')
        && !spek_partner_import_coords_plausible([
            'postcode' => (string) get_post_meta($post_id, 'partner_postcode', true),
        ], $parsed)) {
        update_post_meta($post_id, '_spek_partner_pin_needs_review', '1');
        return false;
    }
    $lat = (string) get_post_meta($post_id, 'partner_latitude', true);
    $lng = (string) get_post_meta($post_id, 'partner_longitude', true);
    if (!$force && $lat !== '' && $lng !== '') {
        return false;
    }
    update_post_meta($post_id, 'partner_latitude', $parsed['latitude']);
    update_post_meta($post_id, 'partner_longitude', $parsed['longitude']);
    update_post_meta($post_id, '_spek_partner_coords_source', $parsed['source']);
    if ($parsed['source'] === 'map_center') {
        update_post_meta($post_id, '_spek_partner_pin_needs_review', '1');
    } else {
        delete_post_meta($post_id, '_spek_partner_pin_needs_review');
    }
    return true;
}

/**
 * Save partner fields.
 */
function spek_save_partner_data($post_id): void
{
    if (!isset($_POST['spek_partner_data_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['spek_partner_data_nonce'])), 'spek_save_partner_data')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $text_fields = [
        'partner_address',
        'partner_area',
        'partner_postcode',
        'partner_phone',
        'partner_mobile',
        'partner_contact_person',
        'partner_notes',
    ];

    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta(
                $post_id,
                $field,
                sanitize_textarea_field(wp_unslash($_POST[$field]))
            );
        }
    }

    if (isset($_POST['partner_email'])) {
        update_post_meta(
            $post_id,
            'partner_email',
            sanitize_email(wp_unslash($_POST['partner_email']))
        );
    }

    $maps_url = '';
    $existing_maps_url = (string) get_post_meta($post_id, 'partner_maps_url', true);

    if (isset($_POST['partner_website'])) {
        update_post_meta(
            $post_id,
            'partner_website',
            esc_url_raw(wp_unslash($_POST['partner_website']))
        );
    }

    if (isset($_POST['partner_maps_url'])) {
        $maps_url = esc_url_raw(wp_unslash($_POST['partner_maps_url']));

        update_post_meta(
            $post_id,
            'partner_maps_url',
            $maps_url
        );

        // Manual approval/correction resolves the importer suggestion.
        $suggested = (string) get_post_meta($post_id, '_spek_partner_suggested_maps_url', true);
        if ($suggested !== '' && $maps_url !== '') {
            delete_post_meta($post_id, '_spek_partner_suggested_maps_url');
        }
    }

    // A changed Google Maps URL gets its embedded coordinates automatically.
    // Unchanged URLs will fill missing coordinates but preserve manual pins.
    $url_changed = $maps_url !== '' && $maps_url !== $existing_maps_url;
    $autofilled = $maps_url !== '' && spek_partner_save_coords_from_maps_url(
        (int) $post_id,
        $maps_url,
        $url_changed
    );
    if (!$autofilled && (!$url_changed || !spek_extract_lat_lng_from_maps_url($maps_url))) {
        // Explicitly edited coordinates are still supported, especially for
        // short links without embedded coordinates.
        foreach (['partner_latitude', 'partner_longitude'] as $field) {
            if (isset($_POST[$field])) {
                update_post_meta(
                    $post_id,
                    $field,
                    sanitize_text_field(wp_unslash((string) $_POST[$field]))
                );
            }
        }
    }

}

add_action('save_post_spek_partner', 'spek_save_partner_data');

/** Live coordinate extraction in the normal WordPress partner editor. */
function spek_enqueue_partner_coordinate_autofill(string $hook): void
{
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'spek_partner' || $screen->base !== 'post') {
        return;
    }
    wp_enqueue_script(
        'spek-partner-coordinates',
        SPEK_THEME_URI . '/assets/js/partner-coordinates.js',
        [],
        filemtime(SPEK_THEME_DIR . '/assets/js/partner-coordinates.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'spek_enqueue_partner_coordinate_autofill');


/**
 * Partner admin columns.
 */
function spek_partner_admin_columns($columns)
{
    $new_columns = [];

    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = __('Επωνυμία', 'spek-theme');
    $new_columns['partner_area'] = __('Περιοχή', 'spek-theme');
    $new_columns['partner_address'] = __('Διεύθυνση', 'spek-theme');
    $new_columns['partner_review'] = __('Έλεγχος', 'spek-theme');
    $new_columns['partner_phone'] = __('Τηλέφωνο', 'spek-theme');
    $new_columns['partner_email'] = __('Email', 'spek-theme');
    $new_columns['date'] = $columns['date'];

    return array_merge($new_columns, array_diff_key($columns, $new_columns));
}

add_filter('manage_spek_partner_posts_columns', 'spek_partner_admin_columns');

function spek_partner_admin_column_content($column, $post_id): void
{
    if ($column === 'partner_review' && function_exists('spek_partner_review_status')) {
        $status = spek_partner_review_status((int) $post_id);
        $label = spek_partner_review_label($status);
        $url = add_query_arg([
            'post_type' => 'spek_partner',
            'page' => 'spek-partner-review',
            's' => get_the_title($post_id),
        ], admin_url('edit.php'));
        echo '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
    }

    if ($column === 'partner_area') {
        echo esc_html(get_post_meta($post_id, 'partner_area', true) ?: '—');
    }

    if ($column === 'partner_address') {
        echo esc_html(get_post_meta($post_id, 'partner_address', true) ?: '—');
    }

    if ($column === 'partner_phone') {
        echo esc_html(get_post_meta($post_id, 'partner_phone', true) ?: '—');
    }

    if ($column === 'partner_email') {
        $email = get_post_meta($post_id, 'partner_email', true);

        if ($email) {
            echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
        } else {
            echo '—';
        }
    }
}

add_action('manage_spek_partner_posts_custom_column', 'spek_partner_admin_column_content', 10, 2);

/**
 * Change title placeholder for partners.
 */
function spek_partner_title_placeholder($title, $post): string
{
    if ($post->post_type === 'spek_partner') {
        return __('Επωνυμία συνεργάτη / σημείου πώλησης', 'spek-theme');
    }

    return $title;
}

add_filter('enter_title_here', 'spek_partner_title_placeholder', 10, 2);