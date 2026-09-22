<?php
/**
 * Catalogue PDF fields and helpers.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return the PDF URL attached to a catalogue.
 */
function spek_get_catalogue_pdf_url(int $post_id): string
{
    $attachment_id = (int) get_post_meta($post_id, '_spek_catalogue_pdf_id', true);

    if ($attachment_id > 0) {
        $url = wp_get_attachment_url($attachment_id);
        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    $url = (string) get_post_meta($post_id, '_spek_catalogue_pdf_url', true);

    return $url !== '' ? esc_url_raw($url) : '';
}

/**
 * Add the PDF selector to catalogue edit screens.
 */
function spek_catalogue_add_meta_boxes(): void
{
    add_meta_box(
        'spek_catalogue_pdf',
        __('PDF καταλόγου', 'spek-theme'),
        'spek_catalogue_render_pdf_meta_box',
        'spek_catalogue',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes_spek_catalogue', 'spek_catalogue_add_meta_boxes');

/**
 * Render catalogue PDF field.
 */
function spek_catalogue_render_pdf_meta_box(WP_Post $post): void
{
    wp_nonce_field('spek_catalogue_pdf_save', 'spek_catalogue_pdf_nonce');

    $attachment_id = (int) get_post_meta($post->ID, '_spek_catalogue_pdf_id', true);
    $pdf_url = spek_get_catalogue_pdf_url($post->ID);
    ?>
    <div class="spek-catalogue-pdf-field">
        <p>
            <?php esc_html_e('Ανέβασε ή επίλεξε το PDF που θα χρησιμοποιείται στον online viewer του καταλόγου.', 'spek-theme'); ?>
        </p>

        <input
            type="hidden"
            id="spek_catalogue_pdf_id"
            name="spek_catalogue_pdf_id"
            value="<?php echo esc_attr((string) $attachment_id); ?>"
        >

        <label for="spek_catalogue_pdf_url">
            <strong><?php esc_html_e('PDF URL', 'spek-theme'); ?></strong>
        </label>

        <input
            type="url"
            class="widefat"
            id="spek_catalogue_pdf_url"
            name="spek_catalogue_pdf_url"
            value="<?php echo esc_attr($pdf_url); ?>"
            placeholder="<?php esc_attr_e('https://example.com/catalogue.pdf', 'spek-theme'); ?>"
            style="margin-top:8px;"
        >

        <p style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:12px;">
            <button type="button" class="button button-primary" id="spek_catalogue_pdf_select">
                <?php esc_html_e('Επιλογή / ανέβασμα PDF', 'spek-theme'); ?>
            </button>

            <button type="button" class="button" id="spek_catalogue_pdf_remove"<?php echo $pdf_url === '' ? ' style="display:none;"' : ''; ?>>
                <?php esc_html_e('Αφαίρεση PDF', 'spek-theme'); ?>
            </button>

            <a
                id="spek_catalogue_pdf_preview"
                class="button"
                href="<?php echo esc_url($pdf_url); ?>"
                target="_blank"
                rel="noopener"
                <?php echo $pdf_url === '' ? 'style="display:none;"' : ''; ?>
            >
                <?php esc_html_e('Άνοιγμα PDF', 'spek-theme'); ?>
            </a>
        </p>

        <p class="description">
            <?php esc_html_e('Προτείνεται το PDF να βρίσκεται στη Media Library του site. Η αναζήτηση στον viewer λειτουργεί όταν το PDF περιέχει πραγματικό κείμενο/OCR και όχι μόνο σαρωμένες εικόνες.', 'spek-theme'); ?>
        </p>
    </div>
    <?php
}

/**
 * Save PDF fields.
 */
function spek_catalogue_save_pdf_meta(int $post_id): void
{
    if (!isset($_POST['spek_catalogue_pdf_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['spek_catalogue_pdf_nonce'])), 'spek_catalogue_pdf_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $attachment_id = isset($_POST['spek_catalogue_pdf_id']) ? absint($_POST['spek_catalogue_pdf_id']) : 0;
    $pdf_url = isset($_POST['spek_catalogue_pdf_url']) ? esc_url_raw(wp_unslash($_POST['spek_catalogue_pdf_url'])) : '';

    if ($attachment_id > 0) {
        $mime = get_post_mime_type($attachment_id);
        if ($mime !== 'application/pdf') {
            $attachment_id = 0;
        }
    }

    if ($attachment_id > 0) {
        update_post_meta($post_id, '_spek_catalogue_pdf_id', $attachment_id);

        $attachment_url = wp_get_attachment_url($attachment_id);
        if (is_string($attachment_url) && $attachment_url !== '') {
            update_post_meta($post_id, '_spek_catalogue_pdf_url', esc_url_raw($attachment_url));
        }
        return;
    }

    delete_post_meta($post_id, '_spek_catalogue_pdf_id');

    if ($pdf_url !== '') {
        update_post_meta($post_id, '_spek_catalogue_pdf_url', $pdf_url);
    } else {
        delete_post_meta($post_id, '_spek_catalogue_pdf_url');
    }
}
add_action('save_post_spek_catalogue', 'spek_catalogue_save_pdf_meta');

/**
 * Load Media Library and the tiny selector script on catalogue edit screens.
 */
function spek_catalogue_admin_assets(string $hook): void
{
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'spek_catalogue') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('jquery');

    wp_localize_script('jquery-core', 'spekCatalogueAdmin', [
        'Επιλογή PDF καταλόγου' => __('Επιλογή PDF καταλόγου', 'spek-theme'),
        'Χρήση αυτού του PDF' => __('Χρήση αυτού του PDF', 'spek-theme'),
    ]);
    $script = <<<'JS'
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var selectButton = document.getElementById('spek_catalogue_pdf_select');
        var removeButton = document.getElementById('spek_catalogue_pdf_remove');
        var idInput = document.getElementById('spek_catalogue_pdf_id');
        var urlInput = document.getElementById('spek_catalogue_pdf_url');
        var preview = document.getElementById('spek_catalogue_pdf_preview');

        if (!selectButton || !urlInput || typeof wp === 'undefined' || !wp.media) {
            return;
        }

        var frame;

        function syncPreview() {
            var url = urlInput.value.trim();
            if (preview) {
                preview.href = url || '#';
                preview.style.display = url ? '' : 'none';
            }
            if (removeButton) {
                removeButton.style.display = url ? '' : 'none';
            }
        }

        selectButton.addEventListener('click', function (event) {
            event.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: spekCatalogueAdmin['Επιλογή PDF καταλόγου'],
                button: { text: spekCatalogueAdmin['Χρήση αυτού του PDF'] },
                library: { type: 'application/pdf' },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                idInput.value = attachment.id || '';
                urlInput.value = attachment.url || '';
                syncPreview();
            });

            frame.open();
        });

        if (removeButton) {
            removeButton.addEventListener('click', function (event) {
                event.preventDefault();
                idInput.value = '';
                urlInput.value = '';
                syncPreview();
            });
        }

        urlInput.addEventListener('input', function () {
            if (idInput) {
                idInput.value = '';
            }
            syncPreview();
        });
    });
}());
JS;

    wp_add_inline_script('jquery-core', $script, 'after');
}
add_action('admin_enqueue_scripts', 'spek_catalogue_admin_assets');
