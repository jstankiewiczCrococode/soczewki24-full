<?php

require '/var/www/html/config/config.inc.php';

$csvPath = '/tmp/prestashop-products-final-import-report.csv';

if (!file_exists($csvPath)) {
    fwrite(
        STDERR,
        "Brak finalnego raportu. Najpierw uruchom final-import-report.php.\n"
    );
    exit(1);
}

function normalizeHeader(string $value): string
{
    return trim(preg_replace('/^\xEF\xBB\xBF/', '', $value));
}

$handle = fopen($csvPath, 'r');

if (!$handle) {
    fwrite(STDERR, "Nie można otworzyć raportu.\n");
    exit(1);
}

$header = fgetcsv($handle, 0, ';');
$header = array_map('normalizeHeader', $header);

$required = [
    'source_id',
    'active',
    'name',
    'category_id',
    'price_net',
    'tax_group',
    'quantity_import',
    'import_status',
    'review_reason',
];

foreach ($required as $column) {
    if (!in_array($column, $header, true)) {
        fwrite(STDERR, "Brak kolumny: {$column}\n");
        exit(1);
    }
}

$total = 0;
$ready = 0;
$review = 0;

$validCategories = [];
$taxGroups = [];

foreach (
    Db::getInstance()->executeS(
        'SELECT id_category FROM ' . _DB_PREFIX_ . 'category'
    ) as $row
) {
    $validCategories[(int) $row['id_category']] = true;
}

foreach (
    Db::getInstance()->executeS(
        'SELECT id_tax_rules_group FROM ' . _DB_PREFIX_ . 'tax_rules_group'
    ) as $row
) {
    $taxGroups[(int) $row['id_tax_rules_group']] = true;
}

echo "=== SOCZEWKI24 - DRY RUN IMPORTU PRODUKTÓW ===\n";
echo "TRYB: SYMULACJA - BRAK ZAPISU DO BAZY\n\n";

$errors = [];
$samples = [];
$reviewSamples = [];

while (($data = fgetcsv($handle, 0, ';')) !== false) {
    if (count($data) < count($header)) {
        continue;
    }

    $row = array_combine($header, $data);

    if ($row === false) {
        continue;
    }

    $total++;

    $sourceId = (int) $row['source_id'];
    $name = trim($row['name']);
    $categoryId = (int) $row['category_id'];
    $taxGroup = (int) $row['tax_group'];
    $price = (float) $row['price_net'];
    $quantity = (int) $row['quantity_import'];
    $status = trim($row['import_status']);

    if ($name === '') {
        $errors[] = "{$sourceId}: brak nazwy";
    }

    if ($categoryId <= 0 || !isset($validCategories[$categoryId])) {
        $errors[] = "{$sourceId}: nieprawidłowa kategoria {$categoryId}";
    }

    if ($price < 0) {
        $errors[] = "{$sourceId}: cena < 0";
    }

    if ($quantity < 0) {
        $errors[] = "{$sourceId}: ilość < 0";
    }

    if ($taxGroup > 0 && !isset($taxGroups[$taxGroup])) {
        $errors[] = "{$sourceId}: nieistniejąca grupa podatkowa {$taxGroup}";
    }

    /*
     * Symulujemy dane przekazywane później do Product.
     */
    $simulatedProduct = [
        'active' => (int) $row['active'],
        'name' => $name,
        'price' => $price,
        'id_category_default' => $categoryId,
        'id_tax_rules_group' => $taxGroup,
        'quantity' => $quantity,
    ];

    if ($status === 'READY') {
        $ready++;

        if (count($samples) < 10) {
            $samples[] = [
                'source_id' => $sourceId,
                'name' => $name,
                'category_id' => $categoryId,
                'price' => $price,
                'tax_group' => $taxGroup,
                'quantity' => $quantity,
            ];
        }
    } else {
        $review++;

        if (count($reviewSamples) < 20) {
            $reviewSamples[] = [
                'source_id' => $sourceId,
                'name' => $name,
                'reason' => $row['review_reason'],
                'price' => $price,
                'quantity' => $quantity,
            ];
        }
    }
}

fclose($handle);

echo "=== PODSUMOWANIE ===\n";
echo "Produktów: {$total}\n";
echo "READY: {$ready}\n";
echo "REVIEW: {$review}\n";
echo "Błędy techniczne: " . count($errors) . "\n\n";

if ($errors) {
    echo "=== BŁĘDY ===\n";

    foreach (array_slice($errors, 0, 50) as $error) {
        echo "- {$error}\n";
    }

    if (count($errors) > 50) {
        echo "... oraz " . (count($errors) - 50) . " kolejnych.\n";
    }

    echo "\n";
}

echo "=== PRZYKŁADOWE PRODUKTY READY ===\n";

foreach ($samples as $sample) {
    echo sprintf(
        "[%d] %s | cat=%d | price=%.2f | tax=%d | qty=%d\n",
        $sample['source_id'],
        $sample['name'],
        $sample['category_id'],
        $sample['price'],
        $sample['tax_group'],
        $sample['quantity']
    );
}

echo "\n";

echo "=== PRZYKŁADOWE PRODUKTY REVIEW ===\n";

foreach ($reviewSamples as $sample) {
    echo sprintf(
        "[%d] %s | %s | price=%.2f | qty=%d\n",
        $sample['source_id'],
        $sample['name'],
        $sample['reason'],
        $sample['price'],
        $sample['quantity']
    );
}

echo "\n";

if (count($errors) === 0) {
    echo "STATUS: OK\n";
    echo "Dry-run nie wykrył błędów technicznych.\n";
} else {
    echo "STATUS: ERROR\n";
    echo "Najpierw należy usunąć błędy techniczne.\n";
}

echo "\n=== KONIEC ===\n";
echo "NIC NIE ZOSTAŁO ZAPISANE DO BAZY.\n";