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

    public function getItems($category = '', $offset = 0, $limit = 20)
    {
        $items = array();
        $matched = 0;

        $this->streamItems(function ($raw) use (&$items, &$matched, $category, $offset, $limit) {
            $item = $this->parseItem($raw);
            if (!$item) return;
            if ($category !== '' && !$this->hasCategory($item['categories'], $category)) return;

            $matched++;
            if ($matched <= $offset || count($items) >= $limit) return;
            $items[] = $item;
        });

        return array('items' => $items, 'total' => $matched);
    }

    public function getCategoryTree()
    {
        $tree = array();
        $seen = array();

        $this->streamItems(function ($raw) use (&$tree, &$seen) {
            $item = $this->parseItem($raw);
            if (!$item) return;

            foreach ($item['categories'] as $path) {
                $parts = array_values(array_filter(array_map('trim', explode('>', $path)), 'strlen'));
                if (!$parts) continue;

                $node =& $tree;
                $pathKey = '';
                foreach ($parts as $part) {
                    $pathKey = $pathKey === '' ? $part : $pathKey . ' > ' . $part;

                    if (!isset($node[$part])) {
                        $node[$part] = array('_count' => 0, '_ids' => array(), '_children' => array());
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
        if (!$h) throw new Exception('Nie można otworzyć feedu.');

        $buffer = '';
        while (!feof($h)) {
            $chunk = fread($h, 1048576);
            if ($chunk === false) break;
            $buffer .= $chunk;

            while (($start = strpos($buffer, '<item>')) !== false) {
                $end = strpos($buffer, '</item>', $start);
                if ($end === false) {
                    if ($start > 0) $buffer = substr($buffer, $start);
                    break;
                }

                $raw = substr($buffer, $start, $end + 7 - $start);
                $callback($raw);
                $buffer = substr($buffer, $end + 7);
            }
        }

        fclose($h);
    }

    private function parseItem($raw)
    {
        $id = $this->tag($raw, 'g:id');
        if ($id === '') return null;

        return array(
            'id' => $id,
            'title' => $this->tag($raw, 'title'),
            'link' => $this->tag($raw, 'link'),
            'image' => $this->tag($raw, 'g:image_link'),
            'additional_images' => $this->tags($raw, 'g:additional_image_link'),
            'brand' => $this->tag($raw, 'g:brand'),
            'gtin' => $this->tag($raw, 'g:gtin'),
            'mpn' => $this->tag($raw, 'g:mpn'),
            'price' => $this->tag($raw, 'g:price'),
            'sale_price' => $this->tag($raw, 'g:sale_price'),
            'availability' => $this->tag($raw, 'g:availability'),
            'description' => $this->tag($raw, 'description'),
            'categories' => $this->tags($raw, 'g:product_type'),
        );
    }

    private function tag($raw, $name)
    {
        if (!preg_match('~<' . preg_quote($name, '~') . '(?:\s[^>]*)?>(.*?)</' . preg_quote($name, '~') . '>~is', $raw, $m)) {
            return '';
        }
        return $this->clean($m[1]);
    }

    private function tags($raw, $name)
    {
        preg_match_all('~<' . preg_quote($name, '~') . '(?:\s[^>]*)?>(.*?)</' . preg_quote($name, '~') . '>~is', $raw, $m);
        $out = array();

        foreach ($m[1] as $v) {
            $v = $this->clean($v);
            if ($v !== '' && !in_array($v, $out, true)) {
                $out[] = $v;
            }
        }

        return $out;
    }

    private function clean($value)
    {
        $value = preg_replace('/<!\[CDATA\[(.*?)\]\]>/s', '$1', $value);
        $value = html_entity_decode(trim($value), ENT_QUOTES | ENT_XML1, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    private function hasCategory($categories, $category)
    {
        foreach ($categories as $path) {
            if ($path === $category || strpos($path, $category . ' >') === 0) {
                return true;
            }
        }
        return false;
    }
}
