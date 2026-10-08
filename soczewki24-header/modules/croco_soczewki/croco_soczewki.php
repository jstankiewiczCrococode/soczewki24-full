<?php
/**
 * Modul projektowy Soczewki24 - CrocoCode.
 *
 * Miejsce na cala logike sklepu ktora nie jest wygladem:
 * nowe hooki, integracje, dodatkowe tabele, ustawienia w BO.
 *
 * install() i pliki w upgrade/ pelnia role migracji bazy - to jedyne
 * miejsce gdzie zmiany strukturalne w DB sa wersjonowane w gicie.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Croco_Soczewki extends Module
{
    private const BANNER_COOKIE = 'croco_banner_dismissed';

    private const BANNER_LANG_KEYS = ['CROCO_BANNER_TEXT', 'CROCO_BANNER_LINK_LABEL', 'CROCO_BANNER_LINK_URL'];

    public function __construct()
    {
        $this->name = 'croco_soczewki';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'CrocoCode';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '9.1.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Soczewki24 - modul projektowy', [], 'Modules.Crocosoczewki.Admin');
        $this->description = $this->trans('Logika projektowa sklepu Soczewki24.', [], 'Modules.Crocosoczewki.Admin');
        $this->confirmUninstall = $this->trans('Na pewno? Usuniete zostana ustawienia modulu.', [], 'Modules.Crocosoczewki.Admin');
    }

    /**
     * Odpowiednik migracji "up". Wszystko co strukturalne ma byc tutaj,
     * zeby dalo sie odtworzyc sklep od zera bez dumpa produkcji.
     */
    public function install(): bool
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayTop')
            && $this->registerHook('displayBanner')
            && $this->installDatabase()
            && $this->installConfiguration();
    }

    public function uninstall(): bool
    {
        // Celowo NIE kasujemy tabel - dane klienta zostaja.
        // Kasowanie tylko przez swiadoma migracje.
        return parent::uninstall() && $this->uninstallConfiguration();
    }

    private function installDatabase(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'croco_soczewki_log` (
            `id_log` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
            `context` VARCHAR(64) NOT NULL,
            `payload` TEXT NULL,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_log`),
            KEY `idx_context` (`context`),
            KEY `idx_date_add` (`date_add`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return (bool) Db::getInstance()->execute($sql);
    }

    private function installConfiguration(): bool
    {
        return Configuration::updateValue('CROCO_SOCZEWKI_ENABLED', 1)
            && Configuration::updateValue('CROCO_BANNER_ENABLED', 0);
    }

    private function uninstallConfiguration(): bool
    {
        $result = Configuration::deleteByName('CROCO_SOCZEWKI_ENABLED')
            && Configuration::deleteByName('CROCO_BANNER_ENABLED');

        foreach (self::BANNER_LANG_KEYS as $key) {
            $result = Configuration::deleteByName($key) && $result;
        }

        return $result;
    }

    /**
     * Assety motywu buduje webpack, wiec tutaj ladujemy tylko to,
     * co jest scisle zwiazane z logika tego modulu.
     */
    public function hookActionFrontControllerSetMedia(): void
    {
        if (!Configuration::get('CROCO_SOCZEWKI_ENABLED')) {
            return;
        }

        // $this->context->controller->registerJavascript(
        //     'croco-soczewki',
        //     'modules/' . $this->name . '/views/js/front.js',
        //     ['position' => 'bottom', 'priority' => 150]
        // );
    }

    public function hookDisplayHeader(): string
    {
        return '';
    }

    /**
     * Przycisk "Zamow ponownie" w naglowku. Logika, a nie wyglad, bo zalezy
     * od tego czy klient jest zalogowany - dlatego siedzi w module, nie w motywie.
     */
    public function hookDisplayTop(): string
    {
        $historyUrl = $this->context->link->getPageLink('history');
        $isLogged = $this->context->customer && $this->context->customer->isLogged();

        $this->context->smarty->assign([
            'reorderUrl' => $isLogged
                ? $historyUrl
                : $this->context->link->getPageLink('authentication', null, null, ['back' => $historyUrl]),
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/hook/reorder-button.tpl');
    }

    public function hookDisplayBanner(): string
    {
        $idLang = (int) $this->context->language->id;
        $text = (string) Configuration::get('CROCO_BANNER_TEXT', $idLang);

        if (!Configuration::get('CROCO_BANNER_ENABLED') || $text === '') {
            return '';
        }

        $label = (string) Configuration::get('CROCO_BANNER_LINK_LABEL', $idLang);
        $url = (string) Configuration::get('CROCO_BANNER_LINK_URL', $idLang);
        $id = md5($text . $label . $url);

        // Surowe $_COOKIE, nie $this->context->cookie: to drugie jest
        // szyfrowane przez PrestaShop i JS nie ma jak go zapisac.
        if (($_COOKIE[self::BANNER_COOKIE] ?? '') === $id) {
            return '';
        }

        $this->context->smarty->assign([
            'bannerId' => $id,
            'bannerCookie' => self::BANNER_COOKIE,
            'bannerText' => $text,
            'bannerLabel' => $label,
            'bannerUrl' => $url,
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/hook/announcement-bar.tpl');
    }

    public function getContent(): string
    {
        return $this->postProcess() . $this->renderBannerForm();
    }

    private function postProcess(): string
    {
        if (!Tools::isSubmit('submitCrocoBanner')) {
            return '';
        }

        $values = [];

        foreach (Language::getLanguages(false) as $lang) {
            foreach (self::BANNER_LANG_KEYS as $key) {
                $values[$key][(int) $lang['id_lang']] = trim((string) Tools::getValue($key . '_' . $lang['id_lang']));
            }

            // Bez tego w href mogloby wyladowac javascript:..., a Smarty
            // escapuje tylko znaki HTML, nie schemat.
            if (!preg_match('#^(https?://|/(?!/)|\#|$)#i', $values['CROCO_BANNER_LINK_URL'][(int) $lang['id_lang']])) {
                return $this->displayError($this->trans('Adres linku musi zaczynac sie od http://, https://, / albo #.', [], 'Modules.Crocosoczewki.Admin'));
            }
        }

        Configuration::updateValue('CROCO_BANNER_ENABLED', (int) Tools::getValue('CROCO_BANNER_ENABLED'));

        foreach (self::BANNER_LANG_KEYS as $key) {
            Configuration::updateValue($key, $values[$key]);
        }

        return $this->displayConfirmation($this->trans('Zapisano ustawienia.', [], 'Admin.Notifications.Success'));
    }

    private function renderBannerForm(): string
    {
        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Pasek komunikatu nad headerem', [], 'Modules.Crocosoczewki.Admin'),
                    'icon' => 'icon-bullhorn',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Wlaczony', [], 'Modules.Crocosoczewki.Admin'),
                        'name' => 'CROCO_BANNER_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'banner_on', 'value' => 1, 'label' => $this->trans('Tak', [], 'Admin.Global')],
                            ['id' => 'banner_off', 'value' => 0, 'label' => $this->trans('Nie', [], 'Admin.Global')],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Tekst', [], 'Modules.Crocosoczewki.Admin'),
                        'name' => 'CROCO_BANNER_TEXT',
                        'desc' => $this->trans('Puste pole ukrywa pasek. Po zmianie tekstu pasek wraca do osob, ktore zamknely poprzedni.', [], 'Modules.Crocosoczewki.Admin'),
                    ],
                    [
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Tekst linku', [], 'Modules.Crocosoczewki.Admin'),
                        'name' => 'CROCO_BANNER_LINK_LABEL',
                    ],
                    [
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Adres linku', [], 'Modules.Crocosoczewki.Admin'),
                        'name' => 'CROCO_BANNER_LINK_URL',
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Zapisz', [], 'Admin.Actions'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCrocoBanner';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getBannerFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fieldsForm]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getBannerFieldsValues(): array
    {
        $fields = [
            'CROCO_BANNER_ENABLED' => Tools::getValue('CROCO_BANNER_ENABLED', Configuration::get('CROCO_BANNER_ENABLED')),
        ];

        foreach (Language::getLanguages(false) as $lang) {
            foreach (self::BANNER_LANG_KEYS as $key) {
                $fields[$key][(int) $lang['id_lang']] = Tools::getValue(
                    $key . '_' . $lang['id_lang'],
                    Configuration::get($key, (int) $lang['id_lang'])
                );
            }
        }

        return $fields;
    }
}
