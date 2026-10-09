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

            <div class="spek-hero-v2__inner">
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
        </div>
    </section>
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
</style>
