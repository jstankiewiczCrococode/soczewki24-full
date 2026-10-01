<?php
class SoczewkiImporterProductImporter
{
    private $context;
    private $mapper;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->mapper = new SoczewkiImporterCategoryMapper();
    }

    public function importItems(array $items)
    {
        $result = array(
            'created' => 0,
            'updated' => 0,
            'errors' => array(),
            'products' => array(),
        );

        foreach ($items as $item) {
            try {
                $mapped = $this->mapper->mapPaths($item['categories']);

                $categoryIds = array();
                foreach ($mapped as $path => $id) {
                    if ((int)$id > 0) {
                        $categoryIds[] = (int)$id;
                    }
                }
                $categoryIds = array_values(array_unique($categoryIds));

                if (!$categoryIds) {
                    throw new Exception('Brak zmapowanej kategorii.');
                }

                $existingId = $this->findByFeedId($item['id']);
                $product = $existingId ? new Product($existingId) : new Product();

                $isNew = !$existingId;

                $product->name = $this->makeLangValue($item['title']);
                $product->description = $this->makeLangValue($item['description']);
                $product->description_short = $this->makeLangValue($this->makeShortDescription($item['description']));
                $product->link_rewrite = $this->makeLangValue(Tools::str2url($item['title']));
                $product->price = $this->parsePrice($item['sale_price'] !== '' ? $item['sale_price'] : $item['price']);
                $product->reference = $this->makeReference($item);
                $product->ean13 = $this->validEan($item['gtin']) ? $item['gtin'] : '';
                $product->active = 1;
                $product->available_for_order = 1;
                $product->show_price = 1;
                $product->visibility = 'both';
                $product->condition = 'new';
                $product->id_category_default = (int)$categoryIds[0];

                if (!$product->id_manufacturer && trim($item['brand']) !== '' && trim($item['brand']) !== '- brak producenta -') {
                    $product->id_manufacturer = $this->getOrCreateManufacturer($item['brand']);
                }

                if ($isNew) {
                    if (!$product->add()) {
                        throw new Exception('Nie udało się utworzyć produktu.');
                    }
                    $result['created']++;
                } else {
                    if (!$product->update()) {
                        throw new Exception('Nie udało się zaktualizować produktu.');
                    }
                    $result['updated']++;
                }

                $product->updateCategories($categoryIds);

                $imageCount = 0;
                if ($isNew && $item['image'] !== '') {
                    $imageCount += $this->addImage($product, $item['image'], true);
                    foreach ($item['additional_images'] as $url) {
                        $imageCount += $this->addImage($product, $url, false);
                    }
                }

                $result['products'][] = array(
                    'feed_id' => $item['id'],
                    'product_id' => (int)$product->id,
                    'title' => $item['title'],
                    'images' => $imageCount,
                    'categories' => $categoryIds,
                );
            } catch (Exception $e) {
                $result['errors'][] = array(
                    'feed_id' => $item['id'],
                    'title' => $item['title'],
                    'message' => $e->getMessage(),
                );
            }
        }

        return $result;
    }

    private function findByFeedId($feedId)
    {
        // The feed ID is stored as reference prefix, allowing repeatable imports
        // without adding a custom database table.
        $needle = pSQL('FEED-' . $feedId);

        $id = Db::getInstance()->getValue(
            'SELECT id_product FROM `' . _DB_PREFIX_ . 'product`
             WHERE reference = "' . $needle . '"
             OR reference LIKE "' . $needle . '-%"'
        );

        return (int)$id;
    }

    private function makeReference(array $item)
    {
        $base = trim($item['mpn']) !== '' ? trim($item['mpn']) : ('FEED-' . trim($item['id']));
        $base = preg_replace('/[^A-Za-z0-9._-]+/', '-', $base);
        $base = trim($base, '-');

        // Keep feed identity unambiguous for repeat imports.
        $ref = 'FEED-' . trim($item['id']);
        if ($base !== '') {
            $ref .= '-' . $base;
        }

        return Tools::substr($ref, 0, 32);
    }

    private function parsePrice($value)
    {
        $value = trim(str_replace(array('PLN', ' '), '', str_replace(',', '.', $value)));
        return (float)$value;
    }

    private function validEan($value)
    {
        $value = preg_replace('/\D+/', '', (string)$value);
        return strlen($value) === 13;
    }

    private function makeLangValue($value)
    {
        $out = array();
        foreach (Language::getIDs(false) as $idLang) {
            $out[(int)$idLang] = (string)$value;
        }
        return $out;
    }

    private function makeShortDescription($description)
    {
        $text = trim(strip_tags((string)$description));
        if (Tools::strlen($text) > 500) {
            $text = Tools::substr($text, 0, 497) . '...';
        }
        return $text;
    }

    private function getOrCreateManufacturer($name)
    {
        $id = (int)Db::getInstance()->getValue(
            'SELECT id_manufacturer
             FROM `' . _DB_PREFIX_ . 'manufacturer`
             WHERE name = "' . pSQL($name) . '"'
        );

        if ($id) return $id;

        $manufacturer = new Manufacturer();
        $manufacturer->name = $name;
        $manufacturer->active = 1;

        if (!$manufacturer->add()) {
            throw new Exception('Nie udało się utworzyć producenta: ' . $name);
        }

        return (int)$manufacturer->id;
    }

    private function downloadImage($url, $destination)
    {
        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'Soczewki24Importer/0.3.5',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ));

            $data = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($data !== false && $httpCode >= 200 && $httpCode < 300 && $data !== '') {
                if (file_put_contents($destination, $data) !== false) {
                    return true;
                }
            }

            if ($attempt < $maxAttempts) {
                usleep(500000);
            }
        }

        return false;
    }

    private function addImage(Product $product, $url, $cover)
    {
        if (!$url) return 0;

        $image = new Image();
        $image->id_product = (int)$product->id;
        $image->position = Image::getHighestPosition($product->id) + 1;
        $image->cover = $cover ? 1 : 0;

        if (!$image->add()) {
            return 0;
        }

        $path = $image->getPathForCreation();
        $tmp = tempnam(_PS_TMP_IMG_DIR_, 'si_');

        if (!$tmp || !$this->downloadImage($url, $tmp)) {
            $image->delete();
            if ($tmp && file_exists($tmp)) @unlink($tmp);
            return 0;
        }

        $ok = ImageManager::resize($tmp, $path . '.jpg');
        @unlink($tmp);

        if (!$ok) {
            $image->delete();
            return 0;
        }

        $types = ImageType::getImagesTypes('products');
        foreach ($types as $type) {
            ImageManager::resize(
                $path . '.jpg',
                $path . '-' . stripslashes($type['name']) . '.jpg',
                (int)$type['width'],
                (int)$type['height']
            );
        }

        return 1;
    }
}
