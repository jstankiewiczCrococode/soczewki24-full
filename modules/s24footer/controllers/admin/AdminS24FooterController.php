<?php

class AdminS24FooterController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = Module::getInstanceByName('s24footer');

        parent::__construct();

        $this->bootstrap = true;
    }

    public function initContent()
    {
        parent::initContent();

        $this->content = $this->module->getContent();

        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    }
}