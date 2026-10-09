<?php
/**
 * Minimal homepage hero with autoplay background video.
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

<section class="spek-hero-v2 spek-hero-v2--minimal" data-hero-slider data-hero-delay="6000" data-home-hero>

    <div
        class="spek-hero-v2__bg"
        aria-hidden="true"
        data-era="<?php echo esc_attr($hero_is_en ? '1990 — TODAY' : '1990 — ΣΗΜΕΡΑ'); ?>"
        data-caption="<?php echo esc_attr($hero_is_en ? 'Designed to perform. Built to last.' : 'Σχεδιασμένο να λειτουργεί. Κατασκευασμένο να αντέχει.'); ?>"
    >
        <div class="spek-hero-v2__media-brand">
            <img src="<?php echo esc_url($hero_image_base . 'brand/spek_logo.png'); ?>" alt="">
        </div>
        <div class="spek-hero-v2__slide is-active" data-hero-slide>
            <video
                class="spek-hero-v2__video"
                autoplay
                muted
                loop
                playsinline
                preload="auto"
                poster="<?php echo esc_url($hero_image_base . 'spek-website-new-factory-blue.png'); ?>"
            >
                <source src="<?php echo esc_url($hero_video_base . $hero_video_name); ?>" type="video/mp4">
            </video>
        </div>
    </div>

    <div class="spek-hero-v2__shade" aria-hidden="true"></div>

    <div class="container spek-hero-v2__inner">
        <div class="spek-hero-v2__content" data-reveal>

            <div class="spek-hero-v2__eyebrow">
                <span aria-hidden="true"></span>
                <?php echo esc_html($hero_is_en ? 'Greek engineering · Since 1990' : 'Ελληνική τεχνογνωσία · Από το 1990'); ?>
            </div>

            <h1>
                <span><?php echo esc_html($hero_is_en ? 'Reliability goes unnoticed.' : 'Η αξιοπιστία δεν φαίνεται.'); ?></span>
                <strong><?php echo esc_html($hero_is_en ? 'Until it is missing.' : 'Μέχρι να λείψει.'); ?></strong>
            </h1>

            <p>
                <?php echo esc_html($hero_is_en
                    ? 'Since 1990, SPEK has designed and produced mechanisms and solutions for sanitary and plumbing applications, where precision, durability and everyday reliability matter.'
                    : 'Από το 1990, η SPEK σχεδιάζει και παράγει μηχανισμούς και λύσεις για είδη υγιεινής και υδραυλικές εφαρμογές, εκεί όπου η ακρίβεια, η αντοχή και η καθημερινή αξιοπιστία κάνουν τη διαφορά.'
                ); ?>
            </p>

            <div class="spek-hero-v2__actions">
                <a href="<?php echo esc_url(function_exists('spek_page_url') ? spek_page_url('products') : home_url('/products/')); ?>" class="button button-primary">
                    <?php echo esc_html($hero_is_en ? 'Explore products' : 'Δείτε τη γκάμα'); ?>
                </a>

                <a href="<?php echo esc_url(function_exists('spek_page_url') ? spek_page_url('product-finder') : home_url('/product-finder/')); ?>" class="button button-light">
                    <?php echo esc_html($hero_is_en ? 'Find the right product' : 'Βρείτε το σωστό προϊόν'); ?>
                </a>
            </div>

            <div class="spek-hero-v2__micro-proof" aria-hidden="true">
                <span><?php echo esc_html($hero_is_en ? 'Design' : 'Σχεδιασμός'); ?></span>
                <span><?php echo esc_html($hero_is_en ? 'Production' : 'Παραγωγή'); ?></span>
                <span><?php echo esc_html($hero_is_en ? 'Quality control' : 'Έλεγχος ποιότητας'); ?></span>
            </div>

        </div>
    </div>

    <div class="container spek-hero-v2__facts" data-reveal>
        <article>
            <strong>1990</strong>
            <span><?php echo esc_html($hero_is_en ? 'Year founded' : 'Έτος ίδρυσης'); ?></span>
        </article>
        <article>
            <strong>8.100 m²</strong>
            <span><?php echo esc_html($hero_is_en ? 'Privately owned production base' : 'Ιδιόκτητη παραγωγική βάση'); ?></span>
        </article>
        <article>
            <strong>1992</strong>
            <span><?php echo esc_html($hero_is_en ? 'Export activity begins' : 'Έναρξη εξαγωγικής δραστηριότητας'); ?></span>
        </article>
        <article>
            <strong>35+</strong>
            <span><?php echo esc_html($hero_is_en ? 'Years of continuous evolution' : 'Χρόνια συνεχούς εξέλιξης'); ?></span>
        </article>
    </div>

</section>
</div>

<style>
.spek-hero-v2.spek-hero-v2--minimal {
    position: relative;
    height: 100vh;
    height: 100svh;
    min-height: 100vh;
    min-height: 100svh;
    width: 100%;
    max-width: none;
    margin-inline: 0;
    display: flex;
    align-items: center;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.38);
    border-radius: 0 0 clamp(120px, 14vw, 220px) 0;
    background: #dfe7ed;
    box-shadow:
        0 34px 90px rgba(15, 41, 62, 0.14),
        inset 0 -1px 0 rgba(255, 255, 255, 0.30);
    isolation: isolate;
}

.spek-hero-v2--minimal .spek-hero-v2__bg,
.spek-hero-v2--minimal .spek-hero-v2__slide {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.spek-hero-v2--minimal .spek-hero-v2__slide img,
.spek-hero-v2--minimal .spek-hero-v2__slide video {
    display: block;
    width: 100%;
    height: 100%;
    max-width: none;
    object-fit: cover;
    object-position: center center;
    filter: saturate(0.98) contrast(1.015) brightness(1.01);
    transform: scale(1.005);
}

.spek-hero-v2--minimal .spek-hero-v2__video {
    position: absolute;
    inset: 0;
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
    height: 100%;
    min-height: 100%;
    margin: 0;
    padding:
        calc(var(--spek-header-top-height, 92px) + clamp(34px, 5vh, 70px))
        clamp(48px, 7.3vw, 140px)
        clamp(54px, 7vh, 92px);
}

.spek-hero-v2--minimal .spek-hero-v2__content {
    position: relative;
    width: min(860px, 62vw);
    max-width: 860px;
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
    background: #d7233f;
    box-shadow: 0 0 0 1px rgba(215, 35, 63, 0.08);
}

.spek-hero-v2--minimal .spek-hero-v2__content h1 {
    max-width: 840px;
    margin: 0 0 30px;
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
    border-color: #174f8a;
    background: #174f8a;
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-primary:hover {
    background: #0c3764;
    border-color: #0c3764;
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

.spek-hero-v2--minimal::before {
    opacity: 0.14;
}

.spek-hero-v2--minimal::after {
    content: "";
    position: absolute;
    inset: 12px;
    z-index: 4;
    border: 1px solid rgba(255, 255, 255, 0.20);
    border-radius: 0 0 clamp(108px, 12vw, 196px) 0;
    pointer-events: none;
    box-shadow: inset 0 0 0 1px rgba(7, 31, 49, 0.025);
}

@media (min-width: 1400px) {
    .spek-hero-v2--minimal .spek-hero-v2__content h1 {
        font-size: clamp(64px, 4.65vw, 88px);
    }
}

@media (max-width: 1100px) {
    .spek-hero-v2--minimal .spek-hero-v2__content {
        width: min(720px, 68vw);
    }
}

@media (max-width: 820px) {
    .spek-hero-v2.spek-hero-v2--minimal {
        width: 100%;
        border-radius: 0 0 112px 0;
        height: 100vh;
        height: 100svh;
        min-height: 100vh;
        min-height: 100svh;
    }

    .spek-hero-v2--minimal .spek-hero-v2__inner {
        height: 100%;
        min-height: 100%;
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

    .spek-hero-v2--minimal::after {
        inset: 8px;
        border-radius: 0 0 96px 0;
    }
}

@media (max-width: 560px) {
    .spek-hero-v2.spek-hero-v2--minimal {
        width: 100%;
        border-radius: 0 0 88px 0;
        height: 100vh;
        height: 100svh;
        min-height: 100vh;
        min-height: 100svh;
    }

    .spek-hero-v2--minimal .spek-hero-v2__inner {
        height: 100%;
        min-height: 100%;
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

    .spek-hero-v2--minimal::after {
        inset: 5px;
        border-radius: 0 0 72px 0;
    }
}
</style>


<!-- spek-home-hero-corporate-v3 -->
<style id="spek-home-hero-corporate-v3">
/* Corporate / memorable homepage hero. The video stays deliberately compact
   so the 720p source remains crisp instead of being stretched full-screen. */
.spek-hero-v2.spek-hero-v2--minimal {
    position: relative;
    display: block;
    width: 100%;
    height: auto;
    min-height: 0;
    margin: 0;
    padding: clamp(72px, 6.4vw, 104px) 0 34px;
    overflow: hidden;
    border: 0;
    border-radius: 0;
    background:
        radial-gradient(circle at 82% 17%, rgba(30, 99, 160, 0.13), transparent 30%),
        radial-gradient(circle at 8% 78%, rgba(23, 79, 138, 0.065), transparent 31%),
        linear-gradient(125deg, #fbfcfd 0%, #f2f6f9 56%, #eaf1f6 100%);
    box-shadow: none;
    isolation: isolate;
}

.spek-hero-v2.spek-hero-v2--minimal::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 0;
    opacity: 0.7;
    pointer-events: none;
    background:
        linear-gradient(rgba(23, 79, 138, 0.034) 1px, transparent 1px),
        linear-gradient(90deg, rgba(23, 79, 138, 0.034) 1px, transparent 1px);
    background-size: 72px 72px;
    mask-image: linear-gradient(90deg, rgba(0,0,0,.78), rgba(0,0,0,.22) 68%, transparent 100%);
}

.spek-hero-v2.spek-hero-v2--minimal::after {
    display: none;
}

.spek-hero-v2--minimal .spek-hero-v2__bg {
    position: absolute;
    inset: clamp(72px, 6.4vw, 104px) max(36px, calc((100vw - 1400px) / 2)) auto auto;
    z-index: 1;
    width: min(46vw, 680px);
    height: auto;
    aspect-ratio: 16 / 10.2;
    overflow: hidden;
    border: 1px solid rgba(17, 56, 87, 0.14);
    border-radius: 28px 28px 92px 28px;
    background: #dce6ee;
    box-shadow:
        0 36px 90px rgba(15, 43, 66, 0.18),
        0 8px 24px rgba(15, 43, 66, 0.08),
        inset 0 0 0 1px rgba(255,255,255,0.24);
}

.spek-hero-v2--minimal .spek-hero-v2__bg::before {
    content: attr(data-era);
    position: absolute;
    top: 27px;
    right: 29px;
    z-index: 6;
    color: rgba(255,255,255,.9);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .15em;
}

.spek-hero-v2--minimal .spek-hero-v2__bg::after {
    content: attr(data-caption);
    position: absolute;
    left: 30px;
    right: 70px;
    bottom: 29px;
    z-index: 6;
    color: #fff;
    font-size: clamp(17px, 1.3vw, 22px);
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: -.025em;
    text-shadow: 0 2px 16px rgba(0,0,0,.14);
}

.spek-hero-v2--minimal .spek-hero-v2__media-brand {
    position: absolute;
    top: 24px;
    left: 28px;
    z-index: 6;
    width: 108px;
}

.spek-hero-v2--minimal .spek-hero-v2__media-brand img {
    width: 100%;
    height: auto;
    filter: brightness(0) invert(1);
    opacity: .96;
}

.spek-hero-v2--minimal .spek-hero-v2__bg > .spek-hero-v2__slide {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.spek-hero-v2--minimal .spek-hero-v2__slide video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    filter: saturate(.92) contrast(1.035) brightness(.96);
    transform: scale(1.01);
}

.spek-hero-v2--minimal .spek-hero-v2__bg > .spek-hero-v2__slide::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
    background:
        linear-gradient(180deg, rgba(6,24,39,.30) 0%, rgba(6,24,39,.03) 42%, rgba(6,24,39,.38) 100%),
        linear-gradient(90deg, rgba(8,32,51,.16), transparent 55%);
}

.spek-hero-v2--minimal .spek-hero-v2__shade {
    display: none;
}

body .front-page .spek-hero-v2--minimal .spek-hero-v2__inner {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    width: min(calc(100vw - 72px), 1400px);
    max-width: 1400px;
    min-height: 550px;
    height: auto;
    margin: 0 auto;
    padding: 0;
}

.spek-hero-v2--minimal .spek-hero-v2__content {
    width: min(47%, 655px);
    max-width: 655px;
    margin: 0;
    padding: 0;
}

.spek-hero-v2--minimal .spek-hero-v2__content::before {
    display: none;
}

.spek-hero-v2--minimal .spek-hero-v2__eyebrow {
    display: flex;
    align-items: center;
    gap: 13px;
    margin: 0 0 27px;
    color: #526b7e;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .135em;
    text-transform: uppercase;
}

.spek-hero-v2--minimal .spek-hero-v2__eyebrow span {
    width: 38px;
    height: 2px;
    flex: 0 0 auto;
    background: #d5223f;
}

.spek-hero-v2--minimal .spek-hero-v2__content h1 {
    max-width: 690px;
    margin: 0 0 28px;
    color: #0b1f32;
    font-size: clamp(54px, 5.45vw, 86px);
    font-weight: 700;
    line-height: .94;
    letter-spacing: -.064em;
    text-shadow: none;
    text-wrap: balance;
}

.spek-hero-v2--minimal .spek-hero-v2__content h1 span,
.spek-hero-v2--minimal .spek-hero-v2__content h1 strong {
    display: block;
}

.spek-hero-v2--minimal .spek-hero-v2__content h1 strong {
    color: #174f8a;
    font-weight: 700;
}

.spek-hero-v2--minimal .spek-hero-v2__content p {
    max-width: 620px;
    margin: 0;
    color: #53697b;
    font-size: clamp(17px, 1.13vw, 20px);
    line-height: 1.66;
    letter-spacing: -.012em;
}

.spek-hero-v2--minimal .spek-hero-v2__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin: 34px 0 0;
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button {
    min-height: 54px;
    padding-inline: 25px;
    border-radius: 12px;
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-primary {
    color: #fff;
    border-color: #174f8a;
    background: #174f8a;
    box-shadow: 0 16px 36px rgba(23,79,138,.20);
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-primary:hover {
    border-color: #0f3d70;
    background: #0f3d70;
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-light {
    color: #173149;
    border-color: rgba(23,49,73,.16);
    background: rgba(255,255,255,.66);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.95);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.spek-hero-v2--minimal .spek-hero-v2__actions .button-light:hover {
    color: #174f8a;
    border-color: rgba(23,79,138,.26);
    background: #fff;
}

.spek-hero-v2--minimal .spek-hero-v2__micro-proof {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 22px;
    margin-top: 31px;
    color: #6a7c8b;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
}

.spek-hero-v2--minimal .spek-hero-v2__micro-proof span {
    position: relative;
}

.spek-hero-v2--minimal .spek-hero-v2__micro-proof span:not(:last-child)::after {
    content: "";
    position: absolute;
    top: 50%;
    right: -13px;
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: #d5223f;
    transform: translateY(-50%);
}

body .front-page .spek-hero-v2--minimal .spek-hero-v2__facts {
    position: relative;
    z-index: 4;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    width: min(calc(100vw - 72px), 1400px);
    max-width: 1400px;
    margin: 22px auto 0;
    overflow: hidden;
    border: 1px solid rgba(22,63,96,.10);
    border-radius: 18px 18px 38px 18px;
    background: rgba(255,255,255,.68);
    box-shadow: 0 16px 50px rgba(21,53,78,.065);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.spek-hero-v2--minimal .spek-hero-v2__facts article {
    position: relative;
    min-height: 92px;
    padding: 20px 24px;
}

.spek-hero-v2--minimal .spek-hero-v2__facts article:not(:last-child)::after {
    content: "";
    position: absolute;
    top: 20px;
    right: 0;
    bottom: 20px;
    width: 1px;
    background: rgba(22,63,96,.10);
}

.spek-hero-v2--minimal .spek-hero-v2__facts strong,
.spek-hero-v2--minimal .spek-hero-v2__facts span {
    display: block;
}

.spek-hero-v2--minimal .spek-hero-v2__facts strong {
    margin-bottom: 5px;
    color: #153d63;
    font-size: 24px;
    line-height: 1;
    letter-spacing: -.04em;
}

.spek-hero-v2--minimal .spek-hero-v2__facts span {
    color: #687b8c;
    font-size: 12px;
    line-height: 1.35;
}

@media (max-width: 1120px) {
    body .front-page .spek-hero-v2--minimal .spek-hero-v2__inner,
    body .front-page .spek-hero-v2--minimal .spek-hero-v2__facts {
        width: min(calc(100vw - 48px), 1120px);
    }

    .spek-hero-v2--minimal .spek-hero-v2__bg {
        right: 24px;
        width: min(45vw, 560px);
    }

    .spek-hero-v2--minimal .spek-hero-v2__content h1 {
        font-size: clamp(48px, 5.7vw, 68px);
    }
}

@media (max-width: 900px) {
    .spek-hero-v2.spek-hero-v2--minimal {
        display: flex;
        flex-direction: column;
        padding: 66px 0 28px;
    }

    body .front-page .spek-hero-v2--minimal .spek-hero-v2__inner {
        order: 1;
        width: min(calc(100vw - 48px), 760px);
        min-height: auto;
    }

    .spek-hero-v2--minimal .spek-hero-v2__content {
        width: 100%;
        max-width: 760px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__content h1 {
        max-width: 760px;
        font-size: clamp(54px, 8.7vw, 76px);
    }

    .spek-hero-v2--minimal .spek-hero-v2__content p {
        max-width: 710px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__bg {
        position: relative;
        inset: auto;
        order: 2;
        width: min(calc(100vw - 48px), 760px);
        margin: 48px auto 0;
        aspect-ratio: 16 / 9.8;
    }

    body .front-page .spek-hero-v2--minimal .spek-hero-v2__facts {
        order: 3;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        width: min(calc(100vw - 48px), 760px);
        margin-top: 30px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__facts article:nth-child(2)::after {
        display: none;
    }

    .spek-hero-v2--minimal .spek-hero-v2__facts article:nth-child(-n+2) {
        border-bottom: 1px solid rgba(22,63,96,.10);
    }
}

@media (max-width: 560px) {
    .spek-hero-v2.spek-hero-v2--minimal {
        padding: 52px 0 22px;
    }

    .spek-hero-v2.spek-hero-v2--minimal::before {
        background-size: 52px 52px;
        opacity: .48;
    }

    body .front-page .spek-hero-v2--minimal .spek-hero-v2__inner,
    .spek-hero-v2--minimal .spek-hero-v2__bg,
    body .front-page .spek-hero-v2--minimal .spek-hero-v2__facts {
        width: min(calc(100vw - 32px), 100%);
    }

    .spek-hero-v2--minimal .spek-hero-v2__eyebrow {
        margin-bottom: 21px;
        font-size: 10px;
        letter-spacing: .10em;
    }

    .spek-hero-v2--minimal .spek-hero-v2__content h1 {
        margin-bottom: 23px;
        font-size: clamp(42px, 12.7vw, 58px);
        line-height: .97;
        letter-spacing: -.056em;
    }

    .spek-hero-v2--minimal .spek-hero-v2__content p {
        font-size: 16px;
        line-height: 1.6;
    }

    .spek-hero-v2--minimal .spek-hero-v2__actions {
        flex-direction: column;
        margin-top: 27px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__actions .button {
        width: 100%;
    }

    .spek-hero-v2--minimal .spek-hero-v2__micro-proof {
        gap: 8px 18px;
        margin-top: 23px;
        font-size: 9px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__bg {
        margin-top: 38px;
        aspect-ratio: 4 / 3;
        border-radius: 22px 22px 62px 22px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__bg::before {
        top: 20px;
        right: 20px;
        font-size: 8px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__bg::after {
        left: 20px;
        right: 74px;
        bottom: 20px;
        font-size: 15px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__media-brand {
        top: 18px;
        left: 20px;
        width: 84px;
    }

    body .front-page .spek-hero-v2--minimal .spek-hero-v2__facts {
        margin-top: 22px;
        border-radius: 15px 15px 30px 15px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__facts article {
        min-height: 86px;
        padding: 16px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__facts strong {
        font-size: 21px;
    }

    .spek-hero-v2--minimal .spek-hero-v2__facts span {
        font-size: 11px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .spek-hero-v2--minimal *,
    .spek-hero-v2--minimal *::before,
    .spek-hero-v2--minimal *::after {
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
    }
}
</style>
