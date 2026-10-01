<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Blog extends Module
{
    public function __construct()
    {
        $this->name = 'blog';
        $this->version = '1.0.0';
        $this->author = 'Soczewki24';
        $this->tab = 'front_office_features';
        $this->need_instance = 0;

        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => _PS_VERSION_,
        ];

        parent::__construct();

        $this->displayName = 'Blog i poradniki';
        $this->description = 'Sekcja Blog i poradniki na stronie głównej.';
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayBlogSection')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function hookDisplayBlogSection($params)
    {
        return $this->display(__FILE__, 'blog-section.tpl');
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'module-blog',
            'modules/' . $this->name . '/views/css/blog.css',
            [
                'media' => 'all',
                'priority' => 150,
            ]
        );
    }
}