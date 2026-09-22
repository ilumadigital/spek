<?php
/**
 * Minimal homepage hero with rotating background images.
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
    ],
    [
        'image' => 'spek-website-bg-001.webp',
        'alt'   => __('Βιομηχανικό καλούπι σε μηχανή παραγωγής SPEK', 'spek-theme'),
    ],
    [
        'image' => 'spek-website-bg-002.webp',
        'alt'   => __('CNC κατεργασία μεταλλικού εξαρτήματος σε χώρο παραγωγής SPEK', 'spek-theme'),
    ],
    [
        'image' => 'spek-website-bg-003.webp',
        'alt'   => __('Βιομηχανικό καλούπι με συνδέσεις σε γραμμή παραγωγής SPEK', 'spek-theme'),
    ],
    [
        'image' => 'spek-website-bg-004.webp',
        'alt'   => __('Σύγχρονη γραμμή παραγωγής με μηχανές injection moulding', 'spek-theme'),
    ],
    [
        'image' => 'spek-website-bg-005.webp',
        'alt'   => __('Κοντινή άποψη βιομηχανικού καλουπιού της SPEK', 'spek-theme'),
    ],
];
?>

<section class="spek-hero-v2 spek-hero-v2--minimal" data-hero-slider data-hero-delay="6000">

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
        <div class="spek-hero-v2__content" data-reveal>

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
    </div>

</section>

<style>
.spek-hero-v2.spek-hero-v2--minimal {
    min-height: min(820px, calc(100vh - 76px));
    display: flex;
    align-items: center;
    overflow: hidden;
    background: #e8edf1;
}

.spek-hero-v2--minimal .spek-hero-v2__slide img {
    filter: saturate(1.02) contrast(1.01) brightness(0.96);
    transform: scale(1.015);
}

.spek-hero-v2--minimal .spek-hero-v2__shade {
    background:
        linear-gradient(
            90deg,
            rgba(8, 24, 38, 0.58) 0%,
            rgba(8, 24, 38, 0.38) 34%,
            rgba(8, 24, 38, 0.14) 62%,
            rgba(8, 24, 38, 0.04) 100%
        );
}

.spek-hero-v2--minimal .spek-hero-v2__inner {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    width: min(calc(100vw - var(--spek-home-gutter, 48px)), var(--spek-home-container, 1400px));
    min-height: min(820px, calc(100vh - 76px));
    padding-top: clamp(78px, 9vh, 120px);
    padding-bottom: clamp(78px, 9vh, 120px);
}

.spek-hero-v2--minimal .spek-hero-v2__content {
    width: min(760px, 100%);
    max-width: 760px;
    margin: 0;
    padding: 0;
}

.spek-hero-v2--minimal .spek-hero-v2__content h1 {
    max-width: 760px;
    margin: 0 0 26px;
    color: #fff;
    font-size: clamp(48px, 5.2vw, 78px);
    line-height: 0.98;
    letter-spacing: -0.055em;
    text-wrap: balance;
}

.spek-hero-v2--minimal .spek-hero-v2__content p {
    max-width: 680px;
    margin: 0 0 34px;
    color: rgba(255, 255, 255, 0.88);
    font-size: clamp(17px, 1.35vw, 20px);
    line-height: 1.65;
}

.spek-hero-v2--minimal .spek-hero-v2__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin: 0;
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button {
    min-height: 50px;
    padding-inline: 24px;
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-light {
    color: #fff;
    border-color: rgba(255, 255, 255, 0.52);
    background: rgba(255, 255, 255, 0.10);
    box-shadow: none;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-light:hover {
    color: #101214;
    background: #fff;
    border-color: #fff;
}

@media (max-width: 820px) {
    .spek-hero-v2.spek-hero-v2--minimal,
    .spek-hero-v2--minimal .spek-hero-v2__inner {
        min-height: 690px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__shade {
        background:
            linear-gradient(
                90deg,
                rgba(8, 24, 38, 0.58) 0%,
                rgba(8, 24, 38, 0.36) 70%,
                rgba(8, 24, 38, 0.20) 100%
            );
    }

    .spek-hero-v2--minimal .spek-hero-v2__content h1 {
        font-size: clamp(43px, 11vw, 64px);
    }
}

@media (max-width: 560px) {
    .spek-hero-v2.spek-hero-v2--minimal,
    .spek-hero-v2--minimal .spek-hero-v2__inner {
        min-height: 640px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__inner {
        padding-top: 72px;
        padding-bottom: 72px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__content h1 {
        margin-bottom: 22px;
        font-size: clamp(40px, 12vw, 54px);
    }

    .spek-hero-v2--minimal .spek-hero-v2__content p {
        margin-bottom: 28px;
        font-size: 16px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__actions {
        align-items: stretch;
        flex-direction: column;
    }

    .spek-hero-v2--minimal .spek-hero-v2__actions .button {
        width: 100%;
    }
}
</style>
