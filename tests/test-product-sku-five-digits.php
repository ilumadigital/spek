<?php
/** Run with: php tests/test-product-sku-five-digits.php */
define('ABSPATH', __DIR__ . '/');
$GLOBALS['products'] = [
    101 => ['status' => 'publish', 'meta' => ['product_code' => ['11']]],
    102 => ['status' => 'draft', 'meta' => ['product_code' => ['455']]],
    103 => ['status' => 'publish', 'meta' => ['product_code' => ['1455']]],
    27 => ['status' => 'publish', 'meta' => []],
    29 => ['status' => 'publish', 'meta' => []],
];
$GLOBALS['writes'] = [];
class FakeDB {
    public string $posts = 'wp_posts';
    public string $postmeta = 'wp_postmeta';
    public function prepare($sql, ...$params): array { return $params; }
    public function get_col($params): array {
        [$post_type, $sku, $not_id] = $params;
        $hits = [];
        foreach ($GLOBALS['products'] as $id => $post) {
            if ((int) $not_id === (int) $id) continue;
            foreach (['product_code', '_product_code', 'sku', '_sku', 'product_sku'] as $key) {
                foreach (($post['meta'][$key] ?? []) as $value) {
                    if ($value === $sku) { $hits[(int) $id] = (int) $id; }
                }
            }
        }
        return array_values($hits);
    }
}
$GLOBALS['wpdb'] = new FakeDB();
function add_action(...$args): void {}
function get_post_type(int $id) { return isset($GLOBALS['products'][$id]) ? 'spek_product' : null; }
function get_post_status(int $id) { return $GLOBALS['products'][$id]['status'] ?? null; }
function get_post_meta(int $id, string $key, bool $single) {
    $values = $GLOBALS['products'][$id]['meta'][$key] ?? [];
    return $single ? ($values[0] ?? '') : $values;
}
function update_post_meta(int $id, string $key, $value) {
    $GLOBALS['writes'][] = [$id, $key, $value];
    $GLOBALS['products'][$id]['meta'][$key] = [(string) $value];
    return true;
}
require __DIR__ . '/../inc/product-sku-five-digits.php';
function check($pass, string $what): void {
    if (!$pass) throw new RuntimeException('FAIL: ' . $what);
    echo 'PASS: ' . $what . "\n";
}
function reject(callable $fn, string $what): void {
    try { $fn(); } catch (RuntimeException $e) { echo 'PASS: ' . $what . ': ' . $e->getMessage() . "\n"; return; }
    throw new RuntimeException('FAIL: expected rejection: ' . $what);
}
function row(int $id, string $old, string $new): array {
    return ['id' => (string) $id, 'old_sku' => $old, 'new_sku' => $new, 'reference_title' => 'Reference'];
}
$csv = tempnam(sys_get_temp_dir(), 'sku5');
$header = "id,old_sku,new_sku,reference_title\n";
file_put_contents($csv, "\xEF\xBB\xBF" . $header . "101,11,00011,Example\n102,455,00455,Example\n103,1455,01455,Example\n");
$rows = spek_sku5_read_csv($csv);
check(count($rows) === 3 && $rows[0]['new_sku'] === '00011', 'UTF-8 BOM and five-digit SKU strings read intact');
check(spek_sku5_check($rows[0]) === 'pending', 'Preview flags pending SKU');
check(!$GLOBALS['writes'], 'Preview causes no product database writes');
check(spek_sku5_check($rows[2]) === 'pending', 'Four-digit code preview');
check(update_post_meta(101, 'product_code', $rows[0]['new_sku']), 'Write five-digit SKU');
check($GLOBALS['products'][101]['meta']['product_code'] === ['00011'], 'Leading zeros remain as literal string');
check(spek_sku5_check($rows[0]) === 'applied', 'Reimport recognizes already applied correction');
reject(fn() => spek_sku5_check(row(102, '454', '00454')), 'Mismatch rejects different code');
$GLOBALS['products'][104] = ['status' => 'trash', 'meta' => ['product_code' => ['00455']]];
reject(fn() => spek_sku5_check($rows[1]), 'New SKU collision in trashed product blocks update');
unset($GLOBALS['products'][104]);
$GLOBALS['products'][104] = ['status' => 'draft', 'meta' => ['_sku' => ['01455']]];
reject(fn() => spek_sku5_check($rows[2]), 'Legacy SKU alias collision blocks update');
unset($GLOBALS['products'][104]);
$GLOBALS['products'][102]['meta']['product_code'][] = '455';
reject(fn() => spek_sku5_check($rows[1]), 'Duplicate product_code rows for same product block update');
$GLOBALS['products'][102]['meta']['product_code'] = ['455'];
reject(fn() => spek_sku5_check(row(999, '11', '00011')), 'Missing WP ID rejected');
$GLOBALS['products'][102]['status'] = 'trash';
reject(fn() => spek_sku5_check($rows[1]), 'Trashed target rejected');
$GLOBALS['products'][102]['status'] = 'draft';
file_put_contents($csv, $header . "101,11,00011,A\n101,12,00012,B\n");
reject(fn() => spek_sku5_read_csv($csv), 'Duplicate CSV WP IDs rejected');
file_put_contents($csv, $header . "101,11,00011,A\n102,11,00011,B\n");
reject(fn() => spek_sku5_read_csv($csv), 'Duplicate old/new SKU rejected');
file_put_contents($csv, $header . "101,11,00012,A\n");
reject(fn() => spek_sku5_read_csv($csv), 'Incorrect left-padding rejected');
file_put_contents($csv, $header . "27,0,00000,No SKU\n");
reject(fn() => spek_sku5_read_csv($csv), 'SKU-less ID 27/29 protected');
unlink($csv);
print "All SKU five-digit correction tests passed.\n";
