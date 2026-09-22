<?php
/** SPEK custom i18n language switcher. @package SpekTheme */
if (!defined('ABSPATH')) { exit; }

$current = spek_current_language();
$scheme = is_ssl() ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
$current_url = $host ? $scheme . $host . $request_uri : home_url('/');
$el_url = spek_i18n_url($current_url, 'el');
$en_url = spek_i18n_url($current_url, 'en');

if (is_singular('spek_product')) {
    $id = get_queried_object_id();
    if ($id && spek_i18n_raw_post_translation((int) $id, 'title') === '') { $en_url = ''; }
}
?>
<div class="language-switcher" aria-label="<?php esc_attr_e('Language switcher', 'spek-theme'); ?>">
    <a href="<?php echo esc_url($el_url); ?>" class="<?php echo $current === 'el' ? 'is-active' : ''; ?>" lang="el" hreflang="el" aria-label="<?php esc_attr_e('Ελληνικά', 'spek-theme'); ?>"<?php if ($current === 'el') : ?> aria-current="true"<?php endif; ?>>
        <img src="https://flagcdn.com/gr.svg" width="24" height="16" alt="<?php esc_attr_e('Ελληνικά', 'spek-theme'); ?>">
    </a>
    <?php if ($en_url !== '') : ?>
        <a href="<?php echo esc_url($en_url); ?>" class="<?php echo $current === 'en' ? 'is-active' : ''; ?>" lang="en" hreflang="en" aria-label="English"<?php if ($current === 'en') : ?> aria-current="true"<?php endif; ?>>
            <img src="https://flagcdn.com/gb.svg" width="24" height="16" alt="English">
        </a>
    <?php else : ?>
        <span class="is-unavailable" aria-disabled="true" title="<?php esc_attr_e('Δεν υπάρχει αγγλική μετάφραση για αυτό το προϊόν.', 'spek-theme'); ?>">
            <img src="https://flagcdn.com/gb.svg" width="24" height="16" alt="English">
        </span>
    <?php endif; ?>
</div>
