<?php

require '/var/www/html/config/config.inc.php';

const CSV_FILE = '/tmp/prestashop-categories-import.csv';
const SHOP_ID = 1;
const LANG_ID = 1;
const ROOT_CATEGORY_ID = 2;

if (!is_file(CSV_FILE)) {
    throw new RuntimeException('Brak pliku: ' . CSV_FILE);
}

function normalizeName(string $name): string
{
    $name = trim($name);
    return preg_replace('/\s+/', ' ', $name);
}

function slugify(string $value): string
{
    $value = trim($value);

    $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

    if ($converted !== false) {
        $value = $converted;
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    $value = trim($value, '-');

    return $value !== '' ? $value : 'category';
}

function findCategory(
    string $name,
    int $parentId
): ?int {
    $query = new DbQuery();

    $query->select('c.id_category');
    $query->from('category', 'c');
    $query->innerJoin(
        'category_lang',
        'cl',
        'cl.id_category = c.id_category
        AND cl.id_lang = ' . LANG_ID . '
        AND cl.id_shop = ' . SHOP_ID
    );
    $query->where(
        "cl.name = '" . pSQL($name) . "'"
    );
    $query->where(
        'c.id_parent = ' . $parentId
    );

    $id = Db::getInstance()->getValue($query);

    return $id !== false && $id !== null
        ? (int) $id
        : null;
}

function createCategory(
    string $name,
    int $parentId
): int {
    $category = new Category();

    $category->id_parent = $parentId;
    $category->active = 1;
    $category->is_root_category = 0;

    $category->name = [
        LANG_ID => $name,
    ];

    $category->link_rewrite = [
        LANG_ID => slugify($name),
    ];

    $category->description = [
        LANG_ID => '',
    ];

    $category->meta_title = [
        LANG_ID => $name,
    ];

    $category->meta_description = [
        LANG_ID => '',
    ];

    $category->meta_keywords = [
        LANG_ID => '',
    ];

    if (!$category->add()) {
        throw new RuntimeException(
            'Nie udało się utworzyć kategorii: ' . $name
        );
    }

    $category->associateTo([SHOP_ID]);

    return (int) $category->id;
}

echo "=== SOCZEWKI24 - IMPORT KATEGORII ===\n";
echo "Prefix: " . _DB_PREFIX_ . "\n";
echo "Shop: " . SHOP_ID . "\n";
echo "Language: " . LANG_ID . "\n";
echo "Parent root: " . ROOT_CATEGORY_ID . "\n\n";

$handle = fopen(CSV_FILE, 'rb');

if ($handle === false) {
    throw new RuntimeException('Nie można otworzyć CSV.');
}

$header = fgetcsv($handle, 0, ';');

if ($header === false) {
    throw new RuntimeException('CSV jest pusty.');
}

$header = array_map(
    static fn($value) => trim(
        (string) $value,
        "\" \t\n\r\0\x0B"
    ),
    $header
);

$pathIndex = array_search(
    'source_path',
    $header,
    true
);

if ($pathIndex === false) {
    throw new RuntimeException(
        'Brak kolumny source_path.'
    );
}

$paths = [];

while (($row = fgetcsv($handle, 0, ';')) !== false) {
    if (!isset($row[$pathIndex])) {
        continue;
    }

    $path = trim($row[$pathIndex]);

    if ($path === '') {
        continue;
    }

    $paths[$path] = true;
}

fclose($handle);

$paths = array_keys($paths);

usort(
    $paths,
    static function ($a, $b) {
        $depthA = substr_count($a, '/');
        $depthB = substr_count($b, '/');

        if ($depthA !== $depthB) {
            return $depthA <=> $depthB;
        }

        return strcmp($a, $b);
    }
);

echo "Ścieżek do przetworzenia: " . count($paths) . "\n\n";

$created = 0;
$existing = 0;

foreach ($paths as $path) {
    $parts = array_values(
        array_filter(
            array_map(
                'normalizeName',
                explode('/', $path)
            ),
            static fn($value) => $value !== ''
        )
    );

    $parentId = ROOT_CATEGORY_ID;
    $currentPath = [];

    foreach ($parts as $name) {
        $currentPath[] = $name;

        $existingId = findCategory(
            $name,
            $parentId
        );

        if ($existingId !== null) {
            $parentId = $existingId;
            $existing++;
            continue;
        }

        $newId = createCategory(
            $name,
            $parentId
        );

        $parentId = $newId;
        $created++;

        echo sprintf(
            "[CREATE] #%d %s\n",
            $newId,
            implode(' / ', $currentPath)
        );
    }
}

echo "\n=== ZAKOŃCZONO ===\n";
echo "Utworzono poziomów: {$created}\n";
echo "Istniejących poziomów: {$existing}\n";
echo "Ścieżek: " . count($paths) . "\n";

