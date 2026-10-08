<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'croco_megamenu/classes/CrocoMegamenuItem.php';

class Croco_Megamenu extends Module
{
    private const MENU_HOOK = 'displayTop';
    private const ADMIN_CONTROLLER = 'AdminCrocoMegamenu';

    public function __construct()
    {
        $this->name = 'croco_megamenu';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'CrocoCode';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '9.1.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Mega menu Soczewki24', [], 'Modules.Crocomegamenu.Admin');
        $this->description = $this->trans('Wlasne mega menu glownej nawigacji (desktop i mobile).', [], 'Modules.Crocomegamenu.Admin');
        $this->confirmUninstall = $this->trans('Na pewno? Zawartosc menu zostanie w bazie, ale menu przestanie sie wyswietlac.', [], 'Modules.Crocomegamenu.Admin');
    }

    public function install(): bool
    {
        return parent::install()
            && $this->installDatabase()
            && $this->installTab()
            && $this->registerHook(self::MENU_HOOK);
    }

    public function uninstall(): bool
    {
        return $this->uninstallTab() && parent::uninstall();
    }

    private function installTab(): bool
    {
        $tab = new Tab();
        $tab->class_name = self::ADMIN_CONTROLLER;
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentThemes');
        $tab->icon = 'menu';
        $tab->active = true;

        foreach (Language::getLanguages(false) as $language) {
            $tab->name[(int) $language['id_lang']] = 'Mega menu';
        }

        return (bool) $tab->add();
    }

    private function uninstallTab(): bool
    {
        $idTab = (int) Tab::getIdFromClassName(self::ADMIN_CONTROLLER);

        if (!$idTab) {
            return true;
        }

        $tab = new Tab($idTab);

        return (bool) $tab->delete();
    }

    public function getContent(): void
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::ADMIN_CONTROLLER));
    }

    private function installDatabase(): bool
    {
        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'croco_megamenu_item` (
            `id_item` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
            `label` VARCHAR(128) NOT NULL,
            `link_type` ENUM(\'category\',\'cms\',\'custom\') NOT NULL DEFAULT \'custom\',
            `id_object` INT UNSIGNED NULL,
            `custom_url` VARCHAR(255) NULL,
            `is_highlighted` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            `position` INT UNSIGNED NOT NULL DEFAULT 0,
            `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
            PRIMARY KEY (`id_item`),
            KEY `idx_position` (`position`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'croco_megamenu_block` (
            `id_block` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_item` INT UNSIGNED NOT NULL,
            `column_index` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `type` ENUM(\'links\',\'banner\',\'cta\') NOT NULL DEFAULT \'links\',
            `title` VARCHAR(128) NULL,
            `position` INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_block`),
            KEY `idx_item` (`id_item`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'croco_megamenu_link` (
            `id_link` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_block` INT UNSIGNED NOT NULL,
            `label` VARCHAR(128) NOT NULL,
            `link_type` ENUM(\'category\',\'cms\',\'product\',\'custom\') NOT NULL DEFAULT \'custom\',
            `id_object` INT UNSIGNED NULL,
            `custom_url` VARCHAR(255) NULL,
            `image` VARCHAR(255) NULL,
            `position` INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_link`),
            KEY `idx_block` (`id_block`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    public function hookActionFrontControllerSetMedia(): void
    {
    }

    public function hookDisplayTop(): string
    {
        $items = $this->getMenuItems();

        if (empty($items)) {
            return '';
        }

        $this->context->smarty->assign([
            'megaMenuItems' => $items,
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/croco_megamenu.tpl');
    }

    private function getMenuItems(): array
    {
        $idShop = (int) $this->context->shop->id;
        $db = Db::getInstance();

        $items = $db->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'croco_megamenu_item`
            WHERE `active` = 1 AND `id_shop` = ' . $idShop . '
            ORDER BY `position` ASC'
        );

        if (!$items) {
            return [];
        }

        foreach ($items as &$item) {
            $item['url'] = $this->resolveUrl($item['link_type'], $item['id_object'], $item['custom_url']);
            $item['columns'] = $this->getColumns((int) $item['id_item']);
            $item['has_panel'] = (bool) array_filter(
                $item['columns'],
                static fn (array $column): bool => !empty($column['blocks'])
            );
        }

        return $items;
    }

    private function getColumns(int $idItem): array
    {
        $columns = CrocoMegamenuItem::getColumns($idItem);

        foreach ($columns as &$column) {
            foreach ($column['blocks'] as &$block) {
                foreach ($block['links'] as &$link) {
                    $link['url'] = $this->resolveUrl($link['link_type'], $link['id_object'], $link['custom_url']);
                }
            }
        }

        return $columns;
    }

    private function resolveUrl(string $linkType, ?int $idObject, ?string $customUrl): string
    {
        if (!$idObject && $linkType !== 'custom') {
            return '#';
        }

        return match ($linkType) {
            'category' => $this->context->link->getCategoryLink($idObject),
            'cms' => $this->context->link->getCMSLink($idObject),
            'product' => $this->context->link->getProductLink($idObject),
            default => $customUrl ?: '#',
        };
    }

}
