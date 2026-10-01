<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class CrocoMegamenuItem extends ObjectModel
{
    public const COLUMN_COUNT = 4;

    private const BLOCK_TYPES = ['links', 'banner', 'cta'];

    public $id_shop;
    public $label;
    public $link_type = 'custom';
    public $id_object;
    public $custom_url;
    public $is_highlighted = 0;
    public $position = 0;
    public $active = 1;

    public static $definition = [
        'table' => 'croco_megamenu_item',
        'primary' => 'id_item',
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'label' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 128],
            'link_type' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 16],
            'id_object' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'allow_null' => true],
            'custom_url' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'is_highlighted' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ],
    ];

    public function add($autoDate = true, $nullValues = false)
    {
        if (!$this->id_shop) {
            $this->id_shop = (int) Context::getContext()->shop->id;
        }

        $this->position = self::getNextPosition();

        return parent::add($autoDate, $nullValues);
    }

    public function delete()
    {
        $this->deleteColumnsAndLinks();

        $result = parent::delete();
        self::cleanPositions();

        return $result;
    }

    private function deleteColumnsAndLinks(): void
    {
        self::deleteBlocks((int) $this->id);
    }

    public static function deleteBlocks(int $idItem): void
    {
        $db = Db::getInstance();
        $blocks = $db->executeS(
            'SELECT `id_block` FROM `' . _DB_PREFIX_ . 'croco_megamenu_block` WHERE `id_item` = ' . $idItem
        );

        foreach ($blocks as $block) {
            $db->delete('croco_megamenu_link', 'id_block = ' . (int) $block['id_block']);
        }

        $db->delete('croco_megamenu_block', 'id_item = ' . $idItem);
    }

    public static function getBlocks(int $idItem): array
    {
        $db = Db::getInstance();
        $blocks = $db->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'croco_megamenu_block`
            WHERE `id_item` = ' . $idItem . '
            ORDER BY `column_index` ASC, `position` ASC'
        );

        if (!$blocks) {
            return [];
        }

        foreach ($blocks as &$block) {
            $block['links'] = $db->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'croco_megamenu_link`
                WHERE `id_block` = ' . (int) $block['id_block'] . '
                ORDER BY `position` ASC'
            ) ?: [];
        }

        return $blocks;
    }

    public static function getColumns(int $idItem): array
    {
        $columns = array_fill(0, self::COLUMN_COUNT, ['blocks' => []]);

        foreach (self::getBlocks($idItem) as $block) {
            $index = (int) $block['column_index'];

            if ($index < 0 || $index >= self::COLUMN_COUNT) {
                $index = 0;
            }

            $columns[$index]['blocks'][] = $block;
        }

        return $columns;
    }

    public static function saveColumns(int $idItem, array $columns): void
    {
        self::deleteBlocks($idItem);

        $db = Db::getInstance();

        foreach ($columns as $columnIndex => $column) {
            foreach (($column['blocks'] ?? []) as $blockPosition => $block) {
                $type = (string) ($block['type'] ?? 'links');

                $db->insert('croco_megamenu_block', [
                    'id_item' => $idItem,
                    'column_index' => (int) $columnIndex,
                    'type' => in_array($type, self::BLOCK_TYPES, true) ? $type : 'links',
                    'title' => pSQL((string) ($block['title'] ?? '')),
                    'position' => (int) $blockPosition,
                ]);

                $idBlock = (int) $db->Insert_ID();

                foreach (($block['links'] ?? []) as $linkPosition => $link) {
                    $db->insert('croco_megamenu_link', [
                        'id_block' => $idBlock,
                        'label' => pSQL((string) ($link['label'] ?? '')),
                        'link_type' => 'custom',
                        'custom_url' => pSQL((string) ($link['url'] ?? '#')),
                        'image' => pSQL((string) ($link['image'] ?? '')),
                        'position' => (int) $linkPosition,
                    ]);
                }
            }
        }
    }

    public static function columnsToJson(int $idItem): string
    {
        $export = [];

        foreach (self::getColumns($idItem) as $column) {
            $blocks = [];

            foreach ($column['blocks'] as $block) {
                $links = [];

                foreach ($block['links'] as $link) {
                    $links[] = [
                        'label' => $link['label'],
                        'url' => $link['custom_url'],
                        'image' => $link['image'],
                    ];
                }

                $blocks[] = [
                    'type' => $block['type'],
                    'title' => $block['title'],
                    'links' => $links,
                ];
            }

            $export[] = ['blocks' => $blocks];
        }

        return (string) json_encode($export, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function getNextPosition(): int
    {
        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'croco_megamenu_item`'
        );
    }

    public function updatePosition(int $way, int $newPosition): bool
    {
        $currentPosition = (int) $this->position;

        if ($currentPosition === $newPosition) {
            return true;
        }

        $db = Db::getInstance();
        $shift = $way ? '`position` - 1' : '`position` + 1';
        $range = $way
            ? '`position` > ' . $currentPosition . ' AND `position` <= ' . $newPosition
            : '`position` < ' . $currentPosition . ' AND `position` >= ' . $newPosition;

        return $db->execute(
            'UPDATE `' . _DB_PREFIX_ . 'croco_megamenu_item` SET `position` = ' . $shift . ' WHERE ' . $range
        ) && $db->execute(
            'UPDATE `' . _DB_PREFIX_ . 'croco_megamenu_item`
            SET `position` = ' . $newPosition . '
            WHERE `id_item` = ' . (int) $this->id
        );
    }

    public static function cleanPositions(): void
    {
        $db = Db::getInstance();
        $items = $db->executeS(
            'SELECT `id_item` FROM `' . _DB_PREFIX_ . 'croco_megamenu_item` ORDER BY `position` ASC'
        );

        foreach ($items as $position => $item) {
            $db->update(
                'croco_megamenu_item',
                ['position' => (int) $position],
                'id_item = ' . (int) $item['id_item']
            );
        }
    }
}
