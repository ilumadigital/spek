<?php
/** CLI regression tests: php tests/test-product-taxonomy-corrections.php */
define('ABSPATH', __DIR__ . '/');
$GLOBALS['products'] = [
    101 => ['sku' => '10062', 'status' => 'publish', 'terms' => ['product_series' => [1], 'product_category' => [10]]],
    102 => ['sku' => '10064', 'status' => 'publish', 'terms' => ['product_series' => [1], 'product_category' => [11]]],
];
$GLOBALS['terms'] = [
    'product_series' => [1 => 'SPEK', 2 => 'LB Plast', 3 => 'McAlpine', 4 => 'Nemo'],
    'product_category' => [10 => 'Καζανάκια', 11 => 'Μπάνιο', 12 => 'Σιφόνια', 13 => 'Διάφορα'],
];
$GLOBALS['writes'] = [];
$GLOBALS['fail_taxonomy'] = '';
class WP_Error { public function get_error_message() { return 'WP error'; } }
class Fake_DB {
    public $posts = 'wp_posts';
    public $postmeta = 'wp_postmeta';
    public function prepare($sql, ...$args) { return $args; }
    public function get_col($args) {
        $matches = [];
        foreach ($GLOBALS['products'] as $id => $product) {
            if ($product['sku'] === $args[2]) $matches[] = $id;
        }
        return array_slice($matches, 0, 2);
    }
}
$GLOBALS['wpdb'] = new Fake_DB();
function add_action(...$args) {}
function taxonomy_exists($tax) {return isset($GLOBALS['terms'][$tax]);}
function get_terms($args) {
    $result = [];
    foreach ($GLOBALS['terms'][$args['taxonomy']] as $id => $name) {
        if ($name === $args['name']) $result[] = (object) ['term_id' => $id, 'name' => $name];
    }
    return $result;
}
function get_term($id, $tax) {return isset($GLOBALS['terms'][$tax][$id]) ? (object) ['name' => $GLOBALS['terms'][$tax][$id]] : null;}
function is_wp_error($v) {return $v instanceof WP_Error;}
function get_post_type($id) {return isset($GLOBALS['products'][$id]) ? 'spek_product' : null;}
function get_post_status($id) {return $GLOBALS['products'][$id]['status'] ?? null;}
function get_post_meta($id, $key, $single) {return $key === 'product_code' ? ($GLOBALS['products'][$id]['sku'] ?? '') : '';}
function wp_get_object_terms($id, $tax, $args) {return $GLOBALS['products'][$id]['terms'][$tax] ?? [];}
function wp_set_object_terms($id, $ids, $tax, $append) {
    if ($append !== false) throw new RuntimeException('Must REPLACE taxonomy assignments');
    if ($GLOBALS['fail_taxonomy'] === $tax) { $GLOBALS['fail_taxonomy'] = ''; return new WP_Error(); }
    $GLOBALS['writes'][] = [$id, $tax, $ids];
    $GLOBALS['products'][$id]['terms'][$tax] = $ids;
    return $ids;
}
require __DIR__ . '/../inc/product-taxonomy-corrections.php';
function check($ok, $msg) {if (!$ok) throw new RuntimeException('FAIL: ' . $msg); echo "PASS: $msg\n";}
function rejects($cb, $msg) {
    try {$cb();} catch (RuntimeException $e) {echo "PASS: $msg (" . $e->getMessage() . ")\n";return;}
    throw new RuntimeException('FAIL (not rejected): ' . $msg);
}
function row($id, $sku, $expectedSeries, $series, $expectedCats='', $cats='') {
    return ['id' => (string) $id, 'sku' => $sku, 'reference_title' => 'not used',
        'expected_series' => $expectedSeries, 'series' => $series,
        'expected_categories' => $expectedCats, 'categories' => $cats];
}

$r = row(101, '10062', 'SPEK', 'LB Plast');
$original = $GLOBALS['products'][101];
check(spek_tc_has_changes(spek_tc_plan($r)), 'Preview detects pending series replacement');
check($GLOBALS['products'][101] === $original && !$GLOBALS['writes'], 'Preview is read-only');
check(spek_tc_apply_row($r, spek_tc_plan($r)), 'Series replacement applied');
check($GLOBALS['products'][101]['terms']['product_series'] === [2], 'Old series removed; only LB Plast remains');
check($GLOBALS['products'][101]['terms']['product_category'] === [10], 'Unmentioned category untouched');
check(!spek_tc_apply_row($r, spek_tc_plan($r)), 'Reimport is idempotent');
rejects(function () {spek_tc_plan(row(101, 'WRONG', 'SPEK', 'LB Plast'));}, 'Reject mismatching SKU');
rejects(function () {spek_tc_plan(row(999, '10062', 'SPEK', 'LB Plast'));}, 'Reject missing WP ID');
$GLOBALS['products'][103] = ['sku' => '10062', 'status' => 'draft', 'terms' => []];
rejects(function () use ($r) {spek_tc_plan($r);}, 'Reject duplicated SKU');
unset($GLOBALS['products'][103]);
rejects(function () {spek_tc_plan(row(101, '10062', 'Nemo', 'McAlpine'));}, 'Reject unexpected current series');
rejects(function () {spek_tc_plan(row(101, '10062', 'LB Plast', 'Unknown Brand'));}, 'Reject missing taxonomy term');

$category = row(102, '10064', '', '', 'Μπάνιο', 'Μπάνιο|Σιφόνια');
check(spek_tc_apply_row($category, spek_tc_plan($category)), 'Add Siphons while retaining Bathroom');
check($GLOBALS['products'][102]['terms']['product_category'] === [11, 12], 'Category set matches CSV exactly');
check($GLOBALS['products'][102]['terms']['product_series'] === [1], 'Blank series means no changes');

$both = row(102, '10064', 'SPEK', 'McAlpine', 'Μπάνιο|Σιφόνια', 'Διάφορα');
$GLOBALS['fail_taxonomy'] = 'product_category';
rejects(function () use ($both) {spek_tc_apply_row($both, spek_tc_plan($both));}, 'Rollback if second taxonomy write fails');
check($GLOBALS['products'][102]['terms']['product_series'] === [1] && $GLOBALS['products'][102]['terms']['product_category'] === [11,12], 'Rollback restores old series/categories');

// Replacement must remove ALL previous series, not just one.
$GLOBALS['products'][101]['terms']['product_series'] = [1, 4];
$multi = row(101, '10062', 'SPEK|Nemo', 'LB Plast');
check(spek_tc_apply_row($multi, spek_tc_plan($multi)), 'Replace multiple old series with one new series');
check($GLOBALS['products'][101]['terms']['product_series'] === [2], 'Neither SPEK nor Nemo remains assigned');

$csv = tempnam(sys_get_temp_dir(), 'spek-tc-test-');
$header = "id,sku,reference_title,expected_series,series,expected_categories,categories\n";
file_put_contents($csv, "\xEF\xBB\xBF".$header."101,10062,Title,LB Plast,McAlpine,,\n");
check(count(spek_tc_read_csv($csv)) === 1, 'CSV UTF-8 BOM accepted');
file_put_contents($csv, $header."101,10062,Title,LB Plast,McAlpine,,\n102,10062,Title,SPEK,LB Plast,,\n");
rejects(function () use ($csv) {spek_tc_read_csv($csv);}, 'Duplicate CSV SKU rejected');
unlink($csv);
echo "All SPEK taxonomy-correction regression tests passed.\n";
