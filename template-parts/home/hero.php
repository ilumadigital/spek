<?php
/**
 * Homepage hero — compact cinematic corporate treatment.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$hero_video_base = get_template_directory_uri() . '/assets/videos/';
$hero_image_base = get_template_directory_uri() . '/assets/images/';
$hero_video_file = get_template_directory() . '/assets/videos/spek_bg.mp4';
$hero_video_name = file_exists($hero_video_file) ? 'spek_bg.mp4' : 'hero-bg.mp4';
$hero_is_en = function_exists('spek_is_english') && spek_is_english();
?>

<div class="spek-hero-stage" data-home-hero-stage>
    <section class="spek-hero-v2 spek-hero-v2--minimal spek-hero-v2--cinematic" data-home-hero aria-labelledby="spek-home-hero-title">

        <div class="spek-hero-v2__bg" aria-hidden="true"
             style="background-image: url('<?php echo esc_url($hero_image_base . 'spek-website-new-factory-blue.png'); ?>');">
            <div class="spek-hero-v2__slide is-active">
                <video class="spek-hero-v2__video" autoplay muted loop playsinline preload="metadata"
                       poster="<?php echo esc_url($hero_image_base . 'spek-website-new-factory-blue.png'); ?>">
                    <source src="<?php echo esc_url($hero_video_base . $hero_video_name); ?>" type="video/mp4">
                </video>
            </div>
        </div>

        <div class="spek-hero-v2__shade" aria-hidden="true"></div>

        <div class="container spek-hero-v2__inner">
            <div class="spek-hero-v2__content">
                <h1 id="spek-home-hero-title">
                    <?php echo esc_html($hero_is_en
                        ? 'The right flow starts with the details.'
                        : 'Η σωστή ροή ξεκινά από τη λεπτομέρεια.'
                    ); ?>
                </h1>

                <p>
                    <?php echo esc_html($hero_is_en
                        ? 'Reliable plumbing solutions for bathrooms, WCs and technical applications.'
                        : 'Αξιόπιστες λύσεις υδραυλικών για μπάνιο, WC και τεχνικές εφαρμογές.'
                    ); ?>
                </p>

                <div class="spek-hero-v2__actions">
                    <a href="<?php echo esc_url(function_exists('spek_page_url') ? spek_page_url('products') : home_url('/products/')); ?>" class="button button-primary">
                        <?php echo esc_html($hero_is_en ? 'Explore products' : 'Δείτε προϊόντα'); ?>
                    </a>
                    <a href="<?php echo esc_url(function_exists('spek_page_url') ? spek_page_url('product-finder') : home_url('/product-finder/')); ?>" class="button button-light">
                        <?php echo esc_html($hero_is_en ? 'Find the right product' : 'Βρείτε το σωστό προϊόν'); ?>
                    </a>
                </div>
            </div>
        </div>

    </section>
</div>

<style id="spek-home-hero-cinematic">
/*
 * The video is part of the full hero background, without a visible card.
 * Its rendered area is limited to the right side to avoid upscaling the
 * original video to 4K-sized desktop displays.
 */
body .front-page .spek-hero-stage {
    min-height: 0;
    height: auto;
    overflow: hidden;
    background: #091d30;
}

.spek-hero-v2.spek-hero-v2--cinematic {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    max-width: none;
    height: auto;
    min-height: clamp(520px, 63vh, 650px);
    margin: 0;
    padding: 84px 0 88px;
    overflow: hidden;
    isolation: isolate;
    border: 0;
    border-radius: 0 0 clamp(56px, 7vw, 100px) 0;
    background: linear-gradient(115deg, #091d30 0%, #0b2845 70%, #154c75 100%);
    box-shadow: none;
}

.spek-hero-v2.spek-hero-v2--cinematic::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
    opacity: .20;
    mix-blend-mode: screen;
    background:
        linear-gradient(118deg, transparent 0 57%, rgba(151, 205, 243, .18) 57.05%, transparent 57.2%),
        linear-gradient(118deg, transparent 0 73%, rgba(151, 205, 243, .15) 73.05%, transparent 73.2%);
}

.spek-hero-v2.spek-hero-v2--cinematic::after {
    display: none;
}

.spek-hero-stage .spek-hero-v2--cinematic .spek-hero-v2__bg {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: auto;
    z-index: 0;
    width: 69%;
    height: 100%;
    overflow: hidden;
    background-color: #0e3756;
    background-position: center center;
    background-size: cover;
    border: 0;
    border-radius: 0;
    box-shadow: none;
    transform: none;
    transition: none;
    will-change: auto;
}

.spek-hero-v2--cinematic .spek-hero-v2__slide {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 1;
    visibility: visible;
}

.spek-hero-v2--cinematic .spek-hero-v2__slide .spek-hero-v2__video {
    position: absolute;
    inset: 0;
    display: block;
    width: 100%;
    height: 100%;
    max-width: none;
    object-fit: cover;
    object-position: center;
    opacity: .96;
    filter: saturate(.87) contrast(1.06) brightness(.88);
    transform: none;
}

.spek-hero-v2--cinematic .spek-hero-v2__shade {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    background:
        linear-gradient(90deg,
            #091d30 0%,
            #091d30 24%,
            rgba(9,29,48,.97) 37%,
            rgba(9,29,48,.81) 51%,
            rgba(9,29,48,.29) 78%,
            rgba(9,29,48,.12) 100%),
        linear-gradient(180deg,
            rgba(6,21,35,.22) 0%,
            rgba(6,21,35,.02) 41%,
            rgba(6,21,35,.30) 100%);
}

body .front-page .spek-hero-v2--cinematic .spek-hero-v2__inner {
    position: relative;
    z-index: 3;
    display: block;
    width: min(calc(100% - 84px), 1260px);
    max-width: 1260px;
    height: auto;
    min-height: 0;
    margin: 0 auto;
    padding: 0;
}

.spek-hero-v2--cinematic .spek-hero-v2__content {
    position: relative;
    display: block;
    align-self: auto;
    width: min(100%, 735px);
    max-width: 735px;
    margin: 0;
    padding: 0 0 0 23px;
    opacity: 1;
    transform: none;
}

.spek-hero-v2--cinematic .spek-hero-v2__content::before {
    content: "";
    position: absolute;
    top: 7px;
    left: 0;
    width: 3px;
    height: 51px;
    background: #d92643;
    box-shadow: none;
}

.spek-hero-v2--cinematic .spek-hero-v2__content h1 {
    max-width: 730px;
    margin: 0 0 22px;
    color: #fff;
    font-size: clamp(47px, 4.35vw, 72px);
    font-weight: 700;
    line-height: 1.055;
    letter-spacing: -.053em;
    text-wrap: balance;
    text-shadow: 0 2px 20px rgba(0, 0, 0, .13);
    opacity: 1;
    transform: none;
    transition: none;
}

.spek-hero-v2--cinematic .spek-hero-v2__content p {
    max-width: 570px;
    margin: 0;
    color: rgba(235,244,252,.84);
    font-size: clamp(16px, 1.25vw, 19px);
    line-height: 1.65;
    letter-spacing: -.005em;
    opacity: 1;
    transform: none;
    transition: none;
}

.spek-hero-v2--cinematic .spek-hero-v2__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin: 31px 0 0;
    opacity: 1;
    transform: none;
    transition: none;
}

.spek-hero-v2--cinematic .spek-hero-v2__actions .button {
    min-height: 52px;
    padding: 0 24px;
    border-radius: 9px;
    font-size: 14px;
    font-weight: 700;
    box-shadow: none;
}

.spek-hero-v2--cinematic .spek-hero-v2__actions .button-primary {
    color: #fff;
    border-color: #2368a7;
    background: #2368a7;
}

.spek-hero-v2--cinematic .spek-hero-v2__actions .button-primary:hover {
    color: #fff;
    border-color: #347fbd;
    background: #347fbd;
    box-shadow: 0 12px 32px rgba(2,16,31,.25);
}

.spek-hero-v2--cinematic .spek-hero-v2__actions .button-light {
    color: #fff;
    border-color: rgba(255,255,255,.65);
    background: rgba(6,24,40,.16);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
}

.spek-hero-v2--cinematic .spek-hero-v2__actions .button-light:hover {
    color: #0a2742;
    border-color: #fff;
    background: #fff;
}

@media (max-width: 900px) {
    .spek-hero-v2.spek-hero-v2--cinematic {
        min-height: 550px;
        padding: 87px 0 90px;
        border-radius: 0 0 72px 0;
    }

    .spek-hero-stage .spek-hero-v2--cinematic .spek-hero-v2__bg {
        width: 100%;
        opacity: .82;
    }

    .spek-hero-v2--cinematic .spek-hero-v2__shade {
        background:
            linear-gradient(90deg, rgba(8,27,45,.95), rgba(8,27,45,.75) 55%, rgba(8,27,45,.47)),
            linear-gradient(180deg, rgba(8,27,45,.10), rgba(8,27,45,.43));
    }

    body .front-page .spek-hero-v2--cinematic .spek-hero-v2__inner {
        width: min(calc(100% - 52px), 720px);
    }

    .spek-hero-v2--cinematic .spek-hero-v2__content {
        width: min(100%, 660px);
    }

    .spek-hero-v2--cinematic .spek-hero-v2__content h1 {
        font-size: clamp(45px, 6.7vw, 64px);
    }
}

@media (max-width: 560px) {
    .spek-hero-v2.spek-hero-v2--cinematic {
        min-height: 590px;
        padding: 76px 0 72px;
        border-radius: 0 0 52px 0;
    }

    body .front-page .spek-hero-v2--cinematic .spek-hero-v2__inner {
        width: calc(100% - 38px);
    }

    .spek-hero-v2--cinematic .spek-hero-v2__content {
        width: 100%;
        padding-left: 15px;
    }

    .spek-hero-v2--cinematic .spek-hero-v2__content::before {
        top: 5px;
        width: 2px;
        height: 39px;
    }

    .spek-hero-v2--cinematic .spek-hero-v2__content h1 {
        max-width: 100%;
        margin-bottom: 21px;
        font-size: clamp(36px, 9.5vw, 51px);
        line-height: 1.08;
        letter-spacing: -.045em;
    }

    .spek-hero-v2--cinematic .spek-hero-v2__content p {
        max-width: 390px;
        font-size: 16px;
        line-height: 1.6;
    }

    .spek-hero-v2--cinematic .spek-hero-v2__actions {
        flex-direction: column;
        gap: 10px;
        margin-top: 25px;
    }

    .spek-hero-v2--cinematic .spek-hero-v2__actions .button {
        width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .spek-hero-v2--cinematic .spek-hero-v2__video {
        display: none;
    }
}
</style>
