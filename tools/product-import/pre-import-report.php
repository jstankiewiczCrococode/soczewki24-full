<?php

require '/var/www/html/config/config.inc.php';

const CSV_FILE = '/tmp/prestashop-products-final.csv';
const REPORT_FILE = '/tmp/prestashop-products-pre-import-report.csv';
const LANG_ID = 1;
const SHOP_ID = 1;

$db = Db::getInstance();

echo "=== SOCZEWKI24 - PEŁNY RAPORT PRZED IMPORTEM ===\n";
echo "TRYB: READ ONLY\n\n";

/*
 * Kategorie PrestaShop
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
        if ($currentId !== 1 && $currentId !== 2) {
            array_unshift($parts, $categoryData[$currentId]['name']);
        }

        $currentId = $categoryData[$currentId]['parent'];
        $guard++;
    }

    return $categoryPathCache[$categoryId] = implode(' / ', $parts);
}

$categoryMap = [];

foreach (array_keys($categoryData) as $categoryId) {
    $path = getCategoryPath($categoryId);

    if ($path !== '') {
        $categoryMap[$path] = $categoryId;
    }
}

echo "Kategorie dostępne w PrestaShop: " . count($categoryMap) . "\n";

/*
 * CSV
 */
$handle = fopen(CSV_FILE, 'rb');

if ($handle === false) {
    throw new RuntimeException('Nie można otworzyć CSV: ' . CSV_FILE);
}

$header = fgetcsv($handle, 0, ';', '"');

if ($header === false) {
    throw new RuntimeException('CSV jest pusty.');
}

/*
 * Usunięcie BOM + normalizacja nagłówków
 */
foreach ($header as $i => $value) {
    $value = (string) $value;
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
    $value = trim($value);
    $value = trim($value, "\"'");
    $header[$i] = $value;
}

$headerMap = [];

foreach ($header as $i => $value) {
    $headerMap[$value] = $i;
}

$requiredColumns = [
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

foreach ($requiredColumns as $column) {
    if (!array_key_exists($column, $headerMap)) {
        throw new RuntimeException(
            'Brak kolumny CSV: ' . $column .
            '. Odczytane nagłówki: ' . implode(', ', $header)
        );
    }
}

function csvValue(array $row, string $column): string
{
    global $headerMap;

    $index = $headerMap[$column];

    return trim((string) ($row[$index] ?? ''));
}

function moneyValue(string $value): float
{
    $value = trim($value);

    if ($value === '') {
        return 0.0;
    }

    $value = str_replace(' ', '', $value);
    $value = str_replace(',', '.', $value);

    return (float) $value;
}

function detectVat(float $net, float $gross): ?int
{
    if ($net <= 0 || $gross <= 0) {
        return null;
    }

    $vat = (($gross / $net) - 1) * 100;

    if (abs($vat - 23) <= 0.20) {
        return 23;
    }

    if (abs($vat - 8) <= 0.20) {
        return 8;
    }

    if (abs($vat) <= 0.20) {
        return 0;
    }

    return null;
}

function classifyQuantity(int $quantity): string
{
    if ($quantity >= 1000000) {
        return 'SPECIAL_SOURCE_VALUE';
    }

    if ($quantity >= 999999) {
        return 'UNLIMITED_OR_SPECIAL';
    }

    if ($quantity < 0) {
        return 'NEGATIVE';
    }

    return 'OK';
}

/*
 * Raport CSV
 */
$report = fopen(REPORT_FILE, 'wb');

if ($report === false) {
    throw new RuntimeException(
        'Nie można utworzyć raportu: ' . REPORT_FILE
    );
}

$reportHeader = [
    'source_id',
    'active',
    'name',
    'reference_source',
    'manufacturer_source',
    'category_path',
    'category_id',
    'price_net',
    'price_gross_source',
    'vat_detected',
    'tax_group_proposed',
    'quantity_source',
    'quantity_status',
    'price_status',
    'import_status',
    'review_reason',
];

fputcsv($report, $reportHeader, ';', '"');

$total = 0;
$ready = 0;
$review = 0;

$stats = [
    'VAT_23' => 0,
    'VAT_8' => 0,
    'VAT_0_REVIEW' => 0,
    'VAT_UNKNOWN_REVIEW' => 0,
    'ZERO_PRICE' => 0,
    'SPECIAL_QUANTITY' => 0,
    'CATEGORY_MISSING' => 0,
];

while (($row = fgetcsv($handle, 0, ';', '"')) !== false) {
    if (count($row) < count($header)) {
        continue;
    }

    $total++;

    $sourceId = csvValue($row, 'id');
    $active = csvValue($row, 'active');
    $name = csvValue($row, 'name');
    $reference = csvValue($row, 'reference');
    $manufacturer = csvValue($row, 'manufacturer');

    $categoryPath = csvValue($row, 'category_path');

    if ($categoryPath === '') {
        $categoryPath = 'Do przypisania';
    }

    $categoryId = $categoryMap[$categoryPath] ?? null;

    $net = moneyValue(csvValue($row, 'price'));
    $gross = moneyValue(csvValue($row, 'price_gross_source'));
    $quantity = (int) csvValue($row, 'quantity');

    $vat = detectVat($net, $gross);

    $taxGroup = '';
    $priceStatus = 'OK';
    $quantityStatus = classifyQuantity($quantity);

    $issues = [];

    /*
     * VAT
     */
    if ($vat === 23) {
        $stats['VAT_23']++;
        $taxGroup = '1';
    } elseif ($vat === 8) {
        $stats['VAT_8']++;
        $taxGroup = '2';
    } elseif ($vat === 0) {
        $stats['VAT_0_REVIEW']++;
        $issues[] = 'NETTO_EQ_BRUTTO';
        $priceStatus = 'PROMO_OR_SPECIAL_PRICE';
    } else {
        $stats['VAT_UNKNOWN_REVIEW']++;
        $issues[] = 'VAT_UNDETERMINED';
    }

    /*
     * Cena
     */
    if ($net <= 0) {
        $stats['ZERO_PRICE']++;
        $issues[] = 'PRICE_ZERO';
        $priceStatus = 'ZERO_PRICE';
    }

    /*
     * Ilość
     */
    if ($quantity >= 999999) {
        $stats['SPECIAL_QUANTITY']++;
        $issues[] = 'SPECIAL_QUANTITY';
    }

    /*
     * Kategoria
     */
    if ($categoryId === null) {
        $stats['CATEGORY_MISSING']++;
        $issues[] = 'CATEGORY_MISSING';
    }

    /*
     * Status końcowy
     */
    if ($issues === []) {
        $importStatus = 'READY';
        $ready++;
    } else {
        $importStatus = 'REVIEW';
        $review++;
    }

    fputcsv($report, [
        $sourceId,
        $active,
        $name,
        $reference,
        $manufacturer,
        $categoryPath,
        $categoryId ?? '',
        number_format($net, 2, '.', ''),
        number_format($gross, 2, '.', ''),
        $vat ?? '',
        $taxGroup,
        $quantity,
        $quantityStatus,
        $priceStatus,
        $importStatus,
        implode('|', $issues),
    ], ';', '"');
}

fclose($handle);
fclose($report);

echo "\n=== PODSUMOWANIE ===\n";
echo "Wszystkich produktów: {$total}\n";
echo "READY: {$ready}\n";
echo "REVIEW: {$review}\n\n";

echo "VAT 23%: {$stats['VAT_23']}\n";
echo "VAT 8%: {$stats['VAT_8']}\n";
echo "Netto = brutto - REVIEW: {$stats['VAT_0_REVIEW']}\n";
echo "VAT nieustalony - REVIEW: {$stats['VAT_UNKNOWN_REVIEW']}\n";
echo "Cena 0 - REVIEW: {$stats['ZERO_PRICE']}\n";
echo "Ilość specjalna - REVIEW: {$stats['SPECIAL_QUANTITY']}\n";
echo "Brak kategorii - REVIEW: {$stats['CATEGORY_MISSING']}\n";

echo "\n=== RAPORT ===\n";
echo REPORT_FILE . "\n";
echo "=== KONIEC ===\n";
echo "NIC NIE ZOSTAŁO ZAPISANE DO BAZY.\n";
