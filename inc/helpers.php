<?php
/**
 * Helper functions.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('spek_primary_menu_fallback')) {
    /**
     * Fallback primary menu used before a WordPress menu is assigned.
     *
     * @param array $args wp_nav_menu fallback arguments.
     */
    function spek_primary_menu_fallback(array $args = []): void
    {
        $menu_class = !empty($args['menu_class']) ? $args['menu_class'] : 'site-nav__menu';
        if (function_exists('spek_allow_hardcoded_menu_fallback') && !spek_allow_hardcoded_menu_fallback()) {
            echo '<ul id="' . esc_attr($args['menu_id'] ?? 'spek-fallback-menu') . '" class="' . esc_attr($menu_class) . '"></ul>';
            return;
        }

        $products_url = get_post_type_archive_link('spek_product');

        $items = [
            [
                'label' => __('Αρχική', 'spek-theme'),
                'url'   => spek_page_url(''),
            ],
            [
                'label' => __('Προϊόντα', 'spek-theme'),
                'url'   => $products_url ? $products_url : spek_page_url('products/'),
            ],
            [
                'label' => __('Κατάλογος', 'spek-theme'),
                'url'   => spek_page_url('catalogues/'),
            ],
            [
                'label' => __('Σημεία Πώλησης', 'spek-theme'),
                'url'   => spek_page_url('partners/'),
            ],
            [
                'label' => __('Η εταιρεία', 'spek-theme'),
                'url'   => spek_page_url('company/'),
            ],
            [
                'label' => __('Επικοινωνία', 'spek-theme'),
                'url'   => spek_page_url('contact/'),
            ],
        ];

        echo '<ul id="' . esc_attr($args['menu_id'] ?? 'spek-fallback-menu') . '" class="' . esc_attr($menu_class) . '">';

        foreach ($items as $item) {
            echo '<li class="menu-item">';
            echo '<a href="' . esc_url($item['url']) . '">' . esc_html($item['label']) . '</a>';
            echo '</li>';
        }

        echo '</ul>';
    }
}
if (!function_exists('spek_product_value_is_empty')) {
    /**
     * Determine whether a product field should be considered empty.
     * Keeps values such as "0" visible.
     */
    function spek_product_value_is_empty($value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value, static function ($item) {
                return !spek_product_value_is_empty($item);
            })) === 0;
        }

        return $value === '' || $value === null || $value === false;
    }
}

if (!function_exists('spek_get_product_field')) {
    /**
     * Read a product value from native post meta first and fall back to ACF.
     * This keeps imported products and manually-entered ACF products compatible.
     */
    function spek_get_product_field(string $key, int $post_id = 0)
    {
        $post_id = $post_id ?: get_the_ID();
        $value = get_post_meta($post_id, $key, true);

        if (!spek_product_value_is_empty($value)) {
            return $value;
        }

        if (function_exists('get_field')) {
            $acf_value = get_field($key, $post_id);
            if (!spek_product_value_is_empty($acf_value)) {
                return $acf_value;
            }
        }

        return '';
    }
}

if (!function_exists('spek_product_value_to_text')) {
    /** Convert common custom-field values to readable text. */
    function spek_product_value_to_text($value): string
    {
        if (is_bool($value)) {
            return $value ? __('Ναι', 'spek-theme') : __('Όχι', 'spek-theme');
        }

        if (is_scalar($value)) {
            return trim(wp_strip_all_tags((string) $value));
        }

        if (!is_array($value)) {
            return '';
        }

        $parts = [];

        foreach ($value as $key => $item) {
            if (is_array($item)) {
                if (!empty($item['label']) && isset($item['value'])) {
                    $parts[] = trim(wp_strip_all_tags((string) $item['label'])) . ': ' . spek_product_value_to_text($item['value']);
                    continue;
                }

                $text = spek_product_value_to_text($item);
                if ($text !== '') {
                    $parts[] = $text;
                }
                continue;
            }

            if (is_scalar($item) && (string) $item !== '') {
                if (!is_int($key) && !ctype_digit((string) $key)) {
                    $parts[] = ucwords(str_replace(['_', '-'], ' ', (string) $key)) . ': ' . trim(wp_strip_all_tags((string) $item));
                } else {
                    $parts[] = trim(wp_strip_all_tags((string) $item));
                }
            }
        }

        return implode(', ', array_values(array_unique(array_filter($parts))));
    }
}

if (!function_exists('spek_get_product_resource_urls')) {
    /**
     * Normalize one or more product media/file fields to URL strings.
     * Supports plain URLs, newline-separated URLs, ACF file arrays and attachment IDs.
     */
    function spek_get_product_resource_urls(int $post_id, array $keys): array
    {
        $urls = [];

        $collect = static function ($value) use (&$urls, &$collect): void {
            if (spek_product_value_is_empty($value)) {
                return;
            }

            if (is_array($value)) {
                if (!empty($value['url'])) {
                    $collect($value['url']);
                    return;
                }

                if (!empty($value['URL'])) {
                    $collect($value['URL']);
                    return;
                }

                foreach ($value as $item) {
                    $collect($item);
                }
                return;
            }

            if (is_numeric($value)) {
                $attachment_url = wp_get_attachment_url((int) $value);
                if ($attachment_url) {
                    $urls[] = $attachment_url;
                }
                return;
            }

            if (!is_scalar($value)) {
                return;
            }

            $value = trim((string) $value);
            if ($value === '') {
                return;
            }

            $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '' && filter_var($line, FILTER_VALIDATE_URL)) {
                    $urls[] = esc_url_raw($line);
                }
            }
        };

        foreach ($keys as $key) {
            $collect(spek_get_product_field((string) $key, $post_id));
        }

        return array_values(array_unique(array_filter($urls)));
    }
}
