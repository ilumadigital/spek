<?php
/**
 * Homepage hero: contained, cinematic SPEK video banner.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$hero_video_base = get_template_directory_uri() . '/assets/videos/';
$hero_video_file = get_template_directory() . '/assets/videos/spek_bg.mp4';
$hero_video_name = file_exists($hero_video_file) ? 'spek_bg.mp4' : 'hero-bg.mp4';
$hero_is_en = function_exists('spek_is_english') && spek_is_english();
$hero_mechanism_images = function_exists('spek_home_mechanism_images') ? spek_home_mechanism_images() : [];
$hero_mechanism_photo = $hero_mechanism_images[0]['url'] ?? '';
?>

<div class="spek-hero-stage" data-home-hero-stage>
    <section class="spek-hero-v2 spek-hero-v2--contained" data-home-hero aria-labelledby="spek-home-hero-title">
        <div class="spek-hero-v2__shell">
            <div class="spek-hero-v2__bg" aria-hidden="true">
                <div class="spek-hero-v2__slide is-active">
                    <video class="spek-hero-v2__video" autoplay muted loop playsinline webkit-playsinline preload="auto">
                        <source src="<?php echo esc_url($hero_video_base . $hero_video_name); ?>" type="video/mp4">
                    </video>
                </div>
            </div>

            <div class="spek-hero-v2__shade" aria-hidden="true"></div>

            <?php if ($hero_mechanism_photo) : ?>
                <div class="spek-hero-v2__mechanism" aria-hidden="true">
                    <div class="spek-hero-v2__mechanism-orbit"></div>
                    <div class="spek-hero-v2__mechanism-frame">
                        <img
                            src="<?php echo esc_url($hero_mechanism_photo); ?>"
                            alt=""
                            loading="eager"
                            decoding="async"
                        >
                    </div>
                    <span class="spek-hero-v2__mechanism-caption">SPEK / <?php echo esc_html($hero_is_en ? 'Engineered in Greece' : 'Ελληνική τεχνογνωσία'); ?></span>
                </div>
            <?php endif; ?>

            <div class="spek-hero-v2__inner">
                <div class="spek-hero-v2__content">
                    <h1 id="spek-home-hero-title">
                        <?php echo esc_html($hero_is_en
                            ? 'The right flow starts with the details'
                            : 'Η σωστή ροή ξεκινά από τη λεπτομέρεια'
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
        </div>
    </section>

    <div class="spek-hero-stage__benefits why-spek--flow">
        <?php get_template_part('template-parts/home/practical-benefits'); ?>
    </div>
</div>

<style id="spek-home-hero-contained">
/* Homepage-only banner. Overrides older full-viewport hero/stage rules. */
body .front-page .spek-hero-stage {
    position: relative;
    width: 100%;
    height: auto;
    min-height: 0;
    margin: 0;
    padding: 18px 0 22px;
    overflow: visible;
    background: transparent;
}
.spek-hero-v2.spek-hero-v2--contained {
    display: block;
    position: relative;
    width: 100%;
    max-width: none;
    min-height: 0;
    height: auto;
    margin: 0;
    padding: 0;
    overflow: visible;
    isolation: isolate;
    border: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
}
.spek-hero-v2.spek-hero-v2--contained::before,
.spek-hero-v2.spek-hero-v2--contained::after {
    display: none;
}
body .front-page .spek-hero-v2--contained .spek-hero-v2__shell {
    position: relative;
    display: flex;
    align-items: stretch;
    width: min(calc(100% - 54px), 1580px);
    max-width: 1580px;
    height: auto;
    min-height: clamp(580px, 80vh, 920px);
    min-height: clamp(580px, 80svh, 920px);
    margin: 0 auto;
    overflow: hidden;
    isolation: isolate;
    border: 1px solid rgba(17, 57, 92, .10);
    border-radius: 32px;
    background: linear-gradient(112deg, #081f35 0%, #0c3556 63%, #356b91 100%);
    box-shadow: 0 23px 62px rgba(9, 35, 59, .14), 0 2px 9px rgba(9, 35, 59, .05);
}
/* Technical line work stays deliberately faint; the water is the visual. */
.spek-hero-v2--contained .spek-hero-v2__shell::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
    opacity: .29;
    background:
        linear-gradient(120deg, transparent 0 63%, rgba(220, 240, 255, .28) 63.045%, transparent 63.09%),
        linear-gradient(120deg, transparent 0 77%, rgba(220, 240, 255, .22) 77.045%, transparent 77.09%);
}
.spek-hero-stage .spek-hero-v2--contained .spek-hero-v2__bg {
    position: absolute;
    inset: 0;
    z-index: 0;
    display: block;
    width: 100%;
    height: 100%;
    overflow: hidden;
    border: 0;
    border-radius: 0;
    background-color: #113956;
    box-shadow: none;
    transform: none;
    transition: none;
    will-change: auto;
}
.spek-hero-v2--contained .spek-hero-v2__slide {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 1;
    visibility: visible;
}
.spek-hero-v2--contained .spek-hero-v2__slide .spek-hero-v2__video {
    position: absolute;
    inset: 0;
    display: block;
    width: 100%;
    height: 100%;
    max-width: none;
    object-fit: cover;
    object-position: center center;
    opacity: 1;
    filter: saturate(.94) contrast(1.045) brightness(.98);
    transform: none;
}
.spek-hero-v2--contained .spek-hero-v2__shade {
    position: absolute;
    inset: 0;
    z-index: 1;
    display: block;
    pointer-events: none;
    background:
        linear-gradient(90deg,
            rgba(5, 26, 46, .96) 0%,
            rgba(5, 26, 46, .90) 22%,
            rgba(5, 26, 46, .70) 42%,
            rgba(5, 26, 46, .35) 65%,
            rgba(5, 26, 46, .08) 100%),
        linear-gradient(180deg,
            rgba(4, 23, 40, .10) 0%,
            transparent 42%,
            rgba(4, 23, 40, .20) 100%);
}
body .front-page .spek-hero-v2--contained .spek-hero-v2__inner {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    width: 100%;
    max-width: none;
    height: auto;
    min-height: clamp(580px, 80vh, 920px);
    min-height: clamp(580px, 80svh, 920px);
    margin: 0;
    padding: 55px clamp(42px, 6.4vw, 112px);
}
.spek-hero-v2--contained .spek-hero-v2__content {
    position: relative;
    display: block;
    align-self: auto;
    width: min(100%, 760px);
    max-width: 760px;
    margin: 0;
    padding: 0 0 0 24px;
    opacity: 1;
    transform: none;
}
.spek-hero-v2--contained .spek-hero-v2__content::before {
    content: "";
    position: absolute;
    top: 9px;
    left: 0;
    width: 3px;
    height: 52px;
    border-radius: 2px;
    background: #e32946;
    box-shadow: none;
}
.spek-hero-v2--contained .spek-hero-v2__content h1 {
    max-width: 730px;
    margin: 0 0 21px;
    color: #fff;
    font-size: clamp(45px, 4.4vw, 76px);
    font-weight: 700;
    line-height: 1.035;
    letter-spacing: -.057em;
    text-wrap: balance;
    text-shadow: 0 2px 18px rgba(0, 0, 0, .1);
    opacity: 1;
    transform: none;
    transition: none;
}
.spek-hero-v2--contained .spek-hero-v2__content p {
    max-width: 590px;
    margin: 0;
    color: rgba(240, 246, 253, .88);
    font-size: clamp(16px, 1.28vw, 20px);
    line-height: 1.58;
    letter-spacing: -.008em;
    opacity: 1;
    transform: none;
    transition: none;
}
.spek-hero-v2--contained .spek-hero-v2__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 13px;
    margin: 32px 0 0;
    opacity: 1;
    transform: none;
    transition: none;
}
.spek-hero-v2--contained .spek-hero-v2__actions .button {
    min-height: 55px;
    padding: 0 27px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    box-shadow: none;
}
.spek-hero-v2--contained .spek-hero-v2__actions .button-primary {
    color: #fff;
    border: 1px solid #2879c5;
    background: #2879c5;
}
.spek-hero-v2--contained .spek-hero-v2__actions .button-primary:hover {
    color: #fff;
    border-color: #378cdb;
    background: #378cdb;
    box-shadow: 0 10px 27px rgba(4, 25, 43, .2);
}
.spek-hero-v2--contained .spek-hero-v2__actions .button-light {
    color: #fff;
    border: 1px solid rgba(255, 255, 255, .79);
    background: rgba(7, 26, 43, .16);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
.spek-hero-v2--contained .spek-hero-v2__actions .button-light:hover {
    color: #09243d;
    border-color: #fff;
    background: #fff;
}
@media (max-width: 900px) {
    body .front-page .spek-hero-v2--contained .spek-hero-v2__shell {
        width: calc(100% - 32px);
        min-height: max(600px, 80vh);
        min-height: max(600px, 80svh);
        border-radius: 26px;
    }
    body .front-page .spek-hero-v2--contained .spek-hero-v2__inner {
        min-height: max(600px, 80vh);
        min-height: max(600px, 80svh);
        padding: 46px 44px;
    }
    .spek-hero-v2--contained .spek-hero-v2__slide .spek-hero-v2__video {
        object-position: 62% center;
    }
    .spek-hero-v2--contained .spek-hero-v2__content {
        max-width: 685px;
    }
    .spek-hero-v2--contained .spek-hero-v2__content h1 {
        font-size: clamp(45px, 6.6vw, 64px);
    }
    .spek-hero-v2--contained .spek-hero-v2__shade {
        background:
            linear-gradient(90deg, rgba(5, 26, 46, .93), rgba(5, 26, 46, .66) 64%, rgba(5, 26, 46, .38)),
            linear-gradient(180deg, rgba(5, 26, 46, .05), rgba(5, 26, 46, .29));
    }
}
@media (max-width: 560px) {
    body .front-page .spek-hero-stage {
        padding: 12px 0 15px;
    }
    body .front-page .spek-hero-v2--contained .spek-hero-v2__shell {
        width: calc(100% - 20px);
        min-height: max(580px, 80vh);
        min-height: max(580px, 80svh);
        border-radius: 22px;
    }
    body .front-page .spek-hero-v2--contained .spek-hero-v2__inner {
        min-height: max(580px, 80vh);
        min-height: max(580px, 80svh);
        padding: 38px 20px;
    }
    .spek-hero-v2--contained .spek-hero-v2__slide .spek-hero-v2__video {
        object-position: 64% center;
    }
    .spek-hero-v2--contained .spek-hero-v2__content {
        width: 100%;
        padding-left: 16px;
    }
    .spek-hero-v2--contained .spek-hero-v2__content::before {
        top: 6px;
        width: 2px;
        height: 38px;
    }
    .spek-hero-v2--contained .spek-hero-v2__content h1 {
        max-width: 100%;
        margin-bottom: 18px;
        font-size: clamp(34px, 9vw, 45px);
        line-height: 1.07;
        letter-spacing: -.045em;
    }
    .spek-hero-v2--contained .spek-hero-v2__content p {
        max-width: 390px;
        font-size: 16px;
        line-height: 1.55;
    }
    .spek-hero-v2--contained .spek-hero-v2__actions {
        flex-direction: column;
        gap: 10px;
        margin-top: 26px;
    }
    .spek-hero-v2--contained .spek-hero-v2__actions .button {
        width: 100%;
        min-height: 53px;
        padding: 0 18px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .spek-hero-v2--contained .spek-hero-v2__slide .spek-hero-v2__video {
        display: none;
    }
}


/* October 2026 homepage concept: product-led engineering instead of a
   dominant water-video image. Keep the video as quiet ambient movement,
   show a REAL photographed SPEK mechanism, and nest practical benefits
   just below the banner. This is homepage-only. */
body .front-page .spek-hero-v2--contained .spek-hero-v2__shell {
    background: linear-gradient(112deg, #071c31 0%, #0b3558 54%, #164e78 100%);
}
body .front-page .spek-hero-v2--contained .spek-hero-v2__slide .spek-hero-v2__video {
    opacity: .28;
    filter: saturate(.6) contrast(1.13) brightness(.72);
}
body .front-page .spek-hero-v2--contained .spek-hero-v2__shade {
    background:
        linear-gradient(90deg, rgba(4,24,43,.94) 0%, rgba(6,32,55,.86) 34%, rgba(9,43,72,.42) 73%, rgba(9,43,72,.26) 100%),
        radial-gradient(ellipse at 88% 58%, rgba(109,195,233,.22), transparent 47%);
}
/* The linework now references technical drawings, rather than water streaks. */
body .front-page .spek-hero-v2--contained .spek-hero-v2__shell::after {
    opacity: .52;
    background:
        radial-gradient(circle at 81% 49%, transparent 0 177px, rgba(211,234,246,.24) 178px 179px, transparent 180px 232px, rgba(211,234,246,.12) 233px 234px, transparent 235px),
        linear-gradient(0deg, transparent 49.94%, rgba(201,229,245,.09) 50%, transparent 50.06%),
        linear-gradient(90deg, transparent 49.94%, rgba(201,229,245,.09) 50%, transparent 50.06%);
}
body .front-page .spek-hero-v2--contained .spek-hero-v2__content {
    z-index: 4;
}
.spek-hero-v2--contained .spek-hero-v2__mechanism {
    position: absolute;
    z-index: 3;
    top: 15%;
    right: clamp(46px, 6.3vw, 112px);
    display: grid;
    align-content: center;
    justify-items: center;
    width: clamp(255px, 27.5vw, 405px);
    height: 70%;
    pointer-events: none;
}
.spek-hero-v2--contained .spek-hero-v2__mechanism-orbit {
    position: absolute;
    inset: 2% -6% 10%;
    border: 1px solid rgba(214,236,251,.28);
    border-radius: 50%;
    box-shadow: 0 0 0 20px rgba(204,231,246,.035), 0 0 0 46px rgba(204,231,246,.025);
    animation: spekMechanismOrbit 8s ease-in-out infinite alternate;
}
.spek-hero-v2--contained .spek-hero-v2__mechanism-frame {
    position: relative;
    width: 100%;
    height: 76%;
    padding: clamp(14px, 2vw, 32px);
    overflow: hidden;
    border: 1px solid rgba(229,244,251,.54);
    border-radius: 32px 32px 82px 32px;
    background: linear-gradient(145deg, rgba(255,255,255,.98), rgba(224,242,251,.96));
    box-shadow: 0 32px 78px rgba(0,14,30,.30);
    transform: rotate(2deg);
}
.spek-hero-v2--contained .spek-hero-v2__mechanism-frame::after {
    content: "";
    position: absolute;
    inset: 12px;
    border: 1px solid rgba(12,66,107,.11);
    border-radius: 23px 23px 65px 23px;
    pointer-events: none;
}
.spek-hero-v2--contained .spek-hero-v2__mechanism-frame img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
    mix-blend-mode: multiply;
}
.spek-hero-v2--contained .spek-hero-v2__mechanism-caption {
    position: relative;
    margin-top: 27px;
    color: #e8f4fc;
    font-size: 11px;
    font-weight: 750;
    letter-spacing: .15em;
    text-align: center;
    text-transform: uppercase;
}
@keyframes spekMechanismOrbit {
    from { transform: translateY(0) rotate(-4deg); opacity: .65; }
    to { transform: translateY(-12px) rotate(4deg); opacity: 1; }
}
/* Visually join the four benefit cards to the bottom of the moving hero. */
body .front-page .spek-hero-stage__benefits.why-spek--flow {
    position: relative;
    z-index: 8;
    width: min(calc(100% - 116px), 1480px);
    max-width: 1480px;
    margin: -45px auto 12px !important;
    padding: 0 !important;
    overflow: visible;
    background: transparent !important;
    box-shadow: none !important;
}
body .front-page .spek-hero-stage__benefits.why-spek--flow .why-spek__practical {
    margin: 0;
    padding: 26px clamp(26px, 3vw, 44px) 23px;
    overflow: hidden;
    border: 1px solid rgba(175,204,222,.65);
    border-radius: 24px;
    background: #fff !important;
    box-shadow: 0 20px 60px rgba(10,47,77,.16);
}
body .front-page .spek-hero-stage__benefits.why-spek--flow .why-spek__benefits-intro {
    margin-bottom: 5px;
}
body .front-page .spek-hero-stage__benefits.why-spek--flow .why-spek__benefits {
    background: transparent !important;
}
body .front-page .spek-hero-stage__benefits.why-spek--flow .why-spek__benefit {
    padding-top: 14px;
    padding-bottom: 14px;
    background: transparent !important;
}
@media (max-width: 1240px) {
    .spek-hero-v2--contained .spek-hero-v2__mechanism {
        right: 40px;
        width: 260px;
        opacity: .87;
    }
    body .front-page .spek-hero-v2--contained .spek-hero-v2__content h1 { max-width: 620px; }
}
@media (max-width: 1080px) {
    .spek-hero-v2--contained .spek-hero-v2__mechanism { display: none; }
    body .front-page .spek-hero-stage__benefits.why-spek--flow {
        width: calc(100% - 58px);
        margin-top: -34px !important;
    }
}
@media (max-width: 680px) {
    body .front-page .spek-hero-stage__benefits.why-spek--flow {
        width: calc(100% - 32px);
        margin-top: -24px !important;
    }
    body .front-page .spek-hero-stage__benefits.why-spek--flow .why-spek__practical {
        padding: 22px 23px 14px;
        border-radius: 20px;
    }
    body .front-page .spek-hero-stage__benefits.why-spek--flow .why-spek__benefits {
        grid-template-columns: minmax(0,1fr);
    }
    body .front-page .spek-hero-stage__benefits.why-spek--flow .why-spek__benefit,
    body .front-page .spek-hero-stage__benefits.why-spek--flow .why-spek__benefit:first-child,
    body .front-page .spek-hero-stage__benefits.why-spek--flow .why-spek__benefit:nth-child(3) {
        padding: 15px 0;
        border-left: 0;
    }
}
@media (prefers-reduced-motion: reduce) {
    .spek-hero-v2--contained .spek-hero-v2__mechanism-orbit { animation: none; }
}

</style>
