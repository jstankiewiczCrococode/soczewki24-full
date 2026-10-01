<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class PsCustomHomeSlider extends Module
{
    public function __construct()
    {
        $this->name = 'pscustomhomeslider';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Soczewki24';
        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => _PS_VERSION_,
        ];

        parent::__construct();

        $this->displayName = $this->trans(
            'Slider strony głównej',
            [],
            'Modules.Pscustomhomeslider.Admin'
        );

        $this->description = $this->trans(
            'Zarządzanie sliderem strony głównej sklepu.',
            [],
            'Modules.Pscustomhomeslider.Admin'
        );

        $this->tabs = [
            [
                'route_name' => 'admin_pscustomhomeslider_index',
                'class_name' => 'AdminPsCustomHomeSlider',
                'visible' => true,
                'name' => $this->getTabNames(),
                'parent_class_name' => 'AdminParentThemes',
                'wording' => 'Slider strony głównej',
                'wording_domain' => 'Modules.Pscustomhomeslider.Admin',
            ],
        ];
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHome')
            && $this->installDatabase();
    }

    public function uninstall()
    {
        return $this->uninstallDatabase()
            && parent::uninstall();
    }

public function hookDisplayHome($params)
{
    $repository = new \PrestaShop\Module\PsCustomHomeSlider\Repository\HomeSliderRepository();

    $slides = $repository->findActive();

    if (!$slides) {
        return '';
    }

    $this->context->smarty->assign([
        'slides' => $slides,
        'image_base_url' => _PS_IMG_BASE_URL_,
    ]);

    return $this->display(
        __FILE__,
        'views/templates/hook/home-slider.tpl'
    );
}

    private function getTabNames()
    {
        $names = [];

        foreach (Language::getLanguages(true) as $language) {
            $names[$language['id_lang']] = $this->trans(
                'Slider strony głównej',
                [],
                'Modules.Pscustomhomeslider.Admin',
                $language['locale']
            );
        }

        return $names;
    }

    private function installDatabase()
    {
        require_once __DIR__ . '/sql/install.php';

        return installPscustomHomeSliderDatabase();
    }

    private function uninstallDatabase()
    {
        require_once __DIR__ . '/sql/uninstall.php';

        return uninstallPscustomHomeSliderDatabase();
    }
}