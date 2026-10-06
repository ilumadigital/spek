<?php
/**
 * Interactive water-inspired homepage hero.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="spek-hero-stage" data-home-hero-stage>

<section class="spek-hero-v2 spek-hero-v2--water" data-home-hero data-parallax-wrap>

    <div class="spek-hero-v2__bg" aria-hidden="true">
        <div class="spek-water spek-water--base" data-parallax="4"></div>
        <div class="spek-water spek-water--caustics" data-parallax="7"></div>
        <div class="spek-water spek-water--flow spek-water--flow-one" data-parallax="12"></div>
        <div class="spek-water spek-water--flow spek-water--flow-two" data-parallax="9"></div>
        <div class="spek-water spek-water--ripple spek-water--ripple-one" data-parallax="18"></div>
        <div class="spek-water spek-water--ripple spek-water--ripple-two" data-parallax="14"></div>
        <div class="spek-water spek-water--ripple spek-water--ripple-three" data-parallax="10"></div>
        <div class="spek-water spek-water--light" data-parallax="8"></div>
        <div class="spek-water spek-water--glass" data-parallax="16"></div>
        <div class="spek-water spek-water--droplets" data-parallax="20"></div>
        <div class="spek-water spek-water--mist"></div>
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
</div>

<style>
.spek-hero-v2.spek-hero-v2--water {
    position: relative;
    width: 100%;
    max-width: none;
    height: 100vh;
    height: 100svh;
    min-height: 100vh;
    min-height: 100svh;
    margin-inline: 0;
    display: flex;
    align-items: center;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.24);
    border-radius: 0 0 clamp(120px, 14vw, 220px) 0;
    background: #06131f;
    box-shadow:
        0 34px 90px rgba(15,41,62,.18),
        inset 0 -1px 0 rgba(255,255,255,.18);
    isolation: isolate;
}

.spek-hero-v2--water .spek-hero-v2__bg {
    position: absolute;
    inset: 0;
    z-index: 0;
    overflow: hidden;
    background:
        radial-gradient(circle at 80% 16%, rgba(190,239,255,.30), transparent 22%),
        radial-gradient(circle at 22% 82%, rgba(20,126,205,.30), transparent 30%),
        radial-gradient(circle at 58% 54%, rgba(40,155,222,.15), transparent 34%),
        linear-gradient(135deg, #06131f 0%, #092b47 44%, #0a69aa 100%);
}

.spek-hero-v2--water .spek-water {
    position: absolute;
    pointer-events: none;
    will-change: transform, opacity;
}

.spek-hero-v2--water .spek-water--base {
    inset: -8%;
    background:
        radial-gradient(circle at 18% 18%, rgba(205,244,255,.30), transparent 22%),
        radial-gradient(circle at 66% 34%, rgba(87,196,243,.28), transparent 28%),
        radial-gradient(circle at 74% 76%, rgba(13,106,176,.40), transparent 34%),
        linear-gradient(155deg, rgba(3,22,36,.08), rgba(40,159,220,.18));
    animation: spekWaterBase 18s ease-in-out infinite alternate;
}

.spek-hero-v2--water .spek-water--caustics {
    inset: -22% -14%;
    opacity: .52;
    mix-blend-mode: screen;
    background:
        repeating-radial-gradient(
            ellipse at 48% 56%,
            transparent 0 24px,
            rgba(220,247,255,.20) 25px 28px,
            transparent 28px 58px
        ),
        repeating-radial-gradient(
            ellipse at 72% 38%,
            transparent 0 42px,
            rgba(148,223,255,.16) 43px 46px,
            transparent 46px 88px
        );
    filter: blur(.4px);
    animation: spekWaterCaustics 20s linear infinite;
}

.spek-hero-v2--water .spek-water--flow {
    width: 86vw;
    height: 30vw;
    min-width: 900px;
    min-height: 300px;
    border: 1px solid rgba(210,244,255,.28);
    border-radius: 50%;
    filter: blur(.1px);
}

.spek-hero-v2--water .spek-water--flow-one {
    left: -18vw;
    bottom: -12vw;
    transform: rotate(-7deg);
    box-shadow:
        0 -34px 90px rgba(112,217,255,.12),
        inset 0 26px 90px rgba(184,237,255,.08);
    animation: spekWaterFlowOne 13s ease-in-out infinite alternate;
}

.spek-hero-v2--water .spek-water--flow-two {
    right: -30vw;
    top: 7vh;
    transform: rotate(11deg);
    opacity: .72;
    animation: spekWaterFlowTwo 17s ease-in-out infinite alternate;
}

.spek-hero-v2--water .spek-water--ripple {
    border-radius: 50%;
    border: 1px solid rgba(220,248,255,.38);
    box-shadow:
        0 0 92px rgba(110,211,255,.12),
        inset 0 0 82px rgba(137,222,255,.07);
}

.spek-hero-v2--water .spek-water--ripple-one {
    width: 52vw;
    height: 52vw;
    min-width: 660px;
    min-height: 660px;
    left: -9vw;
    bottom: -30vw;
    animation: spekWaterRippleOne 12s ease-in-out infinite;
}

.spek-hero-v2--water .spek-water--ripple-two {
    width: 34vw;
    height: 34vw;
    min-width: 430px;
    min-height: 430px;
    right: -8vw;
    bottom: -16vw;
    opacity: .72;
    animation: spekWaterRippleTwo 15s ease-in-out infinite;
}

.spek-hero-v2--water .spek-water--ripple-three {
    width: 18vw;
    height: 18vw;
    min-width: 230px;
    min-height: 230px;
    right: 23vw;
    top: 18vh;
    opacity: .62;
    animation: spekWaterRippleThree 10s ease-in-out infinite;
}

.spek-hero-v2--water .spek-water--light {
    width: 78vw;
    height: 120vh;
    top: -34vh;
    right: -22vw;
    transform: rotate(17deg);
    opacity: .42;
    mix-blend-mode: screen;
    background: linear-gradient(
        115deg,
        transparent 12%,
        rgba(163,226,255,.03) 34%,
        rgba(255,255,255,.28) 48%,
        rgba(138,220,255,.08) 62%,
        transparent 78%
    );
    animation: spekWaterLight 11s ease-in-out infinite;
}

.spek-hero-v2--water .spek-water--glass {
    width: 58vw;
    height: 58vw;
    min-width: 700px;
    min-height: 700px;
    right: -20vw;
    top: -16vw;
    border: 1px solid rgba(255,255,255,.24);
    border-radius: 50%;
    background:
        linear-gradient(135deg, rgba(255,255,255,.09), rgba(255,255,255,.015) 46%, transparent 70%);
    box-shadow:
        inset 24px 18px 90px rgba(255,255,255,.05),
        0 0 120px rgba(123,218,255,.08);
    mix-blend-mode: screen;
    animation: spekWaterGlass 16s ease-in-out infinite alternate;
}

.spek-hero-v2--water .spek-water--droplets {
    inset: 0;
    opacity: .76;
    background:
        radial-gradient(circle at 76% 26%, rgba(255,255,255,.92) 0 2px, rgba(162,228,255,.42) 3px 5px, transparent 7px),
        radial-gradient(circle at 84% 40%, rgba(255,255,255,.78) 0 1px, rgba(162,228,255,.32) 2px 4px, transparent 6px),
        radial-gradient(circle at 69% 62%, rgba(255,255,255,.74) 0 2px, rgba(162,228,255,.28) 3px 5px, transparent 7px),
        radial-gradient(circle at 91% 66%, rgba(255,255,255,.70) 0 1px, rgba(162,228,255,.26) 2px 4px, transparent 6px),
        radial-gradient(circle at 58% 18%, rgba(255,255,255,.60) 0 1px, rgba(162,228,255,.22) 2px 3px, transparent 5px);
    filter: drop-shadow(0 0 12px rgba(147,224,255,.20));
    animation: spekWaterDroplets 13s ease-in-out infinite alternate;
}

.spek-hero-v2--water .spek-water--mist {
    inset: 0;
    background:
        radial-gradient(circle at 58% 44%, rgba(190,237,255,.13), transparent 24%),
        radial-gradient(circle at 91% 72%, rgba(121,220,255,.12), transparent 22%);
    filter: blur(18px);
    animation: spekWaterMist 14s ease-in-out infinite alternate;
}

.spek-hero-v2--water .spek-hero-v2__shade {
    position: absolute;
    inset: 0;
    z-index: 1;
    background:
        linear-gradient(
            90deg,
            rgba(3,16,27,.80) 0%,
            rgba(3,16,27,.58) 27%,
            rgba(3,16,27,.25) 52%,
            rgba(3,16,27,.04) 78%,
            rgba(3,16,27,.01) 100%
        ),
        linear-gradient(
            180deg,
            rgba(4,17,28,.20) 0%,
            rgba(4,17,28,.03) 48%,
            rgba(4,17,28,.28) 100%
        );
}

.spek-hero-v2--water .spek-hero-v2__inner {
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
        calc(var(--spek-header-top-height, 92px) + clamp(34px,5vh,70px))
        clamp(48px,7.3vw,140px)
        clamp(54px,7vh,92px);
}

.spek-hero-v2--water .spek-hero-v2__content {
    position: relative;
    width: min(860px,62vw);
    max-width: 860px;
    margin: 0;
    padding: 0 0 0 clamp(18px,2vw,30px);
}

.spek-hero-v2--water .spek-hero-v2__content::before {
    content: "";
    position: absolute;
    left: 0;
    top: 6px;
    width: 3px;
    height: 54px;
    background: #d7233f;
    box-shadow: 0 0 24px rgba(215,35,63,.18);
}

.spek-hero-v2--water .spek-hero-v2__content h1 {
    max-width: 840px;
    margin: 0 0 30px;
    color: #fff;
    font-size: clamp(52px,5.15vw,82px);
    font-weight: 700;
    line-height: .98;
    letter-spacing: -.055em;
    text-wrap: balance;
    text-shadow: 0 2px 20px rgba(0,0,0,.14);
}

.spek-hero-v2--water .spek-hero-v2__content p {
    max-width: 670px;
    margin: 0 0 38px;
    color: rgba(255,255,255,.88);
    font-size: clamp(17px,1.2vw,20px);
    line-height: 1.65;
    letter-spacing: -.01em;
}

.spek-hero-v2--water .spek-hero-v2__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 0;
}

.spek-hero-v2--water .spek-hero-v2__actions .button {
    min-height: 52px;
    padding-inline: 26px;
    border-radius: 8px;
    box-shadow: 0 12px 32px rgba(2,25,45,.14);
}

.spek-hero-v2--water .spek-hero-v2__actions .button-primary {
    border-color: #174f8a;
    background: #174f8a;
}

.spek-hero-v2--water .spek-hero-v2__actions .button-primary:hover {
    background: #0c3764;
    border-color: #0c3764;
}

.spek-hero-v2--water .spek-hero-v2__actions .button-light {
    color: #fff;
    border-color: rgba(255,255,255,.58);
    background: rgba(7,22,34,.12);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.spek-hero-v2--water .spek-hero-v2__actions .button-light:hover {
    color: #0f2537;
    background: #fff;
    border-color: #fff;
}

.spek-hero-v2--water::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
    opacity: .72;
    background:
        linear-gradient(125deg, transparent 0 56%, rgba(255,255,255,.055) 64%, rgba(255,255,255,.015) 70%, transparent 78%),
        radial-gradient(circle at 86% 16%, rgba(255,255,255,.12), transparent 18%);
    mix-blend-mode: screen;
}

.spek-hero-v2--water::after {
    content: "";
    position: absolute;
    inset: 12px;
    z-index: 4;
    border: 1px solid rgba(255,255,255,.20);
    border-radius: 0 0 clamp(108px,12vw,196px) 0;
    pointer-events: none;
    box-shadow: inset 0 0 0 1px rgba(7,31,49,.025);
}

@keyframes spekWaterBase {
    0% { transform: translate3d(var(--px,0px),var(--py,0px),0) scale(1); }
    100% { transform: translate3d(calc(var(--px,0px) - 22px),calc(var(--py,0px) + 14px),0) scale(1.045); }
}

@keyframes spekWaterCaustics {
    0% { transform: translate3d(var(--px,0px),var(--py,0px),0) rotate(0deg) scale(1); }
    50% { opacity: .46; }
    100% { transform: translate3d(calc(var(--px,0px) + 34px),calc(var(--py,0px) - 22px),0) rotate(3deg) scale(1.04); }
}

@keyframes spekWaterFlowOne {
    0% { transform: translate3d(var(--px,0px),var(--py,0px),0) rotate(-7deg) scale(1); }
    100% { transform: translate3d(calc(var(--px,0px) + 24px),calc(var(--py,0px) - 12px),0) rotate(-4deg) scale(1.06); }
}

@keyframes spekWaterFlowTwo {
    0% { transform: translate3d(var(--px,0px),var(--py,0px),0) rotate(11deg) scale(1); }
    100% { transform: translate3d(calc(var(--px,0px) - 28px),calc(var(--py,0px) + 16px),0) rotate(7deg) scale(1.05); }
}

@keyframes spekWaterRippleOne {
    0%,100% { transform: translate3d(var(--px,0px),var(--py,0px),0) scale(.96); opacity: .36; }
    50% { transform: translate3d(calc(var(--px,0px) + 10px),calc(var(--py,0px) - 8px),0) scale(1.04); opacity: .62; }
}

@keyframes spekWaterRippleTwo {
    0%,100% { transform: translate3d(var(--px,0px),var(--py,0px),0) scale(.94); opacity: .34; }
    50% { transform: translate3d(calc(var(--px,0px) - 12px),calc(var(--py,0px) + 8px),0) scale(1.05); opacity: .58; }
}

@keyframes spekWaterRippleThree {
    0%,100% { transform: translate3d(var(--px,0px),var(--py,0px),0) scale(.88); opacity: .20; }
    50% { transform: translate3d(calc(var(--px,0px) + 8px),calc(var(--py,0px) - 10px),0) scale(1.03); opacity: .48; }
}

@keyframes spekWaterLight {
    0%,100% { transform: translate3d(var(--px,0px),var(--py,0px),0) rotate(17deg); opacity: .20; }
    50% { transform: translate3d(calc(var(--px,0px) - 24px),calc(var(--py,0px) + 16px),0) rotate(19deg); opacity: .46; }
}

@keyframes spekWaterGlass {
    0% { transform: translate3d(var(--px,0px),var(--py,0px),0) rotate(-2deg) scale(.98); opacity: .52; }
    100% { transform: translate3d(calc(var(--px,0px) - 18px),calc(var(--py,0px) + 12px),0) rotate(2deg) scale(1.03); opacity: .82; }
}

@keyframes spekWaterDroplets {
    0% { transform: translate3d(var(--px,0px),var(--py,0px),0); opacity: .52; }
    100% { transform: translate3d(calc(var(--px,0px) + 12px),calc(var(--py,0px) - 18px),0); opacity: .86; }
}

@keyframes spekWaterMist {
    0% { transform: translate3d(-10px,6px,0) scale(1); opacity: .50; }
    100% { transform: translate3d(12px,-8px,0) scale(1.04); opacity: .76; }
}

@media (prefers-reduced-motion: reduce) {
    .spek-hero-v2--water .spek-water {
        animation: none !important;
    }
}

@media (max-width: 1100px) {
    .spek-hero-v2--water .spek-hero-v2__content {
        width: min(720px,68vw);
    }
}

@media (max-width: 820px) {
    .spek-hero-v2.spek-hero-v2--water {
        border-radius: 0 0 112px 0;
    }

    .spek-hero-v2--water .spek-hero-v2__inner {
        padding:
            clamp(72px,10vh,100px)
            28px
            clamp(58px,8vh,86px);
    }

    .spek-hero-v2--water .spek-hero-v2__content {
        width: min(680px,92vw);
    }

    .spek-hero-v2--water .spek-hero-v2__content h1 {
        font-size: clamp(44px,10vw,66px);
    }

    .spek-hero-v2--water .spek-water--ripple-one {
        left: -62vw;
        bottom: -32vw;
    }

    .spek-hero-v2--water .spek-water--ripple-two {
        right: -42vw;
    }

    .spek-hero-v2--water::after {
        inset: 8px;
        border-radius: 0 0 96px 0;
    }
}

@media (max-width: 560px) {
    .spek-hero-v2.spek-hero-v2--water {
        border-radius: 0 0 88px 0;
    }

    .spek-hero-v2--water .spek-hero-v2__inner {
        align-items: flex-end;
        padding: 72px 20px 48px;
    }

    .spek-hero-v2--water .spek-hero-v2__content {
        width: 100%;
        padding-left: 16px;
    }

    .spek-hero-v2--water .spek-hero-v2__content::before {
        height: 42px;
    }

    .spek-hero-v2--water .spek-hero-v2__content h1 {
        margin-bottom: 22px;
        font-size: clamp(39px,11.5vw,52px);
    }

    .spek-hero-v2--water .spek-hero-v2__content p {
        margin-bottom: 28px;
        font-size: 16px;
        line-height: 1.58;
    }

    .spek-hero-v2--water .spek-hero-v2__actions {
        align-items: stretch;
        flex-direction: column;
    }

    .spek-hero-v2--water .spek-hero-v2__actions .button {
        width: 100%;
    }

    .spek-hero-v2--water .spek-water--ripple-three {
        display: none;
    }

    .spek-hero-v2--water::after {
        inset: 5px;
        border-radius: 0 0 72px 0;
    }
}
</style>
