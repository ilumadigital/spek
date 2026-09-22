<?php
/**
 * Homepage hero with rotating background images.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$hero_image_base = get_template_directory_uri() . '/assets/images/iluma/webp/';

$hero_slides = [
    [
        'image' => 'spek-website-bg-0001.webp',
        'alt'   => __('Παραγωγή καλουπιού SPEK σε σύγχρονο βιομηχανικό περιβάλλον', 'spek-theme'),
        'label' => __('Injection moulding', 'spek-theme'),
    ],
    [
        'image' => 'spek-website-bg-001.webp',
        'alt'   => __('Βιομηχανικό καλούπι σε μηχανή παραγωγής SPEK', 'spek-theme'),
        'label' => __('Mould systems', 'spek-theme'),
    ],
    [
        'image' => 'spek-website-bg-002.webp',
        'alt'   => __('CNC κατεργασία μεταλλικού εξαρτήματος σε χώρο παραγωγής SPEK', 'spek-theme'),
        'label' => __('Precision machining', 'spek-theme'),
    ],
    [
        'image' => 'spek-website-bg-003.webp',
        'alt'   => __('Βιομηχανικό καλούπι με συνδέσεις σε γραμμή παραγωγής SPEK', 'spek-theme'),
        'label' => __('Tool engineering', 'spek-theme'),
    ],
    [
        'image' => 'spek-website-bg-004.webp',
        'alt'   => __('Σύγχρονη γραμμή παραγωγής με μηχανές injection moulding', 'spek-theme'),
        'label' => __('Production line', 'spek-theme'),
    ],
    [
        'image' => 'spek-website-bg-005.webp',
        'alt'   => __('Κοντινή άποψη βιομηχανικού καλουπιού της SPEK', 'spek-theme'),
        'label' => __('Manufacturing detail', 'spek-theme'),
    ],
];
?>

<section class="spek-hero-v2" data-hero-slider data-hero-delay="6000">

    <div class="spek-hero-v2__bg" aria-hidden="true">
        <?php foreach ($hero_slides as $index => $slide) : ?>
            <div class="spek-hero-v2__slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-hero-slide>
                <img
                    src="<?php echo esc_url($hero_image_base . $slide['image']); ?>"
                    alt="<?php echo esc_attr($slide['alt']); ?>"
                    loading="eager"
                    decoding="async"
                >
            </div>
        <?php endforeach; ?>
    </div>

    <div class="spek-hero-v2__shade" aria-hidden="true"></div>

    <div class="container spek-hero-v2__inner">

        <div class="spek-hero-v2__topline" data-reveal>
            <span><?php esc_html_e('SPEK Μεταλλοπλαστική ΑΒΕΕ', 'spek-theme'); ?></span>
            <span><?php esc_html_e('Plumbing Components', 'spek-theme'); ?></span>
        </div>

        <div class="spek-hero-v2__content" data-reveal style="--reveal-delay: 80ms;">

            <span class="spek-hero-v2__label"><?php esc_html_e('Greek manufacturing in motion', 'spek-theme'); ?></span>

            <h1>
                <?php esc_html_e('Η σωστή ροή ξεκινά από τη λεπτομέρεια.', 'spek-theme'); ?>
            </h1>

            <p>
                <?php esc_html_e('Προϊόντα υδραυλικών για μπάνιο, WC και τεχνικές εφαρμογές, σχεδιασμένα για καθαρή επιλογή, σωστή εγκατάσταση και καθημερινή αξιοπιστία.', 'spek-theme'); ?>
            </p>

            <div class="spek-hero-v2__actions">
                <a href="<?php echo esc_url(home_url('/products/')); ?>" class="button button-primary">
                    <?php esc_html_e('Δείτε προϊόντα', 'spek-theme'); ?>
                </a>

                <a href="<?php echo esc_url(home_url('/product-finder/')); ?>" class="button button-light">
                    <?php esc_html_e('Βρείτε το σωστό προϊόν', 'spek-theme'); ?>
                </a>
            </div>
        </div>

        <div class="spek-hero-v2__footer-row">
            <div class="spek-hero-v2__slider-meta" data-reveal style="--reveal-delay: 140ms;">
                <span class="spek-hero-v2__slider-label"><?php esc_html_e('Factory visuals', 'spek-theme'); ?></span>
                <div class="spek-hero-v2__slider-status">
                    <span data-hero-current>01</span>
                    <i aria-hidden="true"></i>
                    <span><?php echo esc_html(str_pad((string) count($hero_slides), 2, '0', STR_PAD_LEFT)); ?></span>
                </div>
                <div class="spek-hero-v2__slider-dots" data-hero-dots aria-label="<?php esc_attr_e('Εναλλαγή εικόνας hero', 'spek-theme'); ?>"></div>
            </div>

            <div class="spek-hero-v2__dock" data-reveal style="--reveal-delay: 160ms;">
                <a href="<?php echo esc_url(home_url('/products/')); ?>">
                    <span>01</span>
                    <strong><?php esc_html_e('Προϊόντα', 'spek-theme'); ?></strong>
                    <small><?php esc_html_e('Οργανωμένη γκάμα για εγκαταστάσεις.', 'spek-theme'); ?></small>
                </a>

                <a href="<?php echo esc_url(home_url('/product-finder/')); ?>">
                    <span>02</span>
                    <strong><?php esc_html_e('Finder', 'spek-theme'); ?></strong>
                    <small><?php esc_html_e('Γρήγορη επιλογή προϊόντος.', 'spek-theme'); ?></small>
                </a>

                <a href="<?php echo esc_url(home_url('/catalogues/')); ?>">
                    <span>03</span>
                    <strong><?php esc_html_e('Κατάλογοι', 'spek-theme'); ?></strong>
                    <small><?php esc_html_e('Κωδικοί και τεχνικές πληροφορίες.', 'spek-theme'); ?></small>
                </a>

                <a href="<?php echo esc_url(home_url('/partners/')); ?>">
                    <span>04</span>
                    <strong><?php esc_html_e('Συνεργάτες', 'spek-theme'); ?></strong>
                    <small><?php esc_html_e('Σημεία πώλησης και υποστήριξη.', 'spek-theme'); ?></small>
                </a>
            </div>
        </div>

    </div>

</section>
