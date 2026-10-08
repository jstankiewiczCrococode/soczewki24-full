<?php

class SoczewkiImporterFeedReader
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;

        if (!is_readable($file)) {
            throw new Exception('Nie można odczytać feedu: ' . $file);
        }
    }

    public function getItems($category = '', $offset = 0, $limit = 100)
    {
        $items = array();
        $matched = 0;

        $this->streamItems(function ($raw) use (&$items, &$matched, $category, $offset, $limit) {
            $item = $this->parseItem($raw);

            if (!$item) {
                return;
            }

            if ($category !== '' && !$this->hasCategory($item['categories'], $category)) {
                return;
            }

            $matched++;

            if ($matched <= $offset || count($items) >= $limit) {
                return;
            }

            $items[] = $item;
        });

        return array(
            'items' => $items,
            'total' => $matched
        );
    }

    public function getCategoryTree()
    {
        $tree = array();
        $seen = array();

        $this->streamItems(function ($raw) use (&$tree, &$seen) {
            $item = $this->parseItem($raw);

            if (!$item) {
                return;
            }

            foreach ($item['categories'] as $path) {
                $parts = array_values(
                    array_filter(
                        array_map('trim', explode('>', $path)),
                        'strlen'
                    )
                );

                if (!$parts) {
                    continue;
                }

                $node =& $tree;
                $pathKey = '';

                foreach ($parts as $part) {
                    $pathKey = $pathKey === ''
                        ? $part
                        : $pathKey . ' > ' . $part;

                    if (!isset($node[$part])) {
                        $node[$part] = array(
                            '_count' => 0,
                            '_ids' => array(),
                            '_children' => array()
                        );
                    }

                    if (!isset($seen[$pathKey][$item['id']])) {
                        $node[$part]['_count']++;
                        $node[$part]['_ids'][$item['id']] = true;
                        $seen[$pathKey][$item['id']] = true;
                    }

                    $node =& $node[$part]['_children'];
                }

                unset($node);
            }
        });

        return $tree;
    }

    private function streamItems($callback)
    {
        $h = fopen($this->file, 'rb');

        if (!$h) {
            throw new Exception('Nie można otworzyć feedu.');
        }

        $buffer = '';

        while (!feof($h)) {
            $chunk = fread($h, 1048576);

            if ($chunk === false) {
                break;
            }

            $buffer .= $chunk;

            while (($start = strpos($buffer, '<item>')) !== false) {
                $end = strpos($buffer, '</item>', $start);

                if ($end === false) {
                    if ($start > 0) {
                        $buffer = substr($buffer, $start);
                    }

                    break;
                }

                $raw = substr(
                    $buffer,
                    $start,
                    $end + 7 - $start
                );

                $callback($raw);

                $buffer = substr($buffer, $end + 7);
            }
        }

        fclose($h);
    }

    private function parseItem($raw)
    {
        $id = $this->tag($raw, 'g:id');

        if ($id === '') {
            return null;
        }

        $title = $this->tag($raw, 'title');
        $description = $this->tag($raw, 'description');
        $mpn = $this->tag($raw, 'g:mpn');
        $categories = $this->tags($raw, 'g:product_type');

        return array(
            'id' => $id,

            'title' => $title,

            'link' => $this->tag($raw, 'link'),

            'image' => $this->tag($raw, 'g:image_link'),

            'additional_images' => $this->tags(
                $raw,
                'g:additional_image_link'
            ),

            'brand' => $this->tag($raw, 'g:brand'),

            'gtin' => $this->tag($raw, 'g:gtin'),

            'mpn' => $mpn,

            'price' => $this->tag($raw, 'g:price'),

            'sale_price' => $this->tag($raw, 'g:sale_price'),

            'availability' => $this->tag(
                $raw,
                'g:availability'
            ),

            'description' => $description,

            'categories' => $categories,

            /*
             * DANE DO FILTRÓW
             */

            'style' => $this->extractStyles($categories),

            'material' => $this->extractCategoryValues(
                $categories,
                'Materiał oprawy'
            ),

            'shape' => $this->extractCategoryValues(
                $categories,
                'Kształt oprawek'
            ),

            'colors' => $this->extractCategoryValues(
                $categories,
                'Kolor oprawek'
            ),

            'size' => $this->extractSize(
                $title,
                $description,
                $mpn
            ),
        );
    }

    /**
     * Zwraca style:
     *
     * Okulary damskie
     * Okulary męskie
     * Okulary unisex
     */
    private function extractStyles($categories)
    {
        $styles = array();

        foreach ($categories as $category) {
            $category = trim($category);

                if (preg_match(
                    '/(?:^|>\s*)Okulary(?:\s+przeciwsłoneczne)?\s+(damskie|męskie|unisex)\s*$/iu',
                    $category,
                    $m
                )) {
                $style = trim($m[1]);

                if (!in_array($style, $styles, true)) {
                    $styles[] = $style;
                }
            }
        }

        return $styles;
    }

    /**
     * Pobiera wartości z kategorii typu:
     *
     * Okulary korekcyjne > Materiał oprawy > Plastik
     *
     * Okulary korekcyjne > Kształt oprawek > Kocie
     *
     * Okulary korekcyjne > Kolor oprawek > Złote
     */
    private function extractCategoryValues($categories, $label)
    {
        $values = array();

        foreach ($categories as $category) {
            $parts = array_map(
                'trim',
                explode('>', $category)
            );

            $count = count($parts);

            for ($i = 0; $i < $count - 1; $i++) {
                if (strcasecmp($parts[$i], $label) === 0) {
                    $value = trim($parts[$i + 1]);

                    if (
                        $value !== '' &&
                        !in_array($value, $values, true)
                    ) {
                        $values[] = $value;
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Rozmiar:
     *
     * 1. title
     * 2. description
     * 3. mpn
     *
     * Przykłady:
     * GUESS ... rozmiar 53
     * HARLEY ... 56
     * GU50114 53 020
     */
    private function extractSize($title, $description, $mpn)
    {
        /*
         * Najpierw szukamy "rozmiar 53"
         */
        $texts = array(
            $title,
            $description
        );

        foreach ($texts as $text) {
            if (preg_match(
                '/\brozmiar\s*[:\-]?\s*(\d{2})\b/iu',
                $text,
                $m
            )) {
                return $m[1];
            }
        }

        /*
         * W przypadku Harley-Davidson:
         *
         * HD50019 009 56
         *
         * albo:
         *
         * HD50019 56 009
         *
         * próbujemy znaleźć 2-cyfrowy rozmiar
         * w MPN.
         */
        if (preg_match_all(
            '/\b(\d{2})\b/',
            $mpn,
            $matches
        )) {
            foreach ($matches[1] as $number) {
                /*
                 * Typowe rozmiary okularów:
                 * 48-70
                 */
                $size = (int) $number;

                if ($size >= 40 && $size <= 70) {
                    return $number;
                }
            }
        }

        return '';
    }

    private function tag($raw, $name)
    {
        if (!preg_match(
            '~<' . preg_quote($name, '~') .
            '(?:\s[^>]*)?>(.*?)</' .
            preg_quote($name, '~') .
            '>~is',
            $raw,
            $m
        )) {
            return '';
        }

        return $this->clean($m[1]);
    }

    private function tags($raw, $name)
    {
        preg_match_all(
            '~<' . preg_quote($name, '~') .
            '(?:\s[^>]*)?>(.*?)</' .
            preg_quote($name, '~') .
            '>~is',
            $raw,
            $m
        );

        $out = array();

        foreach ($m[1] as $v) {
            $v = $this->clean($v);

            if (
                $v !== '' &&
                !in_array($v, $out, true)
            ) {
                $out[] = $v;
            }
        }

        return $out;
    }

    private function clean($value)
    {
        $value = preg_replace(
            '/<!\[CDATA\[(.*?)\]\]>/s',
            '$1',
            $value
        );

        $value = html_entity_decode(
            trim($value),
            ENT_QUOTES | ENT_XML1,
            'UTF-8'
        );

        return trim(
            preg_replace(
                '/\s+/u',
                ' ',
                $value
            )
        );
    }

    private function hasCategory($categories, $category)
    {
        foreach ($categories as $path) {
            if (
                $path === $category ||
                strpos($path, $category . ' >') === 0
            ) {
                return true;
            }
        }

        return false;
    }
}