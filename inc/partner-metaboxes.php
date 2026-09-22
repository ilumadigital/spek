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
 * Extract latitude / longitude from a Google Maps URL.
 *
 * Supports URLs containing:
 * - @37.983810,23.727539
 * - !3d37.983810!4d23.727539
 * - query=37.983810,23.727539
 * - q=37.983810,23.727539
 * - ll=37.983810,23.727539
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

    $patterns = [
        // Πρώτη προτεραιότητα: πραγματικές coordinates του place/POI.
        '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/',
    
        // Coordinates που έχουν δοθεί ρητά ως destination/query.
        '/[?&](?:q|query|ll|destination)=(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)/',
    
        // Τελευταίο fallback: viewport / map center.
        '/@(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $decoded_url, $matches)) {
            $latitude  = (float) $matches[1];
            $longitude = (float) $matches[2];

            if (
                $latitude >= -90 &&
                $latitude <= 90 &&
                $longitude >= -180 &&
                $longitude <= 180
            ) {
                return [
                    'latitude'  => (string) $latitude,
                    'longitude' => (string) $longitude,
                ];
            }
        }
    }

    return [];
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
    }

    $coordinates = spek_extract_lat_lng_from_maps_url($maps_url);

    if (!empty($coordinates)) {
        update_post_meta($post_id, 'partner_latitude', sanitize_text_field($coordinates['latitude']));
        update_post_meta($post_id, 'partner_longitude', sanitize_text_field($coordinates['longitude']));
    } else {
        if (isset($_POST['partner_latitude'])) {
            update_post_meta(
                $post_id,
                'partner_latitude',
                sanitize_text_field(wp_unslash($_POST['partner_latitude']))
            );
        }

        if (isset($_POST['partner_longitude'])) {
            update_post_meta(
                $post_id,
                'partner_longitude',
                sanitize_text_field(wp_unslash($_POST['partner_longitude']))
            );
        }
    }
}

add_action('save_post_spek_partner', 'spek_save_partner_data');

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
    $new_columns['partner_phone'] = __('Τηλέφωνο', 'spek-theme');
    $new_columns['partner_email'] = __('Email', 'spek-theme');
    $new_columns['date'] = $columns['date'];

    return array_merge($new_columns, array_diff_key($columns, $new_columns));
}

add_filter('manage_spek_partner_posts_columns', 'spek_partner_admin_columns');

function spek_partner_admin_column_content($column, $post_id): void
{
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