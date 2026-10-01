<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class S24Footer extends Module
{
public function __construct()
{
    $this->name = 's24footer';
    $this->tab = 'front_office_features';
    $this->version = '1.0.0';
    $this->author = 'Soczewki24';
    $this->need_instance = 0;

    $this->tabs = [
        [
            'class_name' => 'AdminS24Footer',
            'visible' => true,
            'name' => 'Stopka Soczewki24',
            'parent_class_name' => 'AdminParentModulesSf',
        ],
    ];

    parent::__construct();

    $this->displayName = $this->l('Stopka Soczewki24');
    $this->description = $this->l(
        'Konfigurowalna stopka sklepu Soczewki24.'
    );
}


public function install()
{
    return parent::install()
        && $this->installDatabase()
        && $this->registerHook('actionFrontControllerSetVariables')
        && $this->registerHook('displayFooter')
        && $this->installAdminTab();
}

public function uninstall()
{
    return $this->uninstallAdminTab()
        && $this->uninstallDatabase()
        && parent::uninstall();
}


    
private function installAdminTab()
{
    $tabClass = 'AdminS24Footer';

    // Jeżeli zakładka już istnieje, nic nie robimy.
    if ((int) Tab::getIdFromClassName($tabClass) > 0) {
        return true;
    }

    $parentId = (int) Tab::getIdFromClassName(
        'AdminParentModulesSf'
    );

    if (!$parentId) {
        return false;
    }

    $tab = new Tab();

    $tab->active = 1;
    $tab->class_name = $tabClass;
    $tab->module = $this->name;
    $tab->id_parent = $parentId;
    $tab->position = 0;

    foreach (Language::getLanguages(true) as $language) {
        $tab->name[(int) $language['id_lang']] = $this->l(
            'Stopka Soczewki24'
        );
    }

    return $tab->add();
}

private function uninstallAdminTab()
{
    $tabId = (int) Tab::getIdFromClassName(
        'AdminS24Footer'
    );

    if (!$tabId) {
        return true;
    }

    $tab = new Tab($tabId);

    return $tab->delete();
}



    private function installDatabase()
    {
        $sql = [];

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 's24footer_column` (
                `id_column` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `position` INT NOT NULL DEFAULT 0,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_column`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;
        ';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 's24footer_item` (
                `id_item` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_column` INT UNSIGNED NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `url` VARCHAR(2048) DEFAULT NULL,
                `type` VARCHAR(20) NOT NULL DEFAULT \'link\',
                `position` INT NOT NULL DEFAULT 0,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_item`),
                KEY `idx_column` (`id_column`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;
        ';


        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 's24footer_social` (
                `id_social` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `type` VARCHAR(50) NOT NULL,
                `url` VARCHAR(2048) DEFAULT NULL,
                `icon` VARCHAR(255) DEFAULT NULL,
                `position` INT NOT NULL DEFAULT 0,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_social`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;
        ';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function uninstallDatabase()
    {
        $sql = [
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 's24footer_item`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 's24footer_column`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 's24footer_social`',
        ];

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /*
     * =========================================================
     * ADMIN
     * =========================================================
     */

    public function getContent()
    {
        $this->ensureAdminTab();

        if (!$this->isRegisteredInHook('displayFooter')) {
            $this->registerHook('displayFooter');
        }

        if (!$this->isRegisteredInHook('actionFrontControllerSetVariables')) {
            $this->registerHook('actionFrontControllerSetVariables');
        }

        $this->processAdminActions();

        $output = '';

        $output .= '
            <div class="panel">
                <h3>
                    <i class="icon icon-cog"></i>
                    ' . $this->l('Stopka Soczewki24') . '
                </h3>

                <p>
                    ' . $this->l(
                        'Tutaj możesz zarządzać kolumnami, linkami, newsletterem oraz mediami społecznościowymi w stopce sklepu.'
                    ) . '
                </p>
            </div>
        ';

        $output .= $this->renderColumnForm();
        $output .= $this->renderColumns();

        $output .= $this->renderNewsletterForm();

        $output .= $this->renderSocialForm();
        $output .= $this->renderSocials();

        return $output;
    }

    
        private function ensureAdminTab()
        {
            $tabClass = 'AdminS24Footer';

            if ((int) Tab::getIdFromClassName($tabClass) > 0) {
                return true;
            }

            return $this->installAdminTab();
        }



    private function processAdminActions()
    {
        $action = Tools::getValue('s24_action');

        if (!$action) {
            return;
        }

        switch ($action) {
            case 'add_column':
                $this->addColumn();
                break;

            case 'update_column':
                $this->updateColumn();
                break;

            case 'delete_column':
                $this->deleteColumn();
                break;

            case 'add_item':
                $this->addItem();
                break;

            case 'update_item':
                $this->updateItem();
                break;

            case 'delete_item':
                $this->deleteItem();
                break;

            case 'add_social':
                $this->addSocial();
                break;

            case 'update_social':
                $this->updateSocial();
                break;

            case 'delete_social':
                $this->deleteSocial();
                break;

            case 'upload_newsletter_logo':
                $this->uploadNewsletterLogo();
                break;

            case 'delete_newsletter_logo':
                $this->deleteNewsletterLogo();
                break;
        }
    }

    /*
     * =========================================================
     * COLUMNS
     * =========================================================
     */

    private function addColumn()
    {
        $name = trim(
            (string) Tools::getValue('column_name')
        );

        if ($name === '') {
            return;
        }

        $maxPosition = (int) Db::getInstance()->getValue(
            'SELECT MAX(`position`)
             FROM `' . _DB_PREFIX_ . 's24footer_column`'
        );

        Db::getInstance()->insert(
            's24footer_column',
            [
                'name' => pSQL($name),
                'position' => $maxPosition + 1,
                'active' => 1,
            ]
        );
    }

    private function updateColumn()
    {
        $id = (int) Tools::getValue('id_column');

        $name = trim(
            (string) Tools::getValue('column_name')
        );

        if (!$id || $name === '') {
            return;
        }

        Db::getInstance()->update(
            's24footer_column',
            [
                'name' => pSQL($name),
                'active' => (int) Tools::getValue(
                    'column_active',
                    1
                ),
            ],
            '`id_column` = ' . $id
        );
    }

    private function deleteColumn()
    {
        $id = (int) Tools::getValue('id_column');

        if (!$id) {
            return;
        }

        Db::getInstance()->delete(
            's24footer_item',
            '`id_column` = ' . $id
        );

        Db::getInstance()->delete(
            's24footer_column',
            '`id_column` = ' . $id
        );
    }

    private function addItem()
    {
        $idColumn = (int) Tools::getValue(
            'item_column'
        );

        $label = trim(
            (string) Tools::getValue('item_label')
        );

        $url = trim(
            (string) Tools::getValue('item_url')
        );

        $type = trim(
                (string) Tools::getValue('item_type', 'link')
            );

            if (!in_array($type, ['link', 'tel', 'email'], true)) {
                $type = 'link';
            }


        if (!$idColumn || $label === '') {
            return;
        }

        $maxPosition = (int) Db::getInstance()->getValue(
            'SELECT MAX(`position`)
             FROM `' . _DB_PREFIX_ . 's24footer_item`
             WHERE `id_column` = ' . $idColumn
        );

        Db::getInstance()->insert(
            's24footer_item',
            [
                'id_column' => $idColumn,
                'label' => pSQL($label),
                'url' => pSQL($url),
                'type' => pSQL($type),
                'position' => $maxPosition + 1,
                'active' => 1,
            ]
        );
    }

    private function updateItem()
    {
        $id = (int) Tools::getValue('id_item');

        $label = trim(
            (string) Tools::getValue('item_label')
        );

        $url = trim(
            (string) Tools::getValue('item_url')
        );

        $type = trim(
            (string) Tools::getValue('item_type', 'link')
        );

        if (!in_array($type, ['link', 'tel', 'email'], true)) {
            $type = 'link';
        }

        if (!$id || $label === '') {
            return;
        }

        Db::getInstance()->update(
            's24footer_item',
            [
                'label' => pSQL($label),
                'url' => pSQL($url),
                'type' => pSQL($type),
                'active' => (int) Tools::getValue(
                    'item_active',
                    1
                ),
            ],

            '`id_item` = ' . $id
        );
    }

    private function deleteItem()
    {
        $id = (int) Tools::getValue('id_item');

        if (!$id) {
            return;
        }

        Db::getInstance()->delete(
            's24footer_item',
            '`id_item` = ' . $id
        );
    }

    /*
     * =========================================================
     * SOCIAL
     * =========================================================
     */

    private function uploadSocialIcon()
    {
        if (
            !isset($_FILES['social_icon']) ||
            empty($_FILES['social_icon']['name'])
        ) {
            return null;
        }

        $file = $_FILES['social_icon'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedExtensions = [
            'png',
            'jpg',
            'jpeg',
            'webp',
        ];

        $extension = Tools::strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $allowedExtensions, true)) {
            return null;
        }

        $directory =
            _PS_MODULE_DIR_ .
            $this->name .
            '/views/img/social/';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename =
            uniqid('social_', true) .
            '.' .
            $extension;

        $destination = $directory . $filename;

        if (!move_uploaded_file(
            $file['tmp_name'],
            $destination
        )) {
            return null;
        }

        return $this->_path .
            'views/img/social/' .
            $filename;
    }

    private function addSocial()
    {
        $type = trim(
            (string) Tools::getValue('social_type')
        );

        $url = trim(
            (string) Tools::getValue('social_url')
        );

        if ($type === '' || $url === '') {
            return;
        }

        $icon = $this->uploadSocialIcon();

        $maxPosition = (int) Db::getInstance()->getValue(
            'SELECT MAX(`position`)
             FROM `' . _DB_PREFIX_ . 's24footer_social`'
        );

        Db::getInstance()->insert(
            's24footer_social',
            [
                'type' => pSQL($type),
                'url' => pSQL($url),
                'icon' => $icon !== null
                    ? pSQL($icon)
                    : null,
                'position' => $maxPosition + 1,
                'active' => 1,
            ]
        );
    }

    private function updateSocial()
    {
        $id = (int) Tools::getValue('id_social');

        $type = trim(
            (string) Tools::getValue('social_type')
        );

        $url = trim(
            (string) Tools::getValue('social_url')
        );

        if (!$id || $type === '' || $url === '') {
            return;
        }

        $data = [
            'type' => pSQL($type),
            'url' => pSQL($url),
            'active' => (int) Tools::getValue(
                'social_active',
                1
            ),
        ];

        $icon = $this->uploadSocialIcon();

        if ($icon !== null) {
            $data['icon'] = pSQL($icon);
        }

        Db::getInstance()->update(
            's24footer_social',
            $data,
            '`id_social` = ' . $id
        );
    }

    private function deleteSocial()
    {
        $id = (int) Tools::getValue('id_social');

        if (!$id) {
            return;
        }

        Db::getInstance()->delete(
            's24footer_social',
            '`id_social` = ' . $id
        );
    }

    /*
     * =========================================================
     * NEWSLETTER LOGO
     * =========================================================
     */

    private function uploadNewsletterLogo()
    {
        if (
            !isset($_FILES['newsletter_logo']) ||
            empty($_FILES['newsletter_logo']['name'])
        ) {
            return;
        }

        $file = $_FILES['newsletter_logo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $allowedExtensions = [
            'png',
            'jpg',
            'jpeg',
            'webp',
        ];

        $extension = Tools::strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $allowedExtensions, true)) {
            return;
        }

        $directory =
            _PS_MODULE_DIR_ .
            $this->name .
            '/views/img/newsletter/';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $files = scandir($directory);

        foreach ($files as $oldFile) {
            if (
                $oldFile === '.' ||
                $oldFile === '..'
            ) {
                continue;
            }

            $oldExtension = Tools::strtolower(
                pathinfo($oldFile, PATHINFO_EXTENSION)
            );

            if (in_array(
                $oldExtension,
                $allowedExtensions,
                true
            )) {
                @unlink($directory . $oldFile);
            }
        }

        $filename =
            uniqid('newsletter_', true) .
            '.' .
            $extension;

        $destination = $directory . $filename;

        move_uploaded_file(
            $file['tmp_name'],
            $destination
        );
    }

    private function deleteNewsletterLogo()
    {
        $directory =
            _PS_MODULE_DIR_ .
            $this->name .
            '/views/img/newsletter/';

        if (!is_dir($directory)) {
            return;
        }

        $allowedExtensions = [
            'png',
            'jpg',
            'jpeg',
            'webp',
        ];

        $files = scandir($directory);

        foreach ($files as $file) {
            if (
                $file === '.' ||
                $file === '..'
            ) {
                continue;
            }

            $extension = Tools::strtolower(
                pathinfo($file, PATHINFO_EXTENSION)
            );

            if (in_array(
                $extension,
                $allowedExtensions,
                true
            )) {
                @unlink($directory . $file);
            }
        }
    }

    /*
     * =========================================================
     * ADMIN FORMS
     * =========================================================
     */

    private function renderColumnForm()
    {
        return '
            <div class="panel">

                <h3>
                    <i class="icon icon-columns"></i>
                    ' . $this->l('Dodaj kolumnę') . '
                </h3>

                <form method="post">

                    <input
                        type="hidden"
                        name="s24_action"
                        value="add_column"
                    >

                    <div class="form-group">

                        <label class="control-label">
                            ' . $this->l('Nazwa kolumny') . '
                        </label>

                        <input
                            type="text"
                            name="column_name"
                            class="form-control"
                            placeholder="np. Informacje"
                            required
                        >

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="icon icon-plus"></i>
                        ' . $this->l('Dodaj kolumnę') . '
                    </button>

                </form>

            </div>
        ';
    }

    private function renderColumns()
    {
        $columns = Db::getInstance()->executeS(
            'SELECT *
             FROM `' . _DB_PREFIX_ . 's24footer_column`
             ORDER BY `position` ASC'
        );

        $html = '
            <div class="panel">

                <h3>
                    <i class="icon icon-list"></i>
                    ' . $this->l('Kolumny stopki') . '
                </h3>
        ';

        if (!$columns) {
            $html .= '
                <div class="alert alert-info">
                    ' . $this->l(
                        'Nie ma jeszcze żadnych kolumn.'
                    ) . '
                </div>
            ';
        }

        foreach ($columns as $column) {
            $idColumn = (int) $column['id_column'];

            $html .= '
                <div
                    style="
                        border:1px solid #ddd;
                        padding:20px;
                        margin-bottom:20px;
                        background:#fff;
                    "
                >

                    <form method="post">

                        <input
                            type="hidden"
                            name="s24_action"
                            value="update_column"
                        >

                        <input
                            type="hidden"
                            name="id_column"
                            value="' . $idColumn . '"
                        >

                        <div class="row">

                            <div class="col-lg-5">

                                <label>
                                    <strong>
                                        ' . $this->l(
                                            'Nazwa kolumny'
                                        ) . '
                                    </strong>
                                </label>

                                <input
                                    type="text"
                                    name="column_name"
                                    class="form-control"
                                    value="' . htmlspecialchars(
                                        $column['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) . '"
                                >

                            </div>

                            <div class="col-lg-3">

                                <label>
                                    <strong>
                                        ' . $this->l(
                                            'Aktywna'
                                        ) . '
                                    </strong>
                                </label>

                                <select
                                    name="column_active"
                                    class="form-control"
                                >

                                    <option
                                        value="1"
                                        ' .
                                        (
                                            (int) $column['active'] === 1
                                                ? 'selected'
                                                : ''
                                        ) . '
                                    >
                                        ' . $this->l('Tak') . '
                                    </option>

                                    <option
                                        value="0"
                                        ' .
                                        (
                                            (int) $column['active'] === 0
                                                ? 'selected'
                                                : ''
                                        ) . '
                                    >
                                        ' . $this->l('Nie') . '
                                    </option>

                                </select>

                            </div>

                            <div
                                class="col-lg-4"
                                style="padding-top:25px;"
                            >

                                <button
                                    type="submit"
                                    class="btn btn-default"
                                >
                                    <i class="icon icon-save"></i>
                                    ' . $this->l('Zapisz') . '
                                </button>

                                <a
                                    href="?s24_action=delete_column&id_column=' .
                                    $idColumn . '"
                                    class="btn btn-danger"
                                    onclick="return confirm(\'' .
                                        addslashes(
                                            $this->l(
                                                'Czy na pewno usunąć tę kolumnę wraz z linkami?'
                                            )
                                        ) .
                                    '\');"
                                >
                                    <i class="icon icon-trash"></i>
                                    ' . $this->l('Usuń') . '
                                </a>

                            </div>

                        </div>

                    </form>
            ';

            $html .= $this->renderItems($idColumn);

            $html .= '
                </div>
            ';
        }

        $html .= '
            </div>
        ';

        return $html;
    }

    private function renderItems($idColumn)
    {
        $items = Db::getInstance()->executeS(
            'SELECT *
             FROM `' . _DB_PREFIX_ . 's24footer_item`
             WHERE `id_column` = ' . (int) $idColumn . '
             ORDER BY `position` ASC'
        );

        $html = '
            <div
                style="
                    margin-top:20px;
                    padding:15px;
                    background:#f8f8f8;
                "
            >

                <h4>
                    ' . $this->l('Linki w kolumnie') . '
                </h4>
        ';

        if ($items) {
            foreach ($items as $item) {
                $idItem = (int) $item['id_item'];

                $html .= '
                    <form
                        method="post"
                        style="
                            background:#fff;
                            padding:12px;
                            margin-bottom:10px;
                            border:1px solid #ddd;
                        "
                    >

                        <input
                            type="hidden"
                            name="s24_action"
                            value="update_item"
                        >

                        <input
                            type="hidden"
                            name="id_item"
                            value="' . $idItem . '"
                        >

                        <div class="row">

                            <div class="col-lg-3">

                                <input
                                    type="text"
                                    name="item_label"
                                    class="form-control"
                                    value="' . htmlspecialchars(
                                        $item['label'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) . '"
                                    placeholder="Nazwa linku"
                                >

                            </div>

                            <div class="col-lg-4">

                                <input
                                    type="text"
                                    name="item_url"
                                    class="form-control"
                                    value="' . htmlspecialchars(
                                        $item['url'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) . '"
                                    placeholder="https://..."
                                >

                            </div>

                            <div class="col-lg-2">

                                <select
                                    name="item_active"
                                    class="form-control"
                                >

                                    <option
                                        value="1"
                                        ' .
                                        (
                                            (int) $item['active'] === 1
                                                ? 'selected'
                                                : ''
                                        ) . '
                                    >
                                        ' . $this->l('Aktywny') . '
                                    </option>

                                    <option
                                        value="0"
                                        ' .
                                        (
                                            (int) $item['active'] === 0
                                                ? 'selected'
                                                : ''
                                        ) . '
                                    >
                                        ' . $this->l('Nieaktywny') . '
                                    </option>

                                </select>

                            </div>

                            <div class="col-lg-3">

                                <button
                                    type="submit"
                                    class="btn btn-default"
                                >
                                    <i class="icon icon-save"></i>
                                    ' . $this->l('Zapisz') . '
                                </button>

                                <a
                                    href="?s24_action=delete_item&id_item=' .
                                    $idItem . '"
                                    class="btn btn-danger"
                                >
                                    <i class="icon icon-trash"></i>
                                </a>

                            </div>

                        </div>

                    </form>
                ';
            }
        } else {
            $html .= '
                <p class="text-muted">
                    ' . $this->l(
                        'Brak linków w tej kolumnie.'
                    ) . '
                </p>
            ';
        }

        $html .= '
                <hr>

                <h5>
                    ' . $this->l('Dodaj link') . '
                </h5>

                <form method="post">

                    <input
                        type="hidden"
                        name="s24_action"
                        value="add_item"
                    >

                    <input
                        type="hidden"
                        name="item_column"
                        value="' . (int) $idColumn . '"
                    >

                    <div class="row">

                        <div class="col-lg-3">

                            <input
                                type="text"
                                name="item_label"
                                class="form-control"
                                placeholder="np. Kontakt"
                                required
                            >

                        </div>

                        <div class="col-lg-5">

                            <input
                                type="text"
                                name="item_url"
                                class="form-control"
                                placeholder="https://..."
                            >

                        </div>

                        <div class="col-lg-4">

                            <label>
                                <strong>
                                    ' . $this->l('Typ') . '
                                </strong>
                            </label>

                            <div>
                                <label style="margin-right:15px;">
                                    <input
                                        type="radio"
                                        name="item_type"
                                        value="link"
                                        checked
                                    >
                                    ' . $this->l('Link') . '
                                </label>

                                <label style="margin-right:15px;">
                                    <input
                                        type="radio"
                                        name="item_type"
                                        value="tel"
                                    >
                                    ' . $this->l('Telefon') . '
                                </label>

                                <label>
                                    <input
                                        type="radio"
                                        name="item_type"
                                        value="email"
                                    >
                                    ' . $this->l('E-mail') . '
                                </label>
                            </div>

                        </div>


                        <div class="col-lg-4">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="icon icon-plus"></i>
                                ' . $this->l('Dodaj link') . '
                            </button>

                        </div>

                    </div>

                </form>

            </div>
        ';

        return $html;
    }

    private function renderNewsletterForm()
    {
        $logo = $this->getNewsletterLogo();

        $html = '
            <div class="panel">

                <h3>
                    <i class="icon icon-picture"></i>
                    ' . $this->l('Logo newslettera') . '
                </h3>

                <form
                    method="post"
                    enctype="multipart/form-data"
                >

                    <input
                        type="hidden"
                        name="s24_action"
                        value="upload_newsletter_logo"
                    >

                    <div class="row">

                        <div class="col-lg-4">

                            <label>
                                <strong>
                                    ' . $this->l('Aktualne logo') . '
                                </strong>
                            </label>

                            <div
                                style="
                                    min-height:80px;
                                    padding:15px;
                                    border:1px solid #ddd;
                                    background:#fff;
                                    margin-bottom:15px;
                                "
                            >
        ';

        if (!empty($logo)) {
            $html .= '
                                <img
                                    src="' . htmlspecialchars(
                                        $logo,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) . '"
                                    alt="Newsletter"
                                    style="
                                        max-width:220px;
                                        max-height:100px;
                                        object-fit:contain;
                                    "
                                >
            ';
        } else {
            $html .= '
                                <span class="text-muted">
                                    ' . $this->l(
                                        'Brak ustawionego logo.'
                                    ) . '
                                </span>
            ';
        }

        $html .= '
                            </div>

                        </div>

                        <div class="col-lg-5">

                            <label>
                                <strong>
                                    ' . $this->l('Nowe logo') . '
                                </strong>
                            </label>

                            <input
                                type="file"
                                name="newsletter_logo"
                                class="form-control"
                                accept=".png,.jpg,.jpeg,.webp"
                            >

                            <p class="help-block">
                                ' . $this->l(
                                    'PNG, JPG, JPEG lub WEBP. Wgranie nowego logo zastąpi poprzednie.'
                                ) . '
                            </p>

                        </div>

                        <div
                            class="col-lg-3"
                            style="padding-top:25px;"
                        >

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="icon icon-upload"></i>
                                ' . $this->l('Wgraj logo') . '
                            </button>

                        </div>

                    </div>

                </form>
        ';

        if (!empty($logo)) {
            $html .= '
                <hr>

                <form method="post">

                    <input
                        type="hidden"
                        name="s24_action"
                        value="delete_newsletter_logo"
                    >

                    <button
                        type="submit"
                        class="btn btn-danger"
                        onclick="return confirm(\'' .
                            addslashes(
                                $this->l(
                                    'Czy na pewno usunąć logo newslettera?'
                                )
                            ) .
                        '\');"
                    >
                        <i class="icon icon-trash"></i>
                        ' . $this->l('Usuń logo') . '
                    </button>

                </form>
            ';
        }

        $html .= '
            </div>
        ';

        return $html;
    }

    private function renderSocialForm()
    {
        return '
            <div class="panel">

                <h3>
                    <i class="icon icon-share-alt"></i>
                    ' . $this->l('Dodaj social media') . '
                </h3>

                <form
                    method="post"
                    enctype="multipart/form-data"
                >

                    <input
                        type="hidden"
                        name="s24_action"
                        value="add_social"
                    >

                    <div class="row">

                        <div class="col-lg-3">

                            <label>
                                ' . $this->l('Typ') . '
                            </label>

                            <select
                                name="social_type"
                                class="form-control"
                            >

                                <option value="facebook">
                                    Facebook
                                </option>

                                <option value="instagram">
                                    Instagram
                                </option>

                                <option value="youtube">
                                    YouTube
                                </option>

                                <option value="tiktok">
                                    TikTok
                                </option>

                                <option value="linkedin">
                                    LinkedIn
                                </option>

                                <option value="x">
                                    X / Twitter
                                </option>

                            </select>

                        </div>

                        <div class="col-lg-4">

                            <label>
                                ' . $this->l('Adres') . '
                            </label>

                            <input
                                type="url"
                                name="social_url"
                                class="form-control"
                                placeholder="https://..."
                                required
                            >

                        </div>

                        <div class="col-lg-3">

                            <label>
                                ' . $this->l('Ikona') . '
                            </label>

                            <input
                                type="file"
                                name="social_icon"
                                class="form-control"
                                accept=".png,.jpg,.jpeg,.webp"
                            >

                            <small class="text-muted">
                                PNG, JPG, JPEG, WEBP
                            </small>

                        </div>

                        <div
                            class="col-lg-2"
                            style="padding-top:25px;"
                        >

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="icon icon-plus"></i>
                                ' . $this->l('Dodaj') . '
                            </button>

                        </div>

                    </div>

                </form>

            </div>
        ';
    }

    private function renderSocials()
    {
        $socials = Db::getInstance()->executeS(
            'SELECT *
             FROM `' . _DB_PREFIX_ . 's24footer_social`
             ORDER BY `position` ASC'
        );

        $html = '
            <div class="panel">

                <h3>
                    <i class="icon icon-share"></i>
                    ' . $this->l('Social media w stopce') . '
                </h3>
        ';

        if (!$socials) {
            $html .= '
                <div class="alert alert-info">
                    ' . $this->l(
                        'Nie dodano jeszcze żadnych profili społecznościowych.'
                    ) . '
                </div>
            ';
        }

        foreach ($socials as $social) {
            $idSocial = (int) $social['id_social'];

            $iconPreview = '';

            if (!empty($social['icon'])) {
                $iconPreview = '
                    <div style="margin-bottom:10px;">

                        <img
                            src="' . htmlspecialchars(
                                $social['icon'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) . '"
                            alt=""
                            style="
                                width:50px;
                                height:50px;
                                object-fit:contain;
                                border:1px solid #ddd;
                                padding:5px;
                                background:#fff;
                            "
                        >

                    </div>
                ';
            }

            $html .= '
                <form
                    method="post"
                    enctype="multipart/form-data"
                    style="
                        padding:15px;
                        margin-bottom:10px;
                        border:1px solid #ddd;
                    "
                >

                    <input
                        type="hidden"
                        name="s24_action"
                        value="update_social"
                    >

                    <input
                        type="hidden"
                        name="id_social"
                        value="' . $idSocial . '"
                    >

                    <div class="row">

                        <div class="col-lg-2">

                            <label>
                                ' . $this->l('Ikona') . '
                            </label>

                            ' . $iconPreview . '

                            <input
                                type="file"
                                name="social_icon"
                                class="form-control"
                                accept=".png,.jpg,.jpeg,.webp"
                            >

                        </div>

                        <div class="col-lg-2">

                            <label>
                                ' . $this->l('Typ') . '
                            </label>

                            <select
                                name="social_type"
                                class="form-control"
                            >

                                <option
                                    value="facebook"
                                    ' . (
                                        $social['type'] === 'facebook'
                                            ? 'selected'
                                            : ''
                                    ) . '
                                >
                                    Facebook
                                </option>

                                <option
                                    value="instagram"
                                    ' . (
                                        $social['type'] === 'instagram'
                                            ? 'selected'
                                            : ''
                                    ) . '
                                >
                                    Instagram
                                </option>

                                <option
                                    value="youtube"
                                    ' . (
                                        $social['type'] === 'youtube'
                                            ? 'selected'
                                            : ''
                                    ) . '
                                >
                                    YouTube
                                </option>

                                <option
                                    value="tiktok"
                                    ' . (
                                        $social['type'] === 'tiktok'
                                            ? 'selected'
                                            : ''
                                    ) . '
                                >
                                    TikTok
                                </option>

                                <option
                                    value="linkedin"
                                    ' . (
                                        $social['type'] === 'linkedin'
                                            ? 'selected'
                                            : ''
                                    ) . '
                                >
                                    LinkedIn
                                </option>

                                <option
                                    value="x"
                                    ' . (
                                        $social['type'] === 'x'
                                            ? 'selected'
                                            : ''
                                    ) . '
                                >
                                    X / Twitter
                                </option>

                            </select>

                        </div>

                        <div class="col-lg-4">

                            <label>
                                ' . $this->l('Adres') . '
                            </label>

                            <input
                                type="url"
                                name="social_url"
                                class="form-control"
                                value="' . htmlspecialchars(
                                    $social['url'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) . '"
                            >

                        </div>

                        <div class="col-lg-2">

                            <label>
                                ' . $this->l('Status') . '
                            </label>

                            <select
                                name="social_active"
                                class="form-control"
                            >

                                <option
                                    value="1"
                                    ' . (
                                        (int) $social['active'] === 1
                                            ? 'selected'
                                            : ''
                                    ) . '
                                >
                                    ' . $this->l('Aktywny') . '
                                </option>

                                <option
                                    value="0"
                                    ' . (
                                        (int) $social['active'] === 0
                                            ? 'selected'
                                            : ''
                                    ) . '
                                >
                                    ' . $this->l('Nieaktywny') . '
                                </option>

                            </select>

                        </div>

                        <div
                            class="col-lg-2"
                            style="padding-top:25px;"
                        >

                            <button
                                type="submit"
                                class="btn btn-default"
                            >
                                <i class="icon icon-save"></i>
                            </button>

                            <a
                                href="?s24_action=delete_social&id_social=' .
                                $idSocial . '"
                                class="btn btn-danger"
                                onclick="return confirm(\'Czy na pewno usunąć ten profil?\');"
                            >
                                <i class="icon icon-trash"></i>
                            </a>

                        </div>

                    </div>

                </form>
            ';
        }

        $html .= '
            </div>
        ';

        return $html;
    }

    /*
     * =========================================================
     * FRONTEND DATA
     * =========================================================
     */

    private function getFooterColumns()
    {
        $columns = Db::getInstance()->executeS(
            'SELECT *
             FROM `' . _DB_PREFIX_ . 's24footer_column`
             WHERE `active` = 1
             ORDER BY `position` ASC'
        );

        foreach ($columns as &$column) {
            $column['items'] = Db::getInstance()->executeS(
                'SELECT *
                 FROM `' . _DB_PREFIX_ . 's24footer_item`
                 WHERE `id_column` = ' . (int) $column['id_column'] . '
                 AND `active` = 1
                 ORDER BY `position` ASC'
            );
        }

        unset($column);

        return $columns;
    }

    private function getFooterSocials()
    {
        return Db::getInstance()->executeS(
            'SELECT *
             FROM `' . _DB_PREFIX_ . 's24footer_social`
             WHERE `active` = 1
             AND `icon` IS NOT NULL
             AND `icon` != ""
             ORDER BY `position` ASC'
        );
    }

    private function getNewsletterLogo()
    {
        $directory =
            _PS_MODULE_DIR_ .
            $this->name .
            '/views/img/newsletter/';

        if (!is_dir($directory)) {
            return '';
        }

        $files = scandir($directory);

        $allowedExtensions = [
            'png',
            'jpg',
            'jpeg',
            'webp',
        ];

        foreach ($files as $file) {
            if (
                $file === '.' ||
                $file === '..'
            ) {
                continue;
            }

            $extension = Tools::strtolower(
                pathinfo($file, PATHINFO_EXTENSION)
            );

            if (in_array(
                $extension,
                $allowedExtensions,
                true
            )) {
                return $this->_path .
                    'views/img/newsletter/' .
                    $file;
            }
        }

        return '';
    }

    private function assignFooterVariables()
    {
        $this->context->smarty->assign([
            's24FooterColumns' => $this->getFooterColumns(),
            's24FooterSocials' => $this->getFooterSocials(),
            's24NewsletterLogo' => $this->getNewsletterLogo(),
        ]);
    }

    public function hookActionFrontControllerSetVariables(&$params)
    {
        $this->assignFooterVariables();

        if (
            isset($params['templateVars']) &&
            is_array($params['templateVars'])
        ) {
            $params['templateVars']['s24FooterColumns'] =
                $this->getFooterColumns();

            $params['templateVars']['s24FooterSocials'] =
                $this->getFooterSocials();

            $params['templateVars']['s24NewsletterLogo'] =
                $this->getNewsletterLogo();
        }
    }

    public function hookDisplayFooter($params)
    {
        $this->assignFooterVariables();

        return '';
    }
}