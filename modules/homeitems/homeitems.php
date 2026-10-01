<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class HomeItems extends Module
{
    public function __construct()
    {
        $this->name = 'homeitems';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Soczewki24';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans(
            'Linki kategorii',
            [],
            'Modules.Homeitems.Admin'
        );

        $this->description = $this->trans(
            'Manages items displayed on the homepage.',
            [],
            'Modules.Homeitems.Admin'
        );
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!require __DIR__ . '/sql/install.php') {
            return false;
        }

        return $this->installTab();
    }

    public function uninstall()
    {
        $result = require __DIR__ . '/sql/uninstall.php';

        if (!$result) {
            return false;
        }

        return $this->uninstallTab() && parent::uninstall();
    }

    public function installTab()
    {
        $tab = new Tab();

        $tab->active = 1;
        $tab->class_name = 'AdminHomeItems';
        $tab->name = [];

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Linki kategorii'; 
        }

        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentModulesSf');
        $tab->module = $this->name;

        return $tab->add();
    }

    public function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminHomeItems');

        if (!$idTab) {
            return true;
        }

        $tab = new Tab($idTab);

        return $tab->delete();
    }

    public function hookDisplayHomeItems($params)
    {
        $items = Db::getInstance()->executeS(
            'SELECT *
             FROM `' . _DB_PREFIX_ . 'homeitems`
             WHERE active = 1
             ORDER BY position ASC, id_homeitem ASC'
        );

        $this->context->smarty->assign([
            'homeitems' => $items,
        ]);

        return $this->display(
            __FILE__,
            'views/templates/hook/homeitems.tpl'
        );
    }
}