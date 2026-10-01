<?php

require '/var/www/html/config/config.inc.php';

$ids = range(1, 19);

echo "=== USUWANIE PRODUKTÓW DEMO ===\n";

foreach ($ids as $id) {
    $product = new Product($id);

    if (!Validate::isLoadedObject($product)) {
        echo "[SKIP] Produkt #{$id} nie istnieje\n";
        continue;
    }

    $name = is_array($product->name)
        ? ($product->name[1] ?? reset($product->name))
        : $product->name;

    echo "[DELETE] #{$id} - {$name}\n";

    if (!$product->delete()) {
        throw new RuntimeException(
            "Nie udało się usunąć produktu #{$id}"
        );
    }
}

echo "\n=== GOTOWE ===\n";
