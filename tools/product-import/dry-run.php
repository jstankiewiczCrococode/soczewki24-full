<?php

require '/var/www/html/config/config.inc.php';

const CSV_FILE = '/tmp/prestashop-products-final.csv';
const LANG_ID = 1;
const SHOP_ID = 1;

$db = Db::getInstance();

echo "=== SOCZEWKI24 - PRODUCT IMPORT DRY-RUN ===\n";
echo "TRYB: READ ONLY - BRAK ZAPISU DO BAZY\n\n";

/*
 * ---------------------------------------------------------
 * KATEGORIE
 * ---------------------------------------------------------
 */

$categories = $db->executeS('
    SELECT
        c.id_category,
        c.id_parent,
        cl.name
    FROM ' . _DB_PREFIX_ . 'category c
    INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
        ON cl.id_category = c.id_category
        AND cl.id_lang = ' . (int) LANG_ID . '
        AND cl.id_shop = ' . (int) SHOP_ID . '
    ORDER BY c.id_category
');

$categoryData = [];

foreach ($categories as $category) {
    $categoryData[(int) $category['id_category']] = [
        'parent' => (int) $category['id_parent'],
        'name' => $category['name'],
    ];
}

$categoryPathCache = [];

function getCategoryPath(int $categoryId): string
{
    global $categoryData, $categoryPathCache;

    if (isset($categoryPathCache[$categoryId])) {
        return $categoryPathCache[$categoryId];
    }

    $parts = [];
    $currentId = $categoryId;
    $guard = 0;

    while ($currentId > 0 && isset($categoryData[$currentId]) && $guard < 100) {
        $category = $categoryData[$currentId];

        if ($currentId !== 1 && $currentId !== 2) {
            array_unshift($parts, $category['name']);
        }

        $currentId = $category['parent'];
        $guard++;
    }

    return $categoryPathCache[$categoryId] = implode(' / ', $parts);
}

$categoryMap = [];

foreach (array_keys($categoryData) as $categoryId) {
    $path = getCategoryPath($categoryId);

    if ($path === '') {
        continue;
    }

    if (isset($categoryMap[$path])) {
        echo "[WARN] Duplikat ścieżki kategorii: {$path}\n";
    }

    $categoryMap[$path] = $categoryId;
}

echo "Kategorie w PrestaShop: " . count($categoryMap) . "\n\n";

/*
 * ---------------------------------------------------------
 * CSV
 * ---------------------------------------------------------
 */

$handle = fopen(CSV_FILE, 'rb');

if ($handle === false) {
    throw new RuntimeException('Nie można otworzyć pliku CSV: ' . CSV_FILE);
}

$header = fgetcsv($handle, 0, ';', '"');

if ($header === false) {
    throw new RuntimeException('CSV jest pusty.');
}

/*
 * UTF-8 BOM.
 */
if (isset($header[0])) {
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
}

/*
 * Normalizacja nagłówków.
 */
foreach ($header as $index => $name) {
    $name = trim($name);
    $name = trim($name, "\"'");
    $name = preg_replace('/^\xEF\xBB\xBF/', '', $name);
    $header[$index] = $name;
}

echo "Nagłówki CSV:\n";
echo "  " . implode(' | ', $header) . "\n\n";

$headerMap = [];

foreach ($header as $index => $name) {
    $headerMap[$name] = $index;
}

$required = [
    'id',
    'active',
    'name',
    'reference',
    'manufacturer',
    'category',
    'category_path',
    'price',
    'price_gross_source',
    'quantity',
    'description',
];

foreach ($required as $column) {
    if (!array_key_exists($column, $headerMap)) {
        throw new RuntimeException(
            "Brak kolumny CSV: {$column}. Odczytane nagłówki: " .
            implode(', ', array_keys($headerMap))
        );
    }
}

function csvValue(array $row, string $column): string
{
    global $headerMap;

    return trim((string) ($row[$headerMap[$column]] ?? ''));
}

function detectVatGroup(float $net, float $gross): array
{
    if ($net <= 0) {
        return [
            'rate' => null,
            'group_id' => null,
            'label' => 'BRAK CENY',
        ];
    }

    $vat = (($gross / $net) - 1) * 100;

    if ($vat < 1) {
        return [
            'rate' => 0.0,
            'group_id' => 4,
            'label' => '0%',
        ];
    }

    if ($vat < 15) {
        return [
            'rate' => 8.0,
            'group_id' => 2,
            'label' => '8%',
        ];
    }

    return [
        'rate' => 23.0,
        'group_id' => 1,
        'label' => '23%',
    ];
}

/*
 * ---------------------------------------------------------
 * ANALIZA PRODUKTÓW
 * ---------------------------------------------------------
 */

$total = 0;
$categoryMissing = [];
$categoryFound = [];

$vatCounts = [];

$activeCounts = [
    'active' => 0,
    'inactive' => 0,
];

$sampleByVat = [
    '8%' => [],
    '23%' => [],
    '0%' => [],
    'BRAK CENY' => [],
];

while (($row = fgetcsv($handle, 0, ';', '"')) !== false) {
    if (count($row) < count($header)) {
        echo "[WARN] Pominięto uszkodzony rekord CSV przy produkcie #" .
            ($row[$headerMap['id']] ?? '?') . "\n";
        continue;
    }

    $total++;

    $sourceId = csvValue($row, 'id');
    $name = csvValue($row, 'name');
    $active = csvValue($row, 'active');
    $categoryPath = csvValue($row, 'category_path');

    $net = (float) str_replace(',', '.', csvValue($row, 'price'));
    $gross = (float) str_replace(',', '.', csvValue($row, 'price_gross_source'));
    $quantity = (int) csvValue($row, 'quantity');

    if ($active === '1') {
        $activeCounts['active']++;
    } else {
        $activeCounts['inactive']++;
    }

    if ($categoryPath === '') {
        $categoryPath = 'Do przypisania';
    }

    if (isset($categoryMap[$categoryPath])) {
        $categoryFound[$categoryPath] =
            ($categoryFound[$categoryPath] ?? 0) + 1;

        $categoryId = $categoryMap[$categoryPath];
    } else {
        $categoryMissing[$categoryPath] =
            ($categoryMissing[$categoryPath] ?? 0) + 1;

        $categoryId = null;
    }

    $vat = detectVatGroup($net, $gross);

    $vatCounts[$vat['label']] =
        ($vatCounts[$vat['label']] ?? 0) + 1;

    if (count($sampleByVat[$vat['label']]) < 7) {
        $sampleByVat[$vat['label']][] = [
            'source_id' => $sourceId,
            'name' => $name,
            'category_path' => $categoryPath,
            'category_id' => $categoryId,
            'net' => $net,
            'gross' => $gross,
            'vat' => $vat['label'],
            'tax_group' => $vat['group_id'],
            'quantity' => $quantity,
            'active' => $active === '1' ? 'TAK' : 'NIE',
        ];
    }
}

fclose($handle);

/*
 * ---------------------------------------------------------
 * WYNIKI
 * ---------------------------------------------------------
 */

echo "=== PODSUMOWANIE CSV ===\n";
echo "Produktów: {$total}\n";
echo "Aktywnych: {$activeCounts['active']}\n";
echo "Nieaktywnych: {$activeCounts['inactive']}\n\n";

echo "=== VAT ===\n";

foreach ($vatCounts as $label => $count) {
    echo str_pad($label, 12) . $count . "\n";
}

echo "\n=== MAPOWANIE KATEGORII ===\n";
echo "Znalezionych ścieżek użytych przez produkty: " .
    count($categoryFound) . "\n";

echo "Brakujących ścieżek: " .
    count($categoryMissing) . "\n";

if ($categoryMissing) {
    echo "\nBRAKUJĄCE KATEGORIE:\n";

    foreach ($categoryMissing as $path => $count) {
        echo "  {$count} x {$path}\n";
    }
}

echo "\n=== PRÓBKA PRODUKTÓW ===\n";

$sample = [];

foreach (['8%', '23%', '0%', 'BRAK CENY'] as $vatLabel) {
    foreach ($sampleByVat[$vatLabel] as $product) {
        $sample[] = $product;
    }
}

foreach ($sample as $product) {
    echo "\n";
    echo "ID źródłowe : {$product['source_id']}\n";
    echo "Nazwa       : {$product['name']}\n";
    echo "Kategoria   : {$product['category_path']}\n";
    echo "ID kategorii: " .
        ($product['category_id'] ?? 'BRAK') . "\n";
    echo "Netto       : {$product['net']}\n";
    echo "Brutto      : {$product['gross']}\n";
    echo "VAT         : {$product['vat']}\n";
    echo "Tax group   : " .
        ($product['tax_group'] ?? 'BRAK') . "\n";
    echo "Ilość       : {$product['quantity']}\n";
    echo "Aktywny     : {$product['active']}\n";
    echo "Reference   : [PUSTE]\n";
}

echo "\n=== WYNIK DRY-RUN ===\n";

if ($categoryMissing) {
    echo "STATUS: STOP - są brakujące ścieżki kategorii.\n";
    exit(2);
}

echo "STATUS: OK - wszystkie ścieżki kategorii produktów mają mapowanie.\n";
echo "UWAGA: nic nie zostało zapisane do bazy.\n";
