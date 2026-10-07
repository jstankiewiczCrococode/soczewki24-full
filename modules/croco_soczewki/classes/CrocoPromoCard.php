<?php
/**
 * Karta promocyjna na stronie produktu (komponent promo-card w motywie).
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CrocoPromoCard extends ObjectModel
{
    public const SLOT_SIDEBAR = 'sidebar';
    public const SLOT_DESCRIPTION = 'description';
    public const SLOTS = [self::SLOT_SIDEBAR, self::SLOT_DESCRIPTION];

    public const VARIANTS = ['default', 'subtle'];
    public const ICON_TONES = ['primary', 'dark'];

    // Ikony z themes/soczewki24/templates/components/icons/. Nowa ikona = nowy plik SVG + wpis tutaj.
    public const ICONS = ['mappin', 'eyeglasses', 'magnifyingglass', 'shoppingbag', 'user'];

    public const IMAGE_DIR_NAME = 'croco_promo';

    public $id_shop;
    public $slot = self::SLOT_SIDEBAR;
    public $variant = 'default';
    public $icon = '';
    public $icon_tone = 'primary';
    public $image = '';
    public $position = 0;
    public $active = 1;

    /** @var string|string[] */
    public $title;
    /** @var string|string[] */
    public $text;
    /** @var string|string[] */
    public $link_label;
    /** @var string|string[] */
    public $link_url;

    public static $definition = [
        'table' => 'croco_promo_card',
        'primary' => 'id_card',
        'multilang' => true,
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'slot' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 16],
            'variant' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 16],
            'icon' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 32],
            'icon_tone' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 16],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],

            'title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'text' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 1000],
            'link_label' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 128],
            'link_url' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 255],
        ],
    ];

    public function add($autoDate = true, $nullValues = false)
    {
        if (!$this->id_shop) {
            $this->id_shop = (int) Context::getContext()->shop->id;
        }

        if (!(int) $this->position) {
            $this->position = self::getNextPosition();
        }

        return parent::add($autoDate, $nullValues);
    }

    public function delete()
    {
        $this->deleteImageFile();
        Db::getInstance()->delete('croco_promo_card_category', 'id_card = ' . (int) $this->id);

        return parent::delete();
    }

    private static function getNextPosition(): int
    {
        return 1 + (int) Db::getInstance()->getValue(
            'SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . 'croco_promo_card`'
        );
    }

    /**
     * Schemat musi byc taki sam jak w BO - w href nie moze wyladowac javascript:...,
     * a Smarty escapuje tylko znaki HTML, nie schemat.
     */
    public static function isSafeUrl(string $url): bool
    {
        return (bool) preg_match('#^(https?://|/(?!/)|\#|$)#i', $url);
    }

    /**
     * @return int[]
     */
    public function getCategoryIds(): array
    {
        $rows = Db::getInstance()->executeS(
            'SELECT `id_category` FROM `' . _DB_PREFIX_ . 'croco_promo_card_category`
            WHERE `id_card` = ' . (int) $this->id
        );

        return array_map('intval', array_column($rows ?: [], 'id_category'));
    }

    /**
     * @param int[] $categoryIds
     */
    public function setCategories(array $categoryIds): void
    {
        $db = Db::getInstance();
        $db->delete('croco_promo_card_category', 'id_card = ' . (int) $this->id);

        foreach (array_unique(array_map('intval', $categoryIds)) as $idCategory) {
            if ($idCategory > 0) {
                $db->insert('croco_promo_card_category', [
                    'id_card' => (int) $this->id,
                    'id_category' => $idCategory,
                ]);
            }
        }
    }

    public static function getImageDir(): string
    {
        return _PS_IMG_DIR_ . self::IMAGE_DIR_NAME . '/';
    }

    public static function getImageUrl(string $file): string
    {
        return Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . 'img/' . self::IMAGE_DIR_NAME . '/' . rawurlencode($file);
    }

    public function deleteImageFile(): void
    {
        $file = basename((string) $this->image);

        if ($file !== '' && is_file(self::getImageDir() . $file)) {
            @unlink(self::getImageDir() . $file);
        }
    }

    /**
     * Aktywne karty dla produktu w danym slocie, w kolejnosci z BO.
     * Karta pasuje, gdy nie ma przypisanych kategorii albo produkt lezy w jednej
     * z nich lub w jej podkategorii (drzewo nleft/nright).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getForProduct(int $idProduct, string $slot, int $idLang, int $idShop): array
    {
        if ($idProduct <= 0 || !in_array($slot, self::SLOTS, true)) {
            return [];
        }

        $db = Db::getInstance();
        $categoryIds = array_map('intval', array_column($db->executeS(
            'SELECT DISTINCT parent.`id_category`
            FROM `' . _DB_PREFIX_ . 'category_product` cp
            INNER JOIN `' . _DB_PREFIX_ . 'category` c ON c.`id_category` = cp.`id_category`
            INNER JOIN `' . _DB_PREFIX_ . 'category` parent
                ON parent.`nleft` <= c.`nleft` AND parent.`nright` >= c.`nright`
            WHERE cp.`id_product` = ' . $idProduct
        ) ?: [], 'id_category'));

        $pivot = '`' . _DB_PREFIX_ . 'croco_promo_card_category`';
        $categoryCondition = 'NOT EXISTS (SELECT 1 FROM ' . $pivot . ' x WHERE x.`id_card` = a.`id_card`)';

        if ($categoryIds) {
            $categoryCondition .= ' OR EXISTS (SELECT 1 FROM ' . $pivot . ' x
                WHERE x.`id_card` = a.`id_card` AND x.`id_category` IN (' . implode(',', $categoryIds) . '))';
        }

        $rows = $db->executeS(
            'SELECT a.*, l.`title`, l.`text`, l.`link_label`, l.`link_url`
            FROM `' . _DB_PREFIX_ . 'croco_promo_card` a
            INNER JOIN `' . _DB_PREFIX_ . 'croco_promo_card_lang` l
                ON l.`id_card` = a.`id_card` AND l.`id_lang` = ' . $idLang . '
            WHERE a.`active` = 1
                AND a.`slot` = \'' . pSQL($slot) . '\'
                AND a.`id_shop` = ' . $idShop . '
                AND l.`title` != \'\'
                AND (' . $categoryCondition . ')
            ORDER BY a.`position` ASC, a.`id_card` ASC'
        ) ?: [];

        foreach ($rows as &$row) {
            if (!self::isSafeUrl((string) $row['link_url'])) {
                $row['link_url'] = '';
            }

            $row['image_url'] = $row['image'] !== '' && is_file(self::getImageDir() . basename($row['image']))
                ? self::getImageUrl($row['image'])
                : '';
        }

        return $rows;
    }
}
