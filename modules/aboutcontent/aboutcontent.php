<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AboutContent extends Module
{
    public $tabs = [
        [
            'name' => 'Sekcja Opisowa Strona Główna',
            'class_name' => 'AdminAboutContent',
            'parent_class_name' => 'AdminParentModulesSf',
            'visible' => true,
            'icon' => 'info',
        ],
    ];

    public function __construct()
    {
        $this->name = 'aboutcontent';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Soczewki24';
        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => '9.99.99',
        ];

        parent::__construct();

        $this->displayName = $this->trans(
            'Sekcja About',
            [],
            'Modules.Aboutcontent.Admin'
        );

        $this->description = $this->trans(
            'Edytowalna sekcja About.',
            [],
            'Modules.Aboutcontent.Admin'
        );
    }


        public function install()
        {
            return parent::install()
                && $this->registerHook('displayHeader');
        }


        public function hookDisplayHeader($params)
{
    $this->context->controller->registerStylesheet(
        'module-aboutcontent',
        'modules/' . $this->name . '/views/css/aboutcontent.css',
        [
            'media' => 'all',
            'priority' => 150,
        ]
    );
}



    public function uninstall()
    {
        Configuration::deleteByName('ABOUT_TITLE');
        Configuration::deleteByName('ABOUT_DESCRIPTION');
        Configuration::deleteByName('ABOUT_LEFT_TITLE');
        Configuration::deleteByName('ABOUT_LEFT_TEXT');
        Configuration::deleteByName('ABOUT_RIGHT_TITLE');
        Configuration::deleteByName('ABOUT_RIGHT_TEXT');
        Configuration::deleteByName('ABOUT_IMAGE');
        Configuration::deleteByName('ABOUT_IMAGE_ALT');

        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitAboutContent')) {
            $output .= $this->processSave();
        }

        return $output . $this->renderForm();
    }

    protected function processSave()
    {
        $languages = $this->context->controller->getLanguages();

        $title = [];
        $description = [];
        $leftTitle = [];
        $leftText = [];
        $rightTitle = [];
        $rightText = [];
        $imageAlt = [];

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];

            $title[$idLang] = Tools::getValue(
                'ABOUT_TITLE_' . $idLang
            );

            $description[$idLang] = Tools::getValue(
                'ABOUT_DESCRIPTION_' . $idLang
            );

            $leftTitle[$idLang] = Tools::getValue(
                'ABOUT_LEFT_TITLE_' . $idLang
            );

            $leftText[$idLang] = Tools::getValue(
                'ABOUT_LEFT_TEXT_' . $idLang
            );

            $rightTitle[$idLang] = Tools::getValue(
                'ABOUT_RIGHT_TITLE_' . $idLang
            );

            $rightText[$idLang] = Tools::getValue(
                'ABOUT_RIGHT_TEXT_' . $idLang
            );

            $imageAlt[$idLang] = Tools::getValue(
                'ABOUT_IMAGE_ALT_' . $idLang
            );
        }

        Configuration::updateValue('ABOUT_TITLE', $title);
        Configuration::updateValue('ABOUT_DESCRIPTION', $description, true);
        Configuration::updateValue('ABOUT_LEFT_TITLE', $leftTitle);
        Configuration::updateValue('ABOUT_LEFT_TEXT', $leftText, true);
        Configuration::updateValue('ABOUT_RIGHT_TITLE', $rightTitle);
        Configuration::updateValue('ABOUT_RIGHT_TEXT', $rightText, true);
        Configuration::updateValue('ABOUT_IMAGE_ALT', $imageAlt);

        if (
            isset($_FILES['ABOUT_IMAGE'])
            && !empty($_FILES['ABOUT_IMAGE']['name'])
        ) {
            $file = $_FILES['ABOUT_IMAGE'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                return $this->displayError(
                    'Nie udało się przesłać obrazka.'
                );
            }

            $extension = strtolower(
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );

            $allowed = [
                'jpg',
                'jpeg',
                'png',
                'webp',
            ];

            if (!in_array($extension, $allowed, true)) {
                return $this->displayError(
                    'Dozwolone formaty obrazka: JPG, JPEG, PNG, WEBP.'
                );
            }

            $uploadDir = _PS_MODULE_DIR_
                . $this->name
                . '/views/img/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileName = 'about.' . $extension;

            if (!move_uploaded_file(
                $file['tmp_name'],
                $uploadDir . $fileName
            )) {
                return $this->displayError(
                    'Nie udało się zapisać obrazka.'
                );
            }

            Configuration::updateValue(
                'ABOUT_IMAGE',
                $fileName
            );
        }

        return $this->displayConfirmation(
            'Sekcja About została zapisana.'
        );
    }

    protected function renderForm()
    {
        $languages = $this->context->controller->getLanguages();

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite(
            'AdminAboutContent'
        );
        $helper->currentIndex = AdminController::$currentIndex;

        $helper->show_toolbar = false;
        $helper->submit_action = 'submitAboutContent';
        $helper->default_form_language = (int) Configuration::get(
            'PS_LANG_DEFAULT'
        );
        $helper->allow_employee_form_lang = true;
        $helper->languages = $languages;

        $title = [];
        $description = [];
        $leftTitle = [];
        $leftText = [];
        $rightTitle = [];
        $rightText = [];
        $imageAlt = [];

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];

            $title[$idLang] = Configuration::get(
                'ABOUT_TITLE',
                $idLang
            );

            $description[$idLang] = Configuration::get(
                'ABOUT_DESCRIPTION',
                $idLang
            );

            $leftTitle[$idLang] = Configuration::get(
                'ABOUT_LEFT_TITLE',
                $idLang
            );

            $leftText[$idLang] = Configuration::get(
                'ABOUT_LEFT_TEXT',
                $idLang
            );

            $rightTitle[$idLang] = Configuration::get(
                'ABOUT_RIGHT_TITLE',
                $idLang
            );

            $rightText[$idLang] = Configuration::get(
                'ABOUT_RIGHT_TEXT',
                $idLang
            );

            $imageAlt[$idLang] = Configuration::get(
                'ABOUT_IMAGE_ALT',
                $idLang
            );
        }

        $image = Configuration::get('ABOUT_IMAGE');

        $imageDescription = 'Brak obrazka';

        if ($image) {
            $imageUrl = $this->_path . 'views/img/' . $image;

            $imageDescription =
                '<img src="' .
                htmlspecialchars(
                    $imageUrl,
                    ENT_QUOTES,
                    'UTF-8'
                ) .
                '" style="max-width:300px;height:auto;">';
        }

        $helper->fields_value = [
            'ABOUT_TITLE' => $title,
            'ABOUT_DESCRIPTION' => $description,
            'ABOUT_LEFT_TITLE' => $leftTitle,
            'ABOUT_LEFT_TEXT' => $leftText,
            'ABOUT_RIGHT_TITLE' => $rightTitle,
            'ABOUT_RIGHT_TEXT' => $rightText,
            'ABOUT_IMAGE_ALT' => $imageAlt,
        ];

        $fields = [
            'form' => [
                'legend' => [
                    'title' => 'Sekcja About',
                    'icon' => 'icon-info',
                ],

                'input' => [
                    [
                        'type' => 'text',
                        'label' => 'Tytuł',
                        'name' => 'ABOUT_TITLE',
                        'lang' => true,
                    ],

                    [
                        'type' => 'textarea',
                        'label' => 'Opis',
                        'name' => 'ABOUT_DESCRIPTION',
                        'lang' => true,
                        'autoload_rte' => true,
                        'rows' => 8,
                    ],

                    [
                        'type' => 'text',
                        'label' => 'Tytuł lewej kolumny',
                        'name' => 'ABOUT_LEFT_TITLE',
                        'lang' => true,
                    ],

                    [
                        'type' => 'textarea',
                        'label' => 'Tekst lewej kolumny',
                        'name' => 'ABOUT_LEFT_TEXT',
                        'lang' => true,
                        'autoload_rte' => true,
                        'rows' => 8,
                    ],

                    [
                        'type' => 'text',
                        'label' => 'Tytuł prawej kolumny',
                        'name' => 'ABOUT_RIGHT_TITLE',
                        'lang' => true,
                    ],

                    [
                        'type' => 'textarea',
                        'label' => 'Tekst prawej kolumny',
                        'name' => 'ABOUT_RIGHT_TEXT',
                        'lang' => true,
                        'autoload_rte' => true,
                        'rows' => 8,
                    ],

                    [
                        'type' => 'file',
                        'label' => 'Obrazek',
                        'name' => 'ABOUT_IMAGE',
                        'desc' => $imageDescription,
                    ],

                    [
                        'type' => 'text',
                        'label' => 'ALT obrazka',
                        'name' => 'ABOUT_IMAGE_ALT',
                        'lang' => true,
                    ],
                ],

                'submit' => [
                    'title' => 'Zapisz',
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        return $helper->generateForm([$fields]);
    }


public function renderAbout()
{
    $langId = (int) $this->context->language->id;

    $title = Configuration::get(
        'ABOUT_TITLE',
        $langId
    );

    $description = Configuration::get(
        'ABOUT_DESCRIPTION',
        $langId
    );

    $leftTitle = Configuration::get(
        'ABOUT_LEFT_TITLE',
        $langId
    );

    $leftText = Configuration::get(
        'ABOUT_LEFT_TEXT',
        $langId
    );

    $rightTitle = Configuration::get(
        'ABOUT_RIGHT_TITLE',
        $langId
    );

    $rightText = Configuration::get(
        'ABOUT_RIGHT_TEXT',
        $langId
    );

    $image = Configuration::get('ABOUT_IMAGE');

    $imageAlt = Configuration::get(
        'ABOUT_IMAGE_ALT',
        $langId
    );

    $imageUrl = '';

    if ($image) {
        $imageUrl = $this->_path . 'views/img/' . $image;
    }

    $this->context->smarty->assign([
        'title' => $title,
        'description' => $description,
        'left_title' => $leftTitle,
        'left_text' => $leftText,
        'right_title' => $rightTitle,
        'right_text' => $rightText,
        'image' => $imageUrl,
        'image_alt' => $imageAlt,
    ]);

    return $this->display(
        __FILE__,
        'views/templates/hook/displayHome.tpl'
    );
}



    
}
