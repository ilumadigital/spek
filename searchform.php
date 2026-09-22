<?php
/** Search submits to the actual language homepage. @package SpekTheme */
if (!defined('ABSPATH')) { exit; }
$search_id = wp_unique_id('spek-search-');
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(spek_home_url()); ?>">
    <label for="<?php echo esc_attr($search_id); ?>"><?php esc_html_e('Αναζήτηση', 'spek-theme'); ?></label>
    <input type="search" id="<?php echo esc_attr($search_id); ?>" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Αναζήτηση', 'spek-theme'); ?>">
    <button type="submit" class="button button-primary"><?php esc_html_e('Αναζήτηση', 'spek-theme'); ?></button>
</form>
