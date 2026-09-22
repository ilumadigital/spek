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
    position: relative;
    min-height: calc(100svh - 76px);
    display: flex;
    align-items: center;
    overflow: hidden;
    background: #dfe7ed;
    isolation: isolate;
}

.spek-hero-v2--minimal .spek-hero-v2__bg,
.spek-hero-v2--minimal .spek-hero-v2__slide {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.spek-hero-v2--minimal .spek-hero-v2__slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center center;
    filter: saturate(0.98) contrast(1.015) brightness(1.01);
    transform: scale(1.005);
}

.spek-hero-v2--minimal .spek-hero-v2__shade {
    position: absolute;
    inset: 0;
    z-index: 2;
    background:
        linear-gradient(
            90deg,
            rgba(8, 24, 38, 0.62) 0%,
            rgba(8, 24, 38, 0.46) 28%,
            rgba(8, 24, 38, 0.20) 52%,
            rgba(8, 24, 38, 0.05) 76%,
            rgba(8, 24, 38, 0.00) 100%
        ),
        linear-gradient(
            180deg,
            rgba(5, 18, 30, 0.06) 0%,
            rgba(5, 18, 30, 0.02) 58%,
            rgba(5, 18, 30, 0.12) 100%
        );
}

.spek-hero-v2--minimal .spek-hero-v2__inner {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    width: 100%;
    max-width: none;
    min-height: calc(100svh - 76px);
    margin: 0;
    padding:
        clamp(88px, 11vh, 138px)
        clamp(28px, 6vw, 112px)
        clamp(72px, 9vh, 112px);
}

.spek-hero-v2--minimal .spek-hero-v2__content {
    position: relative;
    width: min(760px, 58vw);
    max-width: 760px;
    margin: 0;
    padding: 0 0 0 clamp(18px, 2vw, 30px);
}

.spek-hero-v2--minimal .spek-hero-v2__content::before {
    content: "";
    position: absolute;
    left: 0;
    top: 6px;
    width: 3px;
    height: 54px;
    background: rgba(255, 255, 255, 0.82);
}

.spek-hero-v2--minimal .spek-hero-v2__content h1 {
    max-width: 760px;
    margin: 0 0 28px;
    color: #ffffff;
    font-size: clamp(52px, 5.15vw, 82px);
    font-weight: 700;
    line-height: 0.98;
    letter-spacing: -0.055em;
    text-wrap: balance;
    text-shadow: 0 2px 20px rgba(0, 0, 0, 0.12);
}

.spek-hero-v2--minimal .spek-hero-v2__content p {
    max-width: 670px;
    margin: 0 0 38px;
    color: rgba(255, 255, 255, 0.88);
    font-size: clamp(17px, 1.2vw, 20px);
    line-height: 1.65;
    letter-spacing: -0.01em;
}

.spek-hero-v2--minimal .spek-hero-v2__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 0;
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button {
    min-height: 52px;
    padding-inline: 26px;
    border-radius: 8px;
    box-shadow: none;
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-primary {
    border-color: #0f5d97;
    background: #0f5d97;
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-primary:hover {
    background: #0b4c7d;
    border-color: #0b4c7d;
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-light {
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.62);
    background: rgba(7, 22, 34, 0.12);
    box-shadow: none;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-light:hover {
    color: #0f2537;
    background: #ffffff;
    border-color: #ffffff;
}

@media (max-width: 1100px) {
    .spek-hero-v2--minimal .spek-hero-v2__content {
        width: min(720px, 68vw);
    }
}

@media (max-width: 820px) {
    .spek-hero-v2.spek-hero-v2--minimal,
    .spek-hero-v2--minimal .spek-hero-v2__inner {
        min-height: calc(100svh - 70px);
    }

    .spek-hero-v2--minimal .spek-hero-v2__inner {
        padding:
            clamp(72px, 10vh, 100px)
            28px
            clamp(58px, 8vh, 86px);
    }

    .spek-hero-v2--minimal .spek-hero-v2__content {
        width: min(680px, 92vw);
    }

    .spek-hero-v2--minimal .spek-hero-v2__shade {
        background:
            linear-gradient(
                90deg,
                rgba(8, 24, 38, 0.68) 0%,
                rgba(8, 24, 38, 0.48) 60%,
                rgba(8, 24, 38, 0.18) 100%
            );
    }

    .spek-hero-v2--minimal .spek-hero-v2__content h1 {
        font-size: clamp(44px, 10vw, 66px);
    }
}

@media (max-width: 560px) {
    .spek-hero-v2.spek-hero-v2--minimal,
    .spek-hero-v2--minimal .spek-hero-v2__inner {
        min-height: calc(100svh - 68px);
    }

    .spek-hero-v2--minimal .spek-hero-v2__inner {
        align-items: flex-end;
        padding: 72px 20px 48px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__content {
        width: 100%;
        padding-left: 16px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__content::before {
        height: 42px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__content h1 {
        margin-bottom: 22px;
        font-size: clamp(39px, 11.5vw, 52px);
    }

    .spek-hero-v2--minimal .spek-hero-v2__content p {
        margin-bottom: 28px;
        font-size: 16px;
        line-height: 1.58;
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
