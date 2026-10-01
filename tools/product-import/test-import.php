<?php

require '/var/www/html/config/config.inc.php';

use PrestaShop\PrestaShop\Adapter\Entity\Category;
use PrestaShop\PrestaShop\Adapter\Entity\Product;
use PrestaShop\PrestaShop\Adapter\Entity\StockAvailable;

$csv = '/tmp/prestashop-products-final-import-report.csv';

$testIds = [37, 38, 40, 41, 42, 43, 44, 45, 46, 47];

$idLang = (int) Configuration::get('PS_LANG_DEFAULT');
$idShop = (int) Context::getContext()->shop->id;

if (!$idLang) {
    $idLang = 1;
}

if (!$idShop) {
    $idShop = 1;
}

function normalizeHeader(string $value): string
{
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
    $value = trim($value);

    return trim($value, "\"'");
}

function makeLinkRewrite(string $name): string
{
    $rewrite = Tools::str2url($name);

    if ($rewrite === '') {
        $rewrite = 'produkt';
    }

    return $rewrite;
}

function findProductMap(int $sourceId, int $idShop): ?array
{
    $sql = 'SELECT `id_map`, `id_product`
            FROM `' . _DB_PREFIX_ . 'croco_soczewki_product_map`
            WHERE `source_id` = ' . $sourceId . '
              AND `id_shop` = ' . $idShop;

    $row = Db::getInstance()->getRow($sql);

    if (!$row) {
        return null;
    }

    return [
        'id_map' => (int) $row['id_map'],
        'id_product' => (int) $row['id_product'],
    ];
}

function saveProductMap(int $sourceId, int $productId, int $idShop): void
{
    $now = date('Y-m-d H:i:s');

    $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'croco_soczewki_product_map`
            (`source_id`, `id_product`, `id_shop`, `date_add`, `date_upd`)
            VALUES (
                ' . $sourceId . ',
                ' . $productId . ',
                ' . $idShop . ',
                "' . pSQL($now) . '",
                "' . pSQL($now) . '"
            )';

    if (!Db::getInstance()->execute($sql)) {
        throw new RuntimeException(
            "Nie udało się zapisać mapowania source_id={$sourceId}, id_product={$productId}"
        );
    }
}

$fh = fopen($csv, 'r');

if (!$fh) {
    fwrite(STDERR, "Nie można otworzyć CSV: {$csv}\n");
    exit(1);
}

$headers = fgetcsv($fh, 0, ';');

if (!$headers) {
    fwrite(STDERR, "Brak nagłówka CSV.\n");
    exit(1);
}

$headers = array_map('normalizeHeader', $headers);

$rows = [];

while (($data = fgetcsv($fh, 0, ';')) !== false) {
    if (count($data) !== count($headers)) {
        continue;
    }

    $row = array_combine($headers, $data);

    if (!$row) {
        continue;
    }

    if (!in_array((int) $row['source_id'], $testIds, true)) {
        continue;
    }

    if (($row['import_status'] ?? '') !== 'READY') {
        continue;
    }

    $rows[] = $row;
}

fclose($fh);

echo "=== TESTOWY IMPORT PRODUKTÓW ===\n";
echo "Produktów do importu: " . count($rows) . "\n";
echo "Język: {$idLang}\n";
echo "Sklep: {$idShop}\n\n";

if (count($rows) !== count($testIds)) {
    echo "UWAGA: oczekiwano " . count($testIds) . " produktów READY.\n";
    echo "Znaleziono: " . count($rows) . "\n";
    exit(1);
}

$created = 0;
$skipped = 0;

foreach ($rows as $row) {
    $sourceId = (int) $row['source_id'];
    $name = trim($row['name']);
    $categoryId = (int) $row['category_id'];
    $taxGroupId = (int) $row['tax_group'];
    $price = (float) $row['price_net'];
    $quantity = (int) $row['quantity_import'];
    $active = (int) $row['active'];

    echo "[{$sourceId}] {$name}\n";

    $existingMap = findProductMap($sourceId, $idShop);

    if ($existingMap !== null) {
        $existingProductId = $existingMap['id_product'];

        if (Validate::isLoadedObject(new Product($existingProductId))) {
            echo "  -> POMINIĘTO: mapowanie już istnieje\n";
            echo "  -> PS product ID: {$existingProductId}\n\n";
            $skipped++;
            continue;
        }

        throw new RuntimeException(
            "Istnieje mapowanie source_id={$sourceId}, ale produkt {$existingProductId} nie istnieje."
        );
    }

    if ($categoryId <= 0) {
        throw new RuntimeException("Brak category_id dla source_id={$sourceId}");
    }

    if (!Category::existsInDatabase($categoryId, 'Category')) {
        throw new RuntimeException(
            "Kategoria {$categoryId} nie istnieje dla source_id={$sourceId}"
        );
    }

    if (!in_array($taxGroupId, [1, 2], true)) {
        throw new RuntimeException(
            "Nieprawidłowy tax_group={$taxGroupId} dla source_id={$sourceId}"
        );
    }

    Db::getInstance()->execute('START TRANSACTION');

    try {
        $product = new Product();

        $product->name = [
            $idLang => $name,
        ];

        $product->link_rewrite = [
            $idLang => makeLinkRewrite($name),
        ];

        $product->description = [
            $idLang => $row['description'] ?? '',
        ];

        $product->description_short = [
            $idLang => '',
        ];

        $product->price = $price;
        $product->active = $active;
        $product->id_tax_rules_group = $taxGroupId;
        $product->visibility = 'both';
        $product->available_for_order = 1;
        $product->show_price = 1;
        $product->condition = 'new';
        $product->indexed = 1;
        $product->id_category_default = $categoryId;

        if (!$product->add()) {
            throw new RuntimeException(
                "Nie udało się utworzyć produktu source_id={$sourceId}"
            );
        }

        $productId = (int) $product->id;

        if ($productId <= 0) {
            throw new RuntimeException(
                "Produkt został utworzony bez prawidłowego ID source_id={$sourceId}"
            );
        }

        if (!$product->updateCategories([$categoryId])) {
            throw new RuntimeException(
                "Nie udało się przypisać kategorii dla source_id={$sourceId}"
            );
        }

        StockAvailable::setQuantity(
            $productId,
            0,
            $quantity,
            $idShop
        );

        saveProductMap($sourceId, $productId, $idShop);

        Db::getInstance()->execute('COMMIT');

        $created++;

        echo "  -> PS product ID: {$productId}\n";
        echo "  -> category: {$categoryId}\n";
        echo "  -> price: " . number_format($price, 2, '.', '') . "\n";
        echo "  -> tax group: {$taxGroupId}\n";
        echo "  -> quantity: {$quantity}\n";
        echo "  -> active: {$active}\n";
        echo "  -> map: {$sourceId} -> {$productId}\n\n";
    } catch (Throwable $e) {
        Db::getInstance()->execute('ROLLBACK');

        throw $e;
    }
}

echo "=== KONIEC ===\n";
echo "Utworzono: {$created}\n";
echo "Pominięto: {$skipped}\n";

