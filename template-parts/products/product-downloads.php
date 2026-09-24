<?php
/**
 * Product downloads.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$product_id = get_the_ID();
$datasheets = spek_get_product_resource_urls($product_id, [
    'product_datasheet',
    'product_datasheets',
]);
$catalogues = spek_get_product_resource_urls($product_id, [
    'product_catalogue_pdf',
    'product_catalogue',
    'product_catalogues',
]);

if (empty($datasheets) && empty($catalogues)) {
    return;
}
?>

<section class="section product-downloads" id="product-downloads">
    <div class="container">
        <div class="downloads-box downloads-box--product">
            <div>
                <span class="eyebrow"><?php esc_html_e('Downloads', 'spek-theme'); ?></span>
                <h2><?php esc_html_e('Αρχεία & κατάλογοι προϊόντος', 'spek-theme'); ?></h2>
                <p><?php esc_html_e('Κατεβάστε διαθέσιμα datasheets, τεχνικά έντυπα και τον κατάλογο για το συγκεκριμένο προϊόν.', 'spek-theme'); ?></p>
            </div>

            <div class="product-download-list">
                <?php foreach ($datasheets as $index => $datasheet) : ?>
                    <a href="<?php echo esc_url($datasheet); ?>" class="product-download-item" target="_blank" rel="noopener">
                        <span class="product-download-item__type"><?php esc_html_e('PDF / Datasheet', 'spek-theme'); ?></span>
                        <strong>
                            <?php
                            echo esc_html(
                                count($datasheets) > 1
                                    ? sprintf(__('Datasheet %d', 'spek-theme'), (int) $index + 1)
                                    : __('Λήψη Datasheet', 'spek-theme')
                            );
                            ?>
                        </strong>
                        <span aria-hidden="true">↗</span>
                    </a>
                <?php endforeach; ?>

                <?php foreach ($catalogues as $index => $catalogue) : ?>
                    <a href="<?php echo esc_url($catalogue); ?>" class="product-download-item" target="_blank" rel="noopener">
                        <span class="product-download-item__type"><?php esc_html_e('Κατάλογος', 'spek-theme'); ?></span>
                        <strong>
                            <?php
                            echo esc_html(
                                count($catalogues) > 1
                                    ? sprintf(__('Κατάλογος %d', 'spek-theme'), (int) $index + 1)
                                    : __('Λήψη Καταλόγου', 'spek-theme')
                            );
                            ?>
                        </strong>
                        <span aria-hidden="true">↗</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
