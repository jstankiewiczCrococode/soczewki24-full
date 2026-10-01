<?php

require '/var/www/html/config/config.inc.php';

$csvPath = '/tmp/prestashop-products-final.csv';
$outputPath = '/tmp/prestashop-products-final-import-report.csv';

if (!file_exists($csvPath)) {
    fwrite(STDERR, "Brak pliku CSV: {$csvPath}\n");
    exit(1);
}

function normalizeHeader(string $value): string
{
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
    $value = trim($value);
    $value = trim($value, "\"'");

    return $value;
}

function normalizeDecimal(string $value): float
{
    $value = trim($value);

    if ($value === '') {
        return 0.0;
    }

    $value = str_replace(',', '.', $value);

    return (float) $value;
}

function detectVat(float $net, float $gross): ?int
{
    if ($net <= 0 || $gross <= 0) {
        return null;
    }

    $vat = (($gross / $net) - 1) * 100;

    if (abs($vat - 8.0) <= 0.20) {
        return 8;
    }

    if (abs($vat - 23.0) <= 0.20) {
        return 23;
    }

    return null;
}

function normalizeQuantity(float $quantity): array
{
    $source = (string) $quantity;

    /*
     * Wartości techniczne / specjalne ze źródła.
     */
    if ($quantity >= 999999) {
        return [
            'quantity' => 0,
            'status' => 'TECHNICAL_OR_UNLIMITED',
            'reason' => 'TECHNICAL_QUANTITY',
        ];
    }

    if ($quantity < 0) {
        return [
            'quantity' => 0,
            'status' => 'INVALID',
            'reason' => 'NEGATIVE_QUANTITY',
        ];
    }

    return [
        'quantity' => (int) round($quantity),
        'status' => 'OK',
        'reason' => '',
    ];
}
function decideImport(array $row): array
{
    $net = normalizeDecimal($row['price'] ?? '');
    $gross = normalizeDecimal($row['price_gross_source'] ?? '');
    $quantitySource = normalizeDecimal($row['quantity'] ?? '');

    $vat = detectVat($net, $gross);
    $quantity = normalizeQuantity($quantitySource);

    $reasons = [];
    $status = 'READY';

    /*
     * Cena 0 - zachowujemy produkt, ale wymagamy review.
     */
    if ($net <= 0) {
        $reasons[] = 'PRICE_ZERO';
        $status = 'REVIEW';
    }

    /*
     * Brak możliwości wyznaczenia VAT.
     *
     * Dla ceny 0 jest to normalne - nie próbujemy zgadywać.
     */
    if ($vat === null && $net > 0) {
        $reasons[] = 'VAT_UNDETERMINED';
        $status = 'REVIEW';
    }

    /*
     * Netto = brutto nie oznacza automatycznie VAT 0%.
     */
    if ($net > 0 && $gross > 0 && abs($net - $gross) < 0.01) {
        $reasons[] = 'NETTO_EQ_BRUTTO';
        $status = 'REVIEW';
    }

    /*
     * Techniczna / specjalna ilość.
     * Nie blokuje importu produktu - tylko wymaga normalizacji.
     */
    if ($quantity['status'] !== 'OK') {
        $reasons[] = 'TECHNICAL_QUANTITY';
    }

    /*
     * Grupa podatkowa tylko dla VAT, który został
     * wiarygodnie wykryty z relacji netto/brutto.
     */
    $taxGroup = '';

    if ($vat === 23) {
        $taxGroup = 1;
    } elseif ($vat === 8) {
        $taxGroup = 2;
    }

    return [
        'price_net' => number_format($net, 6, '.', ''),
        'price_gross_source' => number_format($gross, 2, '.', ''),
        'vat_detected' => $vat === null ? '' : $vat,
        'tax_group' => $taxGroup,
        'quantity_source' => $quantitySource,
        'quantity_import' => $quantity['quantity'],
        'quantity_status' => $quantity['status'],
        'import_status' => $status,
        'review_reason' => implode('|', array_unique($reasons)),
    ];
}

$handle = fopen($csvPath, 'r');

if (!$handle) {
    fwrite(STDERR, "Nie można otworzyć CSV.\n");
    exit(1);
}

$header = fgetcsv($handle, 0, ';');

if (!$header) {
    fwrite(STDERR, "CSV nie ma nagłówka.\n");
    exit(1);
}

$header = array_map('normalizeHeader', $header);

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
    if (!in_array($column, $header, true)) {
        fwrite(STDERR, "Brak kolumny CSV: {$column}\n");
        exit(1);
    }
}

$categories = [];

$sql = 'SELECT cl.id_category, cl.name
        FROM ' . _DB_PREFIX_ . 'category_lang cl
        WHERE cl.id_lang = ' . (int) Configuration::get('PS_LANG_DEFAULT') . '
          AND cl.id_shop = ' . (int) Context::getContext()->shop->id;

foreach (Db::getInstance()->executeS($sql) as $category) {
    $categories[(int) $category['id_category']] = $category['name'];
}

$categoryPathToId = [];

$categoryRows = Db::getInstance()->executeS(
    'SELECT c.id_category, cl.name, c.id_parent
     FROM ' . _DB_PREFIX_ . 'category c
     INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
        ON cl.id_category = c.id_category
       AND cl.id_lang = ' . (int) Configuration::get('PS_LANG_DEFAULT') . '
       AND cl.id_shop = ' . (int) Context::getContext()->shop->id
);

foreach ($categoryRows as $category) {
    $id = (int) $category['id_category'];

    $parts = [];
    $current = $id;
    $guard = 0;

    while ($current > 1 && $guard < 100) {
        $currentRow = null;

        foreach ($categoryRows as $candidate) {
            if ((int) $candidate['id_category'] === $current) {
                $currentRow = $candidate;
                break;
            }
        }

        if (!$currentRow) {
            break;
        }

        $parts[] = $currentRow['name'];
        $current = (int) $currentRow['id_parent'];
        $guard++;
    }

    if ($parts) {
        $parts = array_reverse($parts);

        /*
         * Usuwamy techniczny root "Strona główna".
         */
        if (($parts[0] ?? '') === 'Strona główna') {
            array_shift($parts);
        }

        if ($parts) {
            $categoryPathToId[implode(' / ', $parts)] = $id;
        }
    }
}

$out = fopen($outputPath, 'w');

if (!$out) {
    fwrite(STDERR, "Nie można utworzyć raportu.\n");
    exit(1);
}

$outputHeader = [
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
    'tax_group',
    'quantity_source',
    'quantity_import',
    'quantity_status',
    'import_status',
    'review_reason',
];

fputcsv($out, $outputHeader, ';');

$total = 0;
$ready = 0;
$review = 0;
$priceZero = 0;
$vatUndetermined = 0;
$netGrossEqual = 0;
$technicalQuantity = 0;
$categoryMissing = 0;

while (($data = fgetcsv($handle, 0, ';')) !== false) {
    if (count($data) < count($header)) {
        continue;
    }

    $row = array_combine($header, $data);

    if ($row === false) {
        continue;
    }

    $total++;

    $decision = decideImport($row);

    $categoryPath = trim($row['category_path'] ?? '');
    $categoryId = $categoryPathToId[$categoryPath] ?? null;

    if (!$categoryId) {
        $categoryId = 10; // Do przypisania
        $categoryMissing++;
        $decision['import_status'] = 'REVIEW';

        $reason = trim($decision['review_reason']);

        $decision['review_reason'] = $reason !== ''
            ? $reason . '|CATEGORY_MISSING'
            : 'CATEGORY_MISSING';
    }

    if ($decision['import_status'] === 'READY') {
        $ready++;
    } else {
        $review++;
    }

    if (str_contains($decision['review_reason'], 'PRICE_ZERO')) {
        $priceZero++;
    }

    if (str_contains($decision['review_reason'], 'VAT_UNDETERMINED')) {
        $vatUndetermined++;
    }

    if (str_contains($decision['review_reason'], 'NETTO_EQ_BRUTTO')) {
        $netGrossEqual++;
    }

    if ($decision['quantity_status'] !== 'OK') {
        $technicalQuantity++;
    }

    fputcsv($out, [
        $row['id'],
        $row['active'],
        $row['name'],
        $row['reference'],
        $row['manufacturer'],
        $categoryPath,
        $categoryId,
        $decision['price_net'],
        $decision['price_gross_source'],
        $decision['vat_detected'],
        $decision['tax_group'],
        $decision['quantity_source'],
        $decision['quantity_import'],
        $decision['quantity_status'],
        $decision['import_status'],
        $decision['review_reason'],
    ], ';');
}

fclose($handle);
fclose($out);

echo "=== SOCZEWKI24 - FINALNY RAPORT IMPORTU ===\n";
echo "TRYB: READ ONLY\n\n";

echo "Wszystkich produktów: {$total}\n";
echo "READY: {$ready}\n";
echo "REVIEW: {$review}\n\n";

echo "Cena 0: {$priceZero}\n";
echo "VAT nieustalony: {$vatUndetermined}\n";
echo "Netto = brutto: {$netGrossEqual}\n";
echo "Ilość techniczna/specjalna: {$technicalQuantity}\n";
echo "Brak mapowania kategorii: {$categoryMissing}\n\n";

echo "RAPORT:\n";
echo "{$outputPath}\n\n";

echo "=== KONIEC ===\n";
echo "NIC NIE ZOSTAŁO ZAPISANE DO BAZY.\n";