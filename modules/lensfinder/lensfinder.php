<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class LensFinder extends Module
{
    public function __construct()
    {
        $this->name = 'lensfinder';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Crococode';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Wyszukiwarka soczewek');
        $this->description = $this->l(
            'Wyszukiwarka soczewek według mocy, krzywizny, trybu wymiany i typu korekcji.'
        );
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayLensFinder');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookHeader($params)
    {
        $this->context->controller->registerStylesheet(
            'lensfinder-css',
            'modules/' . $this->name . '/views/css/lensfinder.css',
            [
                'media' => 'all',
                'priority' => 150,
            ]
        );
    }

    public function hookDisplayLensFinder($params)
    {
        $config = $this->loadConfig();

        $this->context->smarty->assign([
            'lensfinder_config' => $config,
        ]);

        return $this->display(
            __FILE__,
            'views/templates/hook/lensfinder.tpl'
        );
    }

    private function loadConfig()
    {
        $file = __DIR__ . '/soczewki-filtry-wartosci.json';

        if (!file_exists($file)) {
            return [];
        }

        $json = file_get_contents($file);
        $config = json_decode($json, true);

        if (!is_array($config)) {
            return [];
        }

        return $config;
    }
}