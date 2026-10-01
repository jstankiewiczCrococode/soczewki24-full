<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class S24CategoryPage extends Module
{
    public function __construct()
    {
        $this->name = 's24categorypage';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'CrocoCode';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'S24 Category Page';
        $this->description = 'Konfiguracja wspólnego layoutu stron kategorii Soczewki24.';
    }

    public function install()
    {
        
        return parent::install()
            && $this->installDb()
            && $this->registerHook('actionCategoryFormBuilderModifier')
            && $this->registerHook('actionCategoryFormDataProviderData')
            && $this->registerHook('actionAfterCreateCategoryFormHandler')
            && $this->registerHook('actionAfterUpdateCategoryFormHandler')
            && $this->registerHook('actionCategoryUpdate')
            && $this->registerHook('displayHeader');
    }

    private function installDb()
    {
        $sqlFile = __DIR__ . '/sql/install.php';

        if (!file_exists($sqlFile)) {
            return false;
        }

        $sql = require $sqlFile;

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookActionCategoryFormBuilderModifier(array $params)
    {
        $formBuilder = $params['form_builder'];

        $formBuilder->add(
            's24_category_page',
            \Symfony\Component\Form\Extension\Core\Type\FormType::class,
            [
                'label' => 'Konfiguracja strony kategorii',
                'required' => false,
            ]
        );

        $categoryPage = $formBuilder->get('s24_category_page');

        $categoryPage->add(
            'hero_background',
            \Symfony\Component\Form\Extension\Core\Type\FileType::class,
            [
                'label' => 'Tło hero',
                'required' => false,
                'mapped' => false,
            ]
        );

        $categoryPage->add(
            'hero_title',
            \Symfony\Component\Form\Extension\Core\Type\TextType::class,
            [
                'label' => 'Tytuł hero',
                'required' => false,
            ]
        );

        $categoryPage->add(
            'hero_text',
            \Symfony\Component\Form\Extension\Core\Type\TextareaType::class,
            [
                'label' => 'Tekst hero',
                'required' => false,
            ]
        );

        $categoryPage->add(
            'brands_title',
            \Symfony\Component\Form\Extension\Core\Type\TextType::class,
            [
                'label' => 'Tytuł sekcji marek',
                'required' => false,
            ]
        );

        $categoryPage->add(
            'content_title',
            \Symfony\Component\Form\Extension\Core\Type\TextType::class,
            [
                'label' => 'Tytuł sekcji treści',
                'required' => false,
            ]
        );

        $categoryPage->add(
            'content_text',
            \Symfony\Component\Form\Extension\Core\Type\TextareaType::class,
            [
                'label' => 'Treść sekcji',
                'required' => false,
            ]
        );
    }

    public function hookActionCategoryFormDataProviderData(array $params)
    {
        if (empty($params['id'])) {
            return;
        }

        $idCategory = (int) $params['id'];
        $idLang = (int) $this->context->language->id;

        $data = Db::getInstance()->getRow(
            'SELECT
                `hero_background`,
                `hero_title`,
                `hero_text`,
                `brands_title`,
                `content_title`,
                `content_text`
            FROM `' . _DB_PREFIX_ . 's24_category_page`
            WHERE `id_category` = ' . $idCategory . '
            AND `id_lang` = ' . $idLang
        );

        if (!$data) {
            $data = [
                'hero_background' => '',
                'hero_title' => '',
                'hero_text' => '',
                'brands_title' => '',
                'content_title' => '',
                'content_text' => '',
            ];
        }

        $params['data']['s24_category_page'] = $data;
    }

    public function hookActionAfterCreateCategoryFormHandler(array $params)
    {
        $this->saveCategoryPageData($params);
    }

public function hookActionAfterUpdateCategoryFormHandler(array $params)
{
    file_put_contents('/tmp/s24-hook-test.txt', 'HOOK OK');

    $file = ($_FILES['category']['name']['s24_category_page']['hero_background'] ?? 'NO FILE')
    . ' | TMP: '
    . ($_FILES['category']['tmp_name']['s24_category_page']['hero_background'] ?? 'NO TMP');

    file_put_contents(
        '/tmp/s24-file-test.txt',
        $file
    );

    $this->saveCategoryPageData($params);
}

private function saveCategoryPageData(array $params)
{
    if (empty($params['id']) || empty($params['form_data'])) {
        return;
    }

    $idCategory = (int) $params['id'];
    $formData = $params['form_data'];

    file_put_contents(
    '/tmp/s24-category-form-data.txt',
    print_r($formData, true)
);

    if (!isset($formData['s24_category_page'])) {
        return;
    }

    $categoryData = $formData['s24_category_page'];
    PrestaShopLogger::addLog(
    'S24 DEBUG hero_background: ' . print_r($categoryData['hero_background'] ?? null, true),
    1
);
    $idLang = (int) $this->context->language->id;

    $heroBackground = '';

if (
    isset($_FILES['category']['name']['s24_category_page']['hero_background'])
    && !empty($_FILES['category']['tmp_name']['s24_category_page']['hero_background'])
) {
    $fileName = $_FILES['category']['name']['s24_category_page']['hero_background'];
    $tmpName = $_FILES['category']['tmp_name']['s24_category_page']['hero_background'];

    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($extension, $allowedExtensions, true)) {
        $uploadDir = _PS_IMG_DIR_ . 's24categorypage/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = 'category-' . $idCategory . '-' . $idLang . '-' . time() . '.' . $extension;

  $moveResult = move_uploaded_file($tmpName, $uploadDir . $newFileName);

file_put_contents(
    '/tmp/s24-move-test.txt',
    'TMP=' . $tmpName
    . ' | DIR=' . $uploadDir
    . ' | TARGET=' . $newFileName
    . ' | MOVE=' . ($moveResult ? 'YES' : 'NO')
    . ' | DIR_EXISTS=' . (is_dir($uploadDir) ? 'YES' : 'NO')
    . ' | DIR_WRITABLE=' . (is_writable($uploadDir) ? 'YES' : 'NO')
);

if ($moveResult) {
    $heroBackground = 's24categorypage/' . $newFileName;
}
    }
}

    if ($heroBackground === '') {
        $existingBackground = Db::getInstance()->getValue(
            'SELECT `hero_background`
             FROM `' . _DB_PREFIX_ . 's24_category_page`
             WHERE `id_category` = ' . $idCategory . '
             AND `id_lang` = ' . $idLang
        );

        $heroBackground = (string) $existingBackground;
    }

    $heroTitle = isset($categoryData['hero_title'])
        ? trim((string) $categoryData['hero_title'])
        : '';

    $heroText = isset($categoryData['hero_text'])
        ? (string) $categoryData['hero_text']
        : '';

    $brandsTitle = isset($categoryData['brands_title'])
        ? trim((string) $categoryData['brands_title'])
        : '';

    $contentTitle = isset($categoryData['content_title'])
        ? trim((string) $categoryData['content_title'])
        : '';

    $contentText = isset($categoryData['content_text'])
        ? (string) $categoryData['content_text']
        : '';

    Db::getInstance()->execute(
        'INSERT INTO `' . _DB_PREFIX_ . 's24_category_page`
        (
            `id_category`,
            `id_lang`,
            `hero_background`,
            `hero_title`,
            `hero_text`,
            `brands_title`,
            `content_title`,
            `content_text`
        )
        VALUES (
            ' . $idCategory . ',
            ' . $idLang . ',
            \'' . pSQL($heroBackground) . '\',
            \'' . pSQL($heroTitle) . '\',
            \'' . pSQL($heroText, true) . '\',
            \'' . pSQL($brandsTitle) . '\',
            \'' . pSQL($contentTitle) . '\',
            \'' . pSQL($contentText, true) . '\'
        )
        ON DUPLICATE KEY UPDATE
            `hero_background` = \'' . pSQL($heroBackground) . '\',
            `hero_title` = \'' . pSQL($heroTitle) . '\',
            `hero_text` = \'' . pSQL($heroText, true) . '\',
            `brands_title` = \'' . pSQL($brandsTitle) . '\',
            `content_title` = \'' . pSQL($contentTitle) . '\',
            `content_text` = \'' . pSQL($contentText, true) . '\''
    );
}


public function hookDisplayHeader(array $params)
{
    if (!isset($this->context->controller) || !($this->context->controller instanceof CategoryController)) {
        return;
    }

    $idCategory = (int) $this->context->controller->getCategory()->id;
    $idLang = (int) $this->context->language->id;

    $categoryPage = Db::getInstance()->getRow(
        'SELECT
            `hero_background`,
            `hero_title`,
            `hero_text`,
            `brands_title`,
            `content_title`,
            `content_text`
        FROM `' . _DB_PREFIX_ . 's24_category_page`
        WHERE `id_category` = ' . $idCategory . '
        AND `id_lang` = ' . $idLang
    );

    if (!$categoryPage) {
        return;
    }

    $this->context->smarty->assign([
        's24_category_page' => $categoryPage,
    ]);
}


public function hookActionCategoryUpdate(array $params)
{
    file_put_contents(
        '/tmp/s24-category-update-hook.txt',
        print_r($params, true)
    );
}


}

