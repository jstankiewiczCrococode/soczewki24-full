<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class SoczewkiImporter extends Module
{
    public function __construct()
    {
        $this->name = 'soczewkiimporter';
        $this->tab = 'administration';
        $this->version = '0.3.1';
        $this->author = 'Soczewki24';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Soczewki24 Importer';
        $this->description = 'Bezpieczny importer feedu XML partiami.';
    }

    public function install()
    {
        return parent::install()
            && $this->installTab();
    }

    private function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminSoczewkiImporter';
        $tab->module = $this->name;
        $tab->id_parent = (int)Tab::getIdFromClassName('DEFAULT');
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[(int)$lang['id_lang']] = 'Soczewki24 Importer';
        }
        return $tab->add();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }
}
