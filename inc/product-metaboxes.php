<?php
/**
 * Product admin metaboxes.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Disable Gutenberg only for SPEK products.
 * This makes the product screen feel closer to WooCommerce/classic product editing.
 */
function spek_disable_block_editor_for_products($use_block_editor, $post_type)
{
    if ($post_type === 'spek_product') {
        return false;
    }

    return $use_block_editor;
}

add_filter('use_block_editor_for_post_type', 'spek_disable_block_editor_for_products', 10, 2);

/**
 * Register product data metabox.
 */
function spek_add_product_metaboxes(): void
{
    add_meta_box(
        'spek_product_data',
        __('Στοιχεία προϊόντος', 'spek-theme'),
        'spek_render_product_data_metabox',
        'spek_product',
        'normal',
        'high'
    );

    add_meta_box(
        'spek_product_media',
        __('Αρχεία & Media προϊόντος', 'spek-theme'),
        'spek_render_product_media_metabox',
        'spek_product',
        'normal',
        'default'
    );
}

add_action('add_meta_boxes', 'spek_add_product_metaboxes');

/**
 * Product data fields.
 */
function spek_render_product_data_metabox($post): void
{
    wp_nonce_field('spek_save_product_data', 'spek_product_data_nonce');

    $fields = [
        'product_code' => [
            'label' => __('Κωδικός προϊόντος', 'spek-theme'),
            'type' => 'text',
            'placeholder' => __('π.χ. SPK-001', 'spek-theme'),
        ],
        'product_dimensions' => [
            'label' => __('Διαστάσεις', 'spek-theme'),
            'type' => 'text',
            'placeholder' => __('π.χ. 1/2", Φ40, 30cm', 'spek-theme'),
        ],
        'product_material' => [
            'label' => __('Υλικό', 'spek-theme'),
            'type' => 'text',
            'placeholder' => __('π.χ. Πλαστικό, Μεταλλικό, Ελαστικό', 'spek-theme'),
        ],
        'product_color' => [
            'label' => __('Χρώμα', 'spek-theme'),
            'type' => 'text',
            'placeholder' => __('π.χ. Λευκό, Χρωμέ, Μαύρο', 'spek-theme'),
        ],
        'product_application_text' => [
            'label' => __('Εφαρμογή', 'spek-theme'),
            'type' => 'text',
            'placeholder' => __('π.χ. WC, νιπτήρας, μπάνιο, κουζίνα', 'spek-theme'),
        ],
        'product_compatibility' => [
            'label' => __('Συμβατότητα', 'spek-theme'),
            'type' => 'text',
            'placeholder' => __('π.χ. Συμβατό με καζανάκια SPEK / universal', 'spek-theme'),
        ],
        'product_installation_type' => [
            'label' => __('Τύπος εγκατάστασης', 'spek-theme'),
            'type' => 'text',
            'placeholder' => __('π.χ. Εσωτερική, εξωτερική, επιτοίχια', 'spek-theme'),
        ],
        'product_packaging' => ['label' => __('Συσκευασία', 'spek-theme'), 'type' => 'text', 'placeholder' => ''],
        'product_weight' => ['label' => __('Βάρος', 'spek-theme'), 'type' => 'text', 'placeholder' => ''],
        'product_capacity' => ['label' => __('Χωρητικότητα / περιεχόμενο', 'spek-theme'), 'type' => 'text', 'placeholder' => ''],
        'product_features' => ['label' => __('Πρόσθετα χαρακτηριστικά', 'spek-theme'), 'type' => 'text', 'placeholder' => ''],
        '_spek_product_subtitle' => ['label' => __('Υπότιτλος κάρτας', 'spek-theme'), 'type' => 'text', 'placeholder' => ''],
        '_spek_product_application' => ['label' => __('Εφαρμογή κάρτας', 'spek-theme'), 'type' => 'text', 'placeholder' => ''],
        '_spek_product_series' => ['label' => __('Σειρά κάρτας', 'spek-theme'), 'type' => 'text', 'placeholder' => ''],
    ];
    ?>

    <div class="spek-product-admin">
        <p class="spek-product-admin__intro">
            <?php esc_html_e('Συμπληρώστε τα βασικά τεχνικά στοιχεία του προϊόντος. Αυτά θα εμφανίζονται στη σελίδα προϊόντος και στα φίλτρα/οδηγό επιλογής.', 'spek-theme'); ?>
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
            <label for="product_short_description">
                <?php esc_html_e('Σύντομη περιγραφή προϊόντος', 'spek-theme'); ?>
            </label>

            <textarea
                id="product_short_description"
                name="product_short_description"
                rows="4"
                placeholder="<?php esc_attr_e('Σύντομη περιγραφή που θα εμφανίζεται στην κάρτα και στην αρχή της σελίδας προϊόντος.', 'spek-theme'); ?>"
            ><?php echo esc_textarea(get_post_meta($post->ID, 'product_short_description', true)); ?></textarea>
        </div>
    </div>

    <?php
}

/**
 * Product media fields.
 */
function spek_render_product_media_metabox($post): void
{
    $installation_video = get_post_meta($post->ID, 'product_installation_video', true);
    $datasheet = get_post_meta($post->ID, 'product_datasheet', true);
    $catalogue_pdf = get_post_meta($post->ID, 'product_catalogue_pdf', true);
    $gallery_ids = function_exists('spek_image_import_gallery_ids')
        ? spek_image_import_gallery_ids((int) $post->ID)
        : array_values(array_filter(array_map('absint', (array) get_post_meta($post->ID, 'product_gallery', true))));
    wp_enqueue_media();
    ?>

    <div class="spek-product-admin">
        <div class="spek-product-admin__grid">
            <div class="spek-product-admin__field">
                <label for="product_installation_video">
                    <?php esc_html_e('Video εγκατάστασης', 'spek-theme'); ?>
                </label>

                <input
                    type="url"
                    id="product_installation_video"
                    name="product_installation_video"
                    value="<?php echo esc_url($installation_video); ?>"
                    placeholder="<?php esc_attr_e('YouTube / Vimeo URL', 'spek-theme'); ?>"
                >
            </div>

            <div class="spek-product-admin__field">
                <label for="product_datasheet">
                    Datasheet URL
                </label>

                <input
                    type="url"
                    id="product_datasheet"
                    name="product_datasheet"
                    value="<?php echo esc_url($datasheet); ?>"
                    placeholder="<?php esc_attr_e('PDF URL ή αρχείο από Media Library', 'spek-theme'); ?>"
                >
            </div>

            <div class="spek-product-admin__field">
                <label for="product_catalogue_pdf">
                    <?php esc_html_e('Κατάλογος PDF URL', 'spek-theme'); ?>
                </label>

                <input
                    type="url"
                    id="product_catalogue_pdf"
                    name="product_catalogue_pdf"
                    value="<?php echo esc_url($catalogue_pdf); ?>"
                    placeholder="<?php esc_attr_e('PDF URL ή αρχείο από Media Library', 'spek-theme'); ?>"
                >
            </div>
        </div>

        <div class="spek-product-admin__field spek-product-admin__field--full" style="margin-top:18px">
            <label style="display:block;margin-bottom:8px"><strong><?php esc_html_e('Gallery προϊόντος', 'spek-theme'); ?></strong></label>
            <input type="hidden" id="spek_product_gallery_ids" name="spek_product_gallery_ids" value="<?php echo esc_attr(implode(',', $gallery_ids)); ?>">
            <div id="spek_product_gallery_preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px">
                <?php foreach ($gallery_ids as $attachment_id) : ?>
                    <span data-id="<?php echo esc_attr((string) $attachment_id); ?>" style="display:inline-flex;border:1px solid #dcdcde;border-radius:4px;padding:3px;background:#fff">
                        <?php echo wp_get_attachment_image($attachment_id, [72, 72]); ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button" id="spek_product_gallery_choose"><?php esc_html_e('Επιλογή / αλλαγή gallery', 'spek-theme'); ?></button>
            <button type="button" class="button" id="spek_product_gallery_clear" <?php echo empty($gallery_ids) ? 'style="display:none"' : ''; ?>><?php esc_html_e('Καθαρισμός gallery', 'spek-theme'); ?></button>
            <p class="description"><?php esc_html_e('Η χαρακτηριστική εικόνα παραμένει ξεχωριστή. Το μαζικό εργαλείο “Εισαγωγή εικόνων” συμπληρώνει αυτόματα και το gallery.', 'spek-theme'); ?></p>
        </div>
    </div>

    <script>
    (function($){
        if (typeof wp === 'undefined' || !wp.media) { return; }
        let frame;
        const input = $('#spek_product_gallery_ids');
        const preview = $('#spek_product_gallery_preview');
        const clearBtn = $('#spek_product_gallery_clear');

        $('#spek_product_gallery_choose').on('click', function(e){
            e.preventDefault();
            if (frame) { frame.open(); return; }

            frame = wp.media({
                title: <?php echo wp_json_encode(__('Επιλογή εικόνων gallery', 'spek-theme')); ?>,
                button: { text: <?php echo wp_json_encode(__('Χρήση επιλεγμένων εικόνων', 'spek-theme')); ?> },
                library: { type: 'image' },
                multiple: true
            });

            frame.on('open', function(){
                const selection = frame.state().get('selection');
                (input.val() || '').split(',').filter(Boolean).forEach(function(id){
                    const attachment = wp.media.attachment(parseInt(id, 10));
                    attachment.fetch();
                    selection.add(attachment);
                });
            });

            frame.on('select', function(){
                const selection = frame.state().get('selection').toJSON();
                const ids = selection.map(x => x.id);
                input.val(ids.join(','));
                preview.empty();

                selection.forEach(function(x){
                    const src = (x.sizes && x.sizes.thumbnail) ? x.sizes.thumbnail.url : x.url;
                    $('<span/>', {
                        'data-id': x.id,
                        css: {display:'inline-flex',border:'1px solid #dcdcde',borderRadius:'4px',padding:'3px',background:'#fff'}
                    }).append(
                        $('<img/>',{src:src,css:{width:'72px',height:'72px',objectFit:'cover'}})
                    ).appendTo(preview);
                });

                clearBtn.toggle(ids.length > 0);
            });

            frame.open();
        });

        clearBtn.on('click', function(e){
            e.preventDefault();
            input.val('');
            preview.empty();
            clearBtn.hide();
        });
    })(jQuery);
    </script>

    <?php
}

/**
 * Save product fields.
 */
function spek_save_product_data($post_id): void
{
    if (!isset($_POST['spek_product_data_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['spek_product_data_nonce'])), 'spek_save_product_data')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $text_fields = [
        'product_code',
        'product_dimensions',
        'product_material',
        'product_color',
        'product_application_text',
        'product_compatibility',
        'product_installation_type',
        'product_short_description',
        'product_packaging', 'product_weight', 'product_capacity', 'product_features',
        '_spek_product_subtitle', '_spek_product_application', '_spek_product_series',
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

    if (isset($_POST['spek_product_gallery_ids'])) {
        $raw_gallery = sanitize_text_field(wp_unslash($_POST['spek_product_gallery_ids']));
        $gallery_ids = array_values(array_unique(array_filter(array_map('absint', preg_split('/[\s,]+/', $raw_gallery)))));
        update_post_meta($post_id, 'product_gallery', $gallery_ids);
    }

    $url_fields = [
        'product_installation_video',
        'product_datasheet',
        'product_catalogue_pdf',
    ];

    foreach ($url_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta(
                $post_id,
                $field,
                esc_url_raw(wp_unslash($_POST[$field]))
            );
        }
    }
}

add_action('save_post_spek_product', 'spek_save_product_data');

/**
 * Product admin columns.
 */
function spek_product_admin_columns($columns)
{
    $new_columns = [];

    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = __('Προϊόν', 'spek-theme');
    $new_columns['product_code'] = __('Κωδικός', 'spek-theme');
    $new_columns['product_category'] = __('Κατηγορία', 'spek-theme');
    $new_columns['thumbnail'] = __('Εικόνα', 'spek-theme');
    $new_columns['date'] = $columns['date'];

    return array_merge($new_columns, array_diff_key($columns, $new_columns));
}

add_filter('manage_spek_product_posts_columns', 'spek_product_admin_columns');

function spek_product_admin_column_content($column, $post_id): void
{
    if ($column === 'product_code') {
        echo esc_html(get_post_meta($post_id, 'product_code', true) ?: '—');
    }

    if ($column === 'product_category') {
        $terms = get_the_terms($post_id, 'product_category');

        if (!empty($terms) && !is_wp_error($terms)) {
            echo esc_html(implode(', ', wp_list_pluck($terms, 'name')));
        } else {
            echo '—';
        }
    }

    if ($column === 'thumbnail') {
        if (has_post_thumbnail($post_id)) {
            echo get_the_post_thumbnail($post_id, [54, 54]);
        } else {
            echo '—';
        }
    }
}

add_action('manage_spek_product_posts_custom_column', 'spek_product_admin_column_content', 10, 2);

/**
 * Change title placeholder.
 */
function spek_product_title_placeholder($title, $post): string
{
    if ($post->post_type === 'spek_product') {
        return __('Όνομα προϊόντος', 'spek-theme');
    }

    return $title;
}

add_filter('enter_title_here', 'spek_product_title_placeholder', 10, 2);