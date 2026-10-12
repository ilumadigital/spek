<?php
/**
 * Google Maps browser-key settings for the partner locator.
 *
 * The client key is intentionally public in the browser; restrict it by
 * HTTP referrer and Maps JavaScript / Geocoding APIs in Google Cloud.
 * Do not use the server-side SPEK_GOOGLE_MAPS_API_KEY for this setting.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Prefer wp-config.php when set, otherwise the WordPress admin option. */
function spek_partner_google_browser_key(): string
{
    if (defined('SPEK_GOOGLE_MAPS_BROWSER_KEY')) {
        $constant = trim((string) SPEK_GOOGLE_MAPS_BROWSER_KEY);
        if ($constant !== '') {
            return $constant;
        }
    }

    return trim((string) get_option('spek_google_maps_browser_key', ''));
}

/** Register the separate Maps JavaScript browser key setting. */
function spek_register_partner_google_map_settings(): void
{
    register_setting('spek_partner_map_settings', 'spek_google_maps_browser_key', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
        'show_in_rest' => false,
    ]);
}
add_action('admin_init', 'spek_register_partner_google_map_settings');

function spek_partner_google_map_menu(): void
{
    add_submenu_page(
        'edit.php?post_type=spek_partner',
        __('Ρυθμίσεις Google Maps', 'spek-theme'),
        __('Ρυθμίσεις Google Maps', 'spek-theme'),
        'manage_options',
        'spek-partner-google-maps',
        'spek_render_partner_google_map_settings'
    );
}
add_action('admin_menu', 'spek_partner_google_map_menu');

function spek_render_partner_google_map_settings(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Δεν έχετε δικαίωμα πρόσβασης.', 'spek-theme'));
    }

    $key_from_config = defined('SPEK_GOOGLE_MAPS_BROWSER_KEY')
        && trim((string) SPEK_GOOGLE_MAPS_BROWSER_KEY) !== '';
    $current = (string) get_option('spek_google_maps_browser_key', '');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Google Maps — Σημεία Πώλησης', 'spek-theme'); ?></h1>
        <p style="max-width:720px;">
            <?php esc_html_e('Ορίστε το browser API key για να εμφανίζεται ο Google χάρτης των συνεργατών. Αν δεν υπάρχει key, συνεχίζει να χρησιμοποιείται ο προηγούμενος χάρτης.', 'spek-theme'); ?>
        </p>
        <form method="post" action="options.php">
            <?php settings_fields('spek_partner_map_settings'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="spek_google_maps_browser_key"><?php esc_html_e('Google Maps browser API key', 'spek-theme'); ?></label>
                    </th>
                    <td>
                        <input
                            type="password"
                            class="regular-text"
                            id="spek_google_maps_browser_key"
                            name="spek_google_maps_browser_key"
                            value="<?php echo esc_attr($current); ?>"
                            autocomplete="new-password"
                            <?php disabled($key_from_config); ?>
                        >
                        <p class="description">
                            <?php esc_html_e('Στο Google Cloud περιορίστε το key σε Websites: https://spek.iluma.gr/* και APIs: Maps JavaScript API (και Geocoding API για αναζήτηση κοντινών σημείων ανά περιοχή). Μην χρησιμοποιήσετε το server-side Places API key.', 'spek-theme'); ?>
                        </p>
                        <?php if ($key_from_config) : ?>
                            <p><strong><?php esc_html_e('Το κλειδί ορίζεται ήδη στο wp-config.php και έχει προτεραιότητα.', 'spek-theme'); ?></strong></p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <?php if (!$key_from_config) : ?>
                <?php submit_button(__('Αποθήκευση και ενεργοποίηση Google Maps', 'spek-theme')); ?>
            <?php endif; ?>
        </form>
    </div>
    <?php
}
