<?php
/**
 * Download every product matching the current catalogue filters.
 * Reuses the already filtered main archive query (including search/SKU and sorting).
 */
if (!defined('ABSPATH')) { exit; }

/**
 * Export controls live only in wp-admin > Products.
 * The button submits the same admin list-form inputs and their current filters.
 */
function spek_product_admin_export_controls(string $post_type): void {
    if ($post_type !== 'spek_product' || !current_user_can('edit_others_posts')) { return; }
    wp_nonce_field('spek_product_export', '_export_nonce');
    echo '<label for="spek_export_format" style="margin-left:8px">', esc_html__('Εξαγωγή προϊόντων', 'spek-theme'), '</label> ';
    echo '<select id="spek_export_format" name="spek_export_format">';
    foreach (['csv'=>'CSV','xlsx'=>'XLSX','xml'=>'XML','json'=>'JSON'] as $value => $label) {
        echo '<option value="', esc_attr($value), '">', esc_html($label), '</option>';
    }
    echo '</select> ';
    echo '<button type="submit" class="button button-secondary" name="action" value="spek_product_export" formaction="', esc_url(admin_url('admin-post.php')), '" formmethod="get">', esc_html__('Εξαγωγή αποτελεσμάτων', 'spek-theme'), '</button>';
}
add_action('restrict_manage_posts', 'spek_product_admin_export_controls', 30);

function spek_product_export_query_args(): array {
    $args = [
        'post_type' => 'spek_product', 'post_status' => ['publish','draft','pending','future','private'],
        'posts_per_page' => -1, 'nopaging' => true, 'fields' => 'ids', 'no_found_rows' => true,
    ];
    if (!empty($_GET['post_status']) && !is_array($_GET['post_status'])) {
        $status = sanitize_key(wp_unslash((string) $_GET['post_status']));
        if (in_array($status, ['publish','draft','pending','future','private','trash'], true)) { $args['post_status'] = $status; }
    }
    if (!empty($_GET['s']) && !is_array($_GET['s'])) {
        $args['s'] = sanitize_text_field(wp_unslash((string) $_GET['s']));
    }
    if (!empty($_GET['m']) && !is_array($_GET['m'])) {
        $month = sanitize_text_field(wp_unslash((string) $_GET['m']));
        if (preg_match('/^\\d{6}$/', $month)) { $args['m'] = $month; }
    }
    $tax_query = [];
    foreach (['product_category','product_application','product_material','product_series'] as $tax) {
        if (empty($_GET[$tax]) || is_array($_GET[$tax])) { continue; }
        $value = sanitize_text_field(wp_unslash((string) $_GET[$tax]));
        if ($value === '0') { continue; }
        $tax_query[] = ['taxonomy'=>$tax, 'field'=>ctype_digit($value) ? 'term_id' : 'slug', 'terms'=>ctype_digit($value) ? (int)$value : $value];
    }
    if ($tax_query) { $args['tax_query'] = $tax_query; }
    if (!empty($_GET['orderby']) && !is_array($_GET['orderby'])) {
        $order = sanitize_key(wp_unslash((string) $_GET['orderby']));
        if (in_array($order, ['date','title','ID','modified','menu_order'], true)) { $args['orderby'] = $order; }
    }
    if (!empty($_GET['order']) && !is_array($_GET['order'])) {
        $direction = strtoupper(sanitize_key(wp_unslash((string) $_GET['order'])));
        if (in_array($direction, ['ASC','DESC'], true)) { $args['order'] = $direction; }
    }
    $image_status = isset($_GET['spek_image_status']) && !is_array($_GET['spek_image_status'])
        ? sanitize_key(wp_unslash((string) $_GET['spek_image_status'])) : '';
    if (in_array($image_status, ['missing','has_image'], true)) { $args['spek_export_image_status'] = $image_status; }
    return $args;
}

function spek_product_export_image_where(string $where, WP_Query $query): string {
    $status = $query->get('spek_export_image_status');
    if (!in_array($status, ['missing','has_image'], true)) { return $where; }
    global $wpdb;
    $subquery = "SELECT 1 FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} attachment ON attachment.ID = CAST(pm.meta_value AS UNSIGNED) AND attachment.post_type='attachment' AND attachment.post_status='inherit' WHERE pm.post_id={$wpdb->posts}.ID AND pm.meta_key='_thumbnail_id' AND CAST(pm.meta_value AS UNSIGNED)>0";
    return $where . ($status === 'missing' ? " AND NOT EXISTS ($subquery)" : " AND EXISTS ($subquery)");
}
add_filter('posts_where', 'spek_product_export_image_where', 30, 2);

function spek_product_export_rows(): array {
    $args = spek_product_export_query_args();
    $query = new WP_Query($args);
    $rows = [];
    foreach ($query->posts as $post_id) {
        $post_id = (int) $post_id;
        $terms = [];
        foreach (['product_category' => 'categories', 'product_application' => 'applications', 'product_material' => 'materials', 'product_series' => 'series'] as $tax => $column) {
            $names = wp_get_post_terms($post_id, $tax, ['fields' => 'names']);
            $terms[$column] = is_wp_error($names) ? '' : implode(' | ', $names);
        }
        $meta = get_post_meta($post_id);
        $public_meta = [];
        foreach ($meta as $key => $values) {
            if (in_array($key, ['product_code','_thumbnail_id'], true) || strpos($key, '_') === 0) { continue; }
            $public_meta[$key] = count($values) === 1 ? maybe_unserialize($values[0]) : array_map('maybe_unserialize', $values);
        }
        $images = [];
        $featured = get_post_thumbnail_id($post_id);
        if ($featured) { $images[] = wp_get_attachment_url($featured); }
        foreach (get_children(['post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => -1, 'fields' => 'ids']) as $attachment_id) {
            $images[] = wp_get_attachment_url($attachment_id);
        }
        $image_urls = array_values(array_unique(array_filter($images)));
        $rows[] = array_merge([
            'id' => $post_id,
            'sku' => (string) get_post_meta($post_id, 'product_code', true),
            'title' => get_the_title($post_id),
            'description' => wp_strip_all_tags(get_post_field('post_content', $post_id)),
            'excerpt' => wp_strip_all_tags(get_post_field('post_excerpt', $post_id)),
        ], $terms, [
            'main_image' => $image_urls[0] ?? '',
            'gallery' => implode(' | ', $image_urls),
            'technical_data' => wp_json_encode($public_meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'product_url' => get_permalink($post_id),
            'updated_at' => get_post_modified_time('c', true, $post_id),
        ]);
    }
    wp_reset_postdata();
    return $rows;
}

function spek_product_export_xml(array $rows): string {
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('products');
    foreach ($rows as $row) {
        $xml->startElement('product');
        foreach ($row as $key => $value) {
            $xml->writeElement($key, (string) $value);
        }
        $xml->endElement();
    }
    $xml->endElement();
    $xml->endDocument();
    return $xml->outputMemory();
}

function spek_product_export_xlsx(array $rows): ?string {
    if (!class_exists('ZipArchive')) { return null; }
    $columns = array_keys($rows[0] ?? spek_product_export_columns());
    $all = array_merge([$columns], array_map('array_values', $rows));
    $escape = static fn($s) => htmlspecialchars((string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><sheetData>';
    foreach ($all as $i => $row) {
        $sheet .= '<row r="' . ($i + 1) . '">';
        foreach ($row as $j => $value) {
            $n = $j + 1; $col = '';
            while ($n) { $n--; $col = chr(65 + $n % 26) . $col; $n = intdiv($n, 26); }
            $sheet .= '<c r="' . $col . ($i + 1) . '" t="inlineStr"><is><t xml:space="preserve">' . $escape($value) . '</t></is></c>';
        }
        $sheet .= '</row>';
    }
    $sheet .= '</sheetData><autoFilter ref="A1:N' . count($all) . '"/></worksheet>';
    $filename = tempnam(get_temp_dir(), 'spekxlsx');
    if (!$filename) { return null; }
    $zip = new ZipArchive();
    if ($zip->open($filename, ZipArchive::OVERWRITE) !== true) { @unlink($filename); return null; }
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Products" sheetId="1" r:id="rId1"/></sheets></workbook>');
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
    $zip->close();
    return $filename;
}

function spek_product_export_columns(): array {
    return array_fill_keys(['id','sku','title','description','excerpt','categories','applications','materials','series','main_image','gallery','technical_data','product_url','updated_at'], '');
}

function spek_product_export_download(): void {
    if (!current_user_can('edit_others_posts')) { wp_die('Permission denied', '', ['response'=>403]); }
    if (!isset($_GET['spek_export_format']) || is_array($_GET['spek_export_format'])) { wp_die('Invalid export format', '', ['response'=>400]); }
    $format = sanitize_key(wp_unslash((string) $_GET['spek_export_format']));
    if (!in_array($format, ['csv','xlsx','xml','json'], true)) { return; }
    $nonce = isset($_GET['_export_nonce']) && is_scalar($_GET['_export_nonce']) ? sanitize_text_field(wp_unslash((string) $_GET['_export_nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'spek_product_export')) { wp_die(esc_html__('Ο σύνδεσμος εξαγωγής έληξε. Ανανεώστε τη σελίδα.', 'spek-theme'), '', ['response'=>403]); }
    $rows = spek_product_export_rows();
    $name = 'spek-products-' . gmdate('Y-m-d-His') . '.' . $format;
    nocache_headers();
    if ($format === 'xlsx') {
        $file = spek_product_export_xlsx($rows);
        if (!$file) { wp_die('XLSX export needs PHP ZipArchive.'); }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        @unlink($file);
    } elseif ($format === 'csv') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, array_keys(spek_product_export_columns()));
        foreach ($rows as $row) { fputcsv($out, array_values($row)); }
        fclose($out);
    } elseif ($format === 'json') {
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        echo wp_json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        header('Content-Type: application/xml; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        echo spek_product_export_xml($rows);
    }
    exit;
}
add_action('admin_post_spek_product_export', 'spek_product_export_download');
