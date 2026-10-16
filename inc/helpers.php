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
if (!function_exists('spek_product_category_icon_asset')) {
    /**
     * Return the shared category icon asset URL.
     *
     * Accepts both category group keys and the legacy icon keys used by
     * homepage / mega-menu category definitions.
     */
    function spek_product_category_icon_asset(string $icon): string
    {
        $aliases = [
            'mixanismoi' => 'mechanisms.svg',
            'mechanisms' => 'mechanisms.svg',
            'floter' => 'float.svg',
            'float' => 'float.svg',
            'sifonia' => 'siphon.svg',
            'siphon' => 'siphon.svg',
            'kalymmata-lekanis' => 'seat.svg',
            'seat' => 'seat.svg',
            'kazanakia' => 'cistern.svg',
            'cistern' => 'cistern.svg',
            'banio' => 'bathroom.svg',
            'bathroom' => 'bathroom.svg',
            'lastixa' => 'seals.svg',
            'seals' => 'seals.svg',
            'diafora' => 'misc.svg',
            'misc' => 'misc.svg',
        ];

        $filename = $aliases[$icon] ?? 'misc.svg';
        $relative_path = '/assets/images/categories/' . $filename;
        $file_path = get_template_directory() . $relative_path;
        $asset_url = get_template_directory_uri() . $relative_path;

        // Category icons are shared by homepage, mega menu and filters.
        // Version the URL from the actual file mtime so a replaced SVG can
        // never remain stuck in browser/CDN cache on one surface.
        if (is_file($file_path)) {
            $asset_url = add_query_arg('ver', (string) filemtime($file_path), $asset_url);
        }

        return $asset_url;
    }
}

if (!function_exists('spek_render_product_category_icon')) {
    /**
     * Render a shared category icon image.
     */
    function spek_render_product_category_icon(string $icon, string $class = 'spek-category-icon'): void
    {
        echo '<img'
            . ' src="' . esc_url(spek_product_category_icon_asset($icon)) . '"'
            . ' class="' . esc_attr($class) . '"'
            . ' alt=""'
            . ' aria-hidden="true"'
            . ' loading="eager"'
            . ' decoding="async"'
            . '>';
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


/**
 * Source homepage/company value-card visuals from REAL SPEK product photos.
 * Curated product codes identify pneumatic cistern mechanisms; if a particular
 * code has no photo, prefer another photographed product from the mechanism
 * taxonomy. Never fall back to unrelated stock/AI photographs.
 *
 * @return array<int,array{url:string,id:int}>
 */
if (!function_exists('spek_home_mechanism_images')) {
    function spek_home_mechanism_images(): array
    {
        static $images = null;
        if ($images !== null) {
            return $images;
        }

        $images = array_fill(0, 4, ['url' => '', 'id' => 0]);
        $preferred_codes = [
            ['11001', '11002', '11007', '16017'],
            ['17013', '17958', '17011', '17012'],
            ['27142', '27141', '27012', '27021'],
            ['12069', '12075', '12065', '12070'],
        ];
        $all_codes = array_values(array_unique(array_merge(...$preferred_codes)));
        $photo_by_code = [];

        $preferred_ids = get_posts([
            'post_type' => 'spek_product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'suppress_filters' => true,
            'meta_query' => [
                ['key' => 'product_code', 'value' => $all_codes, 'compare' => 'IN'],
                ['key' => '_thumbnail_id', 'compare' => 'EXISTS'],
            ],
        ]);

        foreach ($preferred_ids as $product_id) {
            $product_id = (int) $product_id;
            $code = trim((string) get_post_meta($product_id, 'product_code', true));
            $photo = get_the_post_thumbnail_url($product_id, 'large');
            if ($code !== '' && $photo && !isset($photo_by_code[$code])) {
                $photo_by_code[$code] = ['url' => $photo, 'id' => $product_id];
            }
        }

        $used = [];
        foreach ($preferred_codes as $slot => $codes) {
            foreach ($codes as $code) {
                if (isset($photo_by_code[$code]) && !isset($used[$photo_by_code[$code]['id']])) {
                    $images[$slot] = $photo_by_code[$code];
                    $used[$images[$slot]['id']] = true;
                    break;
                }
            }
        }

        // Fill only missing slots, and only with photographed mechanisms.
        if (count($used) < 4 && function_exists('spek_product_category_group_term_ids')) {
            $term_ids = spek_product_category_group_term_ids(['mixanismoi']);
            if ($term_ids) {
                $fallback_ids = get_posts([
                    'post_type' => 'spek_product',
                    'post_status' => 'publish',
                    'posts_per_page' => 100,
                    'fields' => 'ids',
                    'no_found_rows' => true,
                    'suppress_filters' => true,
                    'tax_query' => [[
                        'taxonomy' => 'product_category',
                        'field' => 'term_id',
                        'terms' => array_map('intval', $term_ids),
                    ]],
                    'meta_query' => [['key' => '_thumbnail_id', 'compare' => 'EXISTS']],
                ]);

                foreach ($fallback_ids as $product_id) {
                    $product_id = (int) $product_id;
                    if (isset($used[$product_id])) {
                        continue;
                    }
                    $title = (string) get_post_field('post_title', $product_id);
                    if (!preg_match('/μηχ|mechanis|duo|αέρ|aero/iu', $title)) {
                        continue;
                    }
                    $photo = get_the_post_thumbnail_url($product_id, 'large');
                    if (!$photo) {
                        continue;
                    }
                    foreach ($images as $slot => $image) {
                        if ($image['url'] === '') {
                            $images[$slot] = ['url' => $photo, 'id' => $product_id];
                            $used[$product_id] = true;
                            break;
                        }
                    }
                    if (count($used) >= 4) {
                        break;
                    }
                }
            }
        }

        return $images;
    }
}
