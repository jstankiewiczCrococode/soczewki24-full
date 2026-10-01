<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class BrandSlider extends Module
{
    public function __construct()
    {
        $this->name = 'brandslider';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'Soczewki24';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans(
            'Slider marek',
            [],
            'Modules.Brandslider.Admin'
        );

        $this->description = $this->trans(
            'Zarządza markami wyświetlanymi w sliderze.',
            [],
            'Modules.Brandslider.Admin'
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

        return $this->installTab()
    && $this->registerHook('displayBrandSlider')
    && $this->registerHook('displayHeader');
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
        $tab->class_name = 'AdminBrandSlider';
        $tab->name = [];

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Slider marek';
        }

        $tab->id_parent = (int) Tab::getIdFromClassName(
            'AdminParentModulesSf'
        );

        $tab->module = $this->name;

        return $tab->add();
    }

public function hookDisplayHeader($params)
{
    file_put_contents('/tmp/brandslider-hook.txt', date('c') . PHP_EOL, FILE_APPEND);

    $this->context->controller->registerJavascript(
        'module-brandslider',
        'modules/' . $this->name . '/views/js/brandslider.js',
        [
            'position' => 'bottom',
            'priority' => 150,
        ]
    );
}

    public function hookDisplayBrandSlider($params)
    {
        $manufacturers = Manufacturer::getManufacturers(
            $this->context->language->id,
            true
        );

        $this->context->smarty->assign([
            'brandslider_manufacturers' => $manufacturers,
        ]);

        return $this->display(
            __FILE__,
            'views/templates/hook/brandslider.tpl'
        );
    }
}