<?php
/** Backend editors for the SPEK custom i18n layer. @package SpekTheme */
if (!defined('ABSPATH')) { exit; }


add_action('admin_notices', static function (): void {
    $conflicts = [];
    if (function_exists('pll_current_language')) { $conflicts[] = 'Polylang'; }
    if (class_exists('TRP_Translate_Press') || defined('TRP_PLUGIN_VERSION')) { $conflicts[] = 'TranslatePress'; }
    if (!$conflicts || !current_user_can('manage_options')) { return; }
    echo '<div class="notice notice-warning"><p><strong>SPEK i18n:</strong> ' . esc_html(sprintf(__('Απενεργοποιήστε τα plugins %s πριν χρησιμοποιήσετε το custom i18n, ώστε να μη συγκρούονται τα language URLs και τα φίλτρα.', 'spek-theme'), implode(', ', $conflicts))) . '</p></div>';
});

function spek_i18n_supported_post_types(): array {
    return ['page', 'post', 'spek_product', 'spek_catalogue', 'spek_partner', 'spek_career'];
}

add_action('add_meta_boxes', static function (): void {
    foreach (spek_i18n_supported_post_types() as $type) {
        if (!post_type_exists($type)) { continue; }
        add_meta_box(
            'spek_i18n_english',
            __('Αγγλική μετάφραση (SPEK i18n)', 'spek-theme'),
            'spek_i18n_render_post_metabox',
            $type,
            'normal',
            $type === 'spek_product' ? 'high' : 'default'
        );
    }
}, 20);

function spek_i18n_render_post_metabox(WP_Post $post): void {
    wp_nonce_field('spek_i18n_save_post', 'spek_i18n_nonce');
    $title = spek_i18n_raw_post_translation($post->ID, 'title');
    $excerpt = spek_i18n_raw_post_translation($post->ID, 'excerpt');
    $content = spek_i18n_raw_post_translation($post->ID, 'content');
    ?>
    <p class="description"><?php esc_html_e('Το ελληνικό περιεχόμενο παραμένει το canonical WordPress περιεχόμενο. Τα παρακάτω πεδία εμφανίζονται μόνο στο /en/. Κενό πεδίο σημαίνει ότι δεν υπάρχει χειροκίνητη αγγλική τιμή.', 'spek-theme'); ?></p>
    <table class="form-table" role="presentation">
        <tr>
            <th><label for="spek_i18n_title_en"><?php esc_html_e('Τίτλος (English)', 'spek-theme'); ?></label></th>
            <td><input class="widefat" type="text" id="spek_i18n_title_en" name="spek_i18n_title_en" value="<?php echo esc_attr($title); ?>"></td>
        </tr>
        <tr>
            <th><label for="spek_i18n_excerpt_en"><?php esc_html_e('Σύντομη περιγραφή / Excerpt (English)', 'spek-theme'); ?></label></th>
            <td><textarea class="widefat" rows="3" id="spek_i18n_excerpt_en" name="spek_i18n_excerpt_en"><?php echo esc_textarea($excerpt); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="spek_i18n_content_en"><?php esc_html_e('Περιεχόμενο (English)', 'spek-theme'); ?></label></th>
            <td><textarea class="widefat" rows="10" id="spek_i18n_content_en" name="spek_i18n_content_en"><?php echo esc_textarea($content); ?></textarea></td>
        </tr>
    </table>
    <?php

    if ($post->post_type !== 'spek_product') { return; }

    $fields = [
        'product_short_description' => __('Σύντομη περιγραφή προϊόντος', 'spek-theme'),
        'product_dimensions' => __('Διαστάσεις', 'spek-theme'),
        'product_material' => __('Υλικό', 'spek-theme'),
        'product_color' => __('Χρώμα', 'spek-theme'),
        'product_application_text' => __('Εφαρμογή', 'spek-theme'),
        'product_compatibility' => __('Συμβατότητα', 'spek-theme'),
        'product_installation_type' => __('Τύπος εγκατάστασης', 'spek-theme'),
        'product_packaging' => __('Συσκευασία', 'spek-theme'),
        'product_weight' => __('Βάρος', 'spek-theme'),
        'product_capacity' => __('Χωρητικότητα / περιεχόμενο', 'spek-theme'),
        'product_features' => __('Πρόσθετα χαρακτηριστικά', 'spek-theme'),
        '_spek_product_subtitle' => __('Υπότιτλος κάρτας', 'spek-theme'),
        '_spek_product_application' => __('Εφαρμογή κάρτας', 'spek-theme'),
        '_spek_product_series' => __('Σειρά κάρτας', 'spek-theme'),
    ];
    ?>
    <hr>
    <h3><?php esc_html_e('Αγγλικά πεδία προϊόντος', 'spek-theme'); ?></h3>
    <table class="form-table" role="presentation">
        <?php foreach ($fields as $field => $label) :
            $value = spek_i18n_raw_post_translation($post->ID, $field); ?>
            <tr>
                <th><label for="spek_i18n_meta_<?php echo esc_attr(md5($field)); ?>"><?php echo esc_html($label); ?> (EN)</label></th>
                <td><textarea class="widefat" rows="2" id="spek_i18n_meta_<?php echo esc_attr(md5($field)); ?>" name="spek_i18n_meta_en[<?php echo esc_attr($field); ?>]"><?php echo esc_textarea($value); ?></textarea></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}

add_action('save_post', static function (int $post_id): void {
    if (!isset($_POST['spek_i18n_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['spek_i18n_nonce'])), 'spek_i18n_save_post')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    if (!in_array(get_post_type($post_id), spek_i18n_supported_post_types(), true)) { return; }

    $basic = [
        'title' => ['field' => 'spek_i18n_title_en', 'sanitize' => 'sanitize_text_field'],
        'excerpt' => ['field' => 'spek_i18n_excerpt_en', 'sanitize' => 'sanitize_textarea_field'],
        'content' => ['field' => 'spek_i18n_content_en', 'sanitize' => 'wp_kses_post'],
    ];
    foreach ($basic as $name => $config) {
        if (!isset($_POST[$config['field']])) { continue; }
        $raw = wp_unslash($_POST[$config['field']]);
        $value = call_user_func($config['sanitize'], $raw);
        $key = spek_i18n_post_meta_key($name);
        if ($value === '') { delete_post_meta($post_id, $key); } else { update_post_meta($post_id, $key, wp_slash($value)); }
    }

    if (isset($_POST['spek_i18n_meta_en']) && is_array($_POST['spek_i18n_meta_en'])) {
        $allowed = array_flip(spek_i18n_translatable_post_meta_keys());
        foreach (wp_unslash($_POST['spek_i18n_meta_en']) as $field => $raw) {
            $field = (string) $field;
            if (!isset($allowed[$field])) { continue; }
            $value = sanitize_textarea_field((string) $raw);
            $key = spek_i18n_post_meta_key($field);
            if ($value === '') { delete_post_meta($post_id, $key); } else { update_post_meta($post_id, $key, wp_slash($value)); }
        }
    }
}, 20);

/** Taxonomy English name/description fields. */
foreach (spek_multilingual_taxonomies() as $spek_i18n_taxonomy) {
    add_action($spek_i18n_taxonomy . '_add_form_fields', static function () { ?>
        <div class="form-field term-spek-name-en-wrap">
            <label for="spek_name_en"><?php esc_html_e('English name', 'spek-theme'); ?></label>
            <input type="text" name="spek_name_en" id="spek_name_en" value="">
        </div>
        <div class="form-field term-spek-description-en-wrap">
            <label for="spek_description_en"><?php esc_html_e('English description', 'spek-theme'); ?></label>
            <textarea name="spek_description_en" id="spek_description_en" rows="4"></textarea>
        </div>
    <?php });

    add_action($spek_i18n_taxonomy . '_edit_form_fields', static function (WP_Term $term) { ?>
        <tr class="form-field term-spek-name-en-wrap">
            <th><label for="spek_name_en"><?php esc_html_e('English name', 'spek-theme'); ?></label></th>
            <td><input type="text" name="spek_name_en" id="spek_name_en" value="<?php echo esc_attr((string) get_term_meta($term->term_id, '_spek_name_en', true)); ?>"></td>
        </tr>
        <tr class="form-field term-spek-description-en-wrap">
            <th><label for="spek_description_en"><?php esc_html_e('English description', 'spek-theme'); ?></label></th>
            <td><textarea name="spek_description_en" id="spek_description_en" rows="5" class="large-text"><?php echo esc_textarea((string) get_term_meta($term->term_id, '_spek_description_en', true)); ?></textarea></td>
        </tr>
    <?php });

    $save_term = static function (int $term_id): void {
        if (!current_user_can('manage_categories')) { return; }
        if (isset($_POST['spek_name_en'])) {
            $value = sanitize_text_field(wp_unslash($_POST['spek_name_en']));
            if ($value === '') { delete_term_meta($term_id, '_spek_name_en'); } else { update_term_meta($term_id, '_spek_name_en', $value); }
        }
        if (isset($_POST['spek_description_en'])) {
            $value = sanitize_textarea_field(wp_unslash($_POST['spek_description_en']));
            if ($value === '') { delete_term_meta($term_id, '_spek_description_en'); } else { update_term_meta($term_id, '_spek_description_en', $value); }
        }
    };
    add_action('created_' . $spek_i18n_taxonomy, $save_term);
    add_action('edited_' . $spek_i18n_taxonomy, $save_term);
}

/** English label field directly inside Appearance > Menus. */
add_action('wp_nav_menu_item_custom_fields', static function ($item_id, $item) { ?>
    <p class="description description-wide">
        <label for="edit-menu-item-spek-label-en-<?php echo (int) $item_id; ?>">
            <?php esc_html_e('English label', 'spek-theme'); ?><br>
            <input type="text" class="widefat code edit-menu-item-custom" id="edit-menu-item-spek-label-en-<?php echo (int) $item_id; ?>" name="menu-item-spek-label-en[<?php echo (int) $item_id; ?>]" value="<?php echo esc_attr((string) get_post_meta($item_id, '_spek_label_en', true)); ?>">
        </label>
    </p>
<?php }, 10, 2);
add_action('wp_update_nav_menu_item', static function ($menu_id, $menu_item_db_id): void {
    if (!current_user_can('edit_theme_options')) { return; }
    $value = isset($_POST['menu-item-spek-label-en'][$menu_item_db_id]) ? sanitize_text_field(wp_unslash($_POST['menu-item-spek-label-en'][$menu_item_db_id])) : '';
    if ($value === '') { delete_post_meta($menu_item_db_id, '_spek_label_en'); } else { update_post_meta($menu_item_db_id, '_spek_label_en', $value); }
}, 10, 2);

/** Theme string translation manager. */
add_action('admin_menu', static function (): void {
    add_theme_page(
        __('SPEK Μεταφράσεις', 'spek-theme'),
        __('SPEK Μεταφράσεις', 'spek-theme'),
        'manage_options',
        'spek-i18n',
        'spek_i18n_render_string_admin'
    );
});

function spek_i18n_render_string_admin(): void {
    if (!current_user_can('manage_options')) { wp_die(esc_html__('Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη σελίδα.', 'spek-theme')); }
    $registry = spek_registered_strings();
    $groups = array_values(array_unique(array_values($registry)));
    sort($groups, SORT_NATURAL | SORT_FLAG_CASE);
    $group = isset($_GET['spek_group']) ? sanitize_text_field(wp_unslash($_GET['spek_group'])) : 'SPEK Home';
    if (!in_array($group, $groups, true)) { $group = $groups[0] ?? ''; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['spek_i18n_save_strings'])) {
        check_admin_referer('spek_i18n_save_strings');
        $stored = get_option('spek_i18n_strings_en', []);
        if (!is_array($stored)) { $stored = []; }
        $posted = isset($_POST['translations']) && is_array($_POST['translations']) ? wp_unslash($_POST['translations']) : [];
        foreach ($registry as $source => $source_group) {
            if ($source_group !== $group) { continue; }
            $hash = md5($source);
            $value = isset($posted[$hash]) ? sanitize_textarea_field((string) $posted[$hash]) : '';
            if ($value === '') { unset($stored[$source]); } else { $stored[$source] = $value; }
        }
        update_option('spek_i18n_strings_en', $stored, false);
        echo '<div class="notice notice-success"><p>' . esc_html__('Οι αγγλικές μεταφράσεις αποθηκεύτηκαν.', 'spek-theme') . '</p></div>';
    }

    $stored = get_option('spek_i18n_strings_en', []);
    if (!is_array($stored)) { $stored = []; }
    $defaults = spek_i18n_default_strings_en();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('SPEK · Μεταφράσεις Theme', 'spek-theme'); ?></h1>
        <p><?php esc_html_e('Εδώ μεταφράζονται τα στατικά κείμενα του theme. Οι τιμές αυτής της οθόνης υπερισχύουν των προεπιλεγμένων μεταφράσεων του theme.', 'spek-theme'); ?></p>
        <form method="get" style="margin:16px 0">
            <input type="hidden" name="page" value="spek-i18n">
            <select name="spek_group">
                <?php foreach ($groups as $item) : ?><option value="<?php echo esc_attr($item); ?>" <?php selected($group, $item); ?>><?php echo esc_html($item); ?></option><?php endforeach; ?>
            </select>
            <?php submit_button(__('Προβολή ομάδας', 'spek-theme'), 'secondary', '', false); ?>
        </form>

        <form method="post">
            <?php wp_nonce_field('spek_i18n_save_strings'); ?>
            <input type="hidden" name="spek_i18n_save_strings" value="1">
            <table class="widefat striped" style="max-width:1200px">
                <thead><tr><th style="width:45%"><?php esc_html_e('Ελληνικό / source', 'spek-theme'); ?></th><th><?php esc_html_e('English', 'spek-theme'); ?></th></tr></thead>
                <tbody>
                <?php foreach ($registry as $source => $source_group) : if ($source_group !== $group) { continue; }
                    $value = isset($stored[$source]) ? (string) $stored[$source] : (string) ($defaults[$source] ?? ''); ?>
                    <tr>
                        <td><code style="white-space:normal"><?php echo esc_html($source); ?></code></td>
                        <td><textarea class="large-text" rows="2" name="translations[<?php echo esc_attr(md5($source)); ?>]"><?php echo esc_textarea($value); ?></textarea></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php submit_button(__('Αποθήκευση αγγλικών μεταφράσεων', 'spek-theme')); ?>
        </form>
    </div>
    <?php
}
