<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminAboutContentController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = Module::getInstanceByName('aboutcontent');

        parent::__construct();

        $this->bootstrap = true;
    }

    public function initContent()
    {
        $this->content = $this->module->getContent();

        parent::initContent();
    }
}
