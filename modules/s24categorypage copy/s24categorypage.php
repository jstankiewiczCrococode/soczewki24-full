<?php

use Symfony\Component\Form\Extension\Core\Type\FileType;

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

    public function hookActionAfterCreateFeatureValueFormHandler(array $params)
    {
        if (!isset($params['id'])) {
            return;
        }

        $this->saveFeatureValueImage((int) $params['id']);
    }

    public function hookActionAfterUpdateFeatureValueFormHandler(array $params)
    {
        if (!isset($params['id'])) {
            return;
        }

        $this->saveFeatureValueImage((int) $params['id']);
    }


public function hookActionFrontControllerSetVariables(array $params)
{
    if (
        !isset($this->context->controller)
        || !method_exists($this->context->controller, 'getCategory')
    ) {
        return [];
    }

    $category = $this->context->controller->getCategory();

    if (!is_object($category) || !(int) $category->id) {
        return [];
    }

    $idCategory = (int) $category->id;
    $idLang = (int) $this->context->language->id;

    /*
     * Domyślne wartości.
     * Dzięki temu category-header.tpl zawsze otrzyma
     * komplet zmiennych.
     */
    $data = [
        's24_category_page' => [
            'hero_background' => '',
            'hero_title' => '',
            'hero_text' => '',
            'brands_title' => '',
            'content_title' => '',
            'content_text' => '',
        ],
        'styl_values' => [],
        'ksztalt_values' => [],
        'material_values' => [],
        'rozmiar_values' => [],
        'kolor_values' => [],
        'max_product_price' => 0,
    ];

    /*
     * DANE STRONY KATEGORII
     */
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

    if ($categoryPage) {
        $data['s24_category_page'] = $categoryPage;
    }

    /*
     * FUNKCJA POMOCNICZA DO POBIERANIA CECH
     */
    $features = [
        'styl_values' => 'Styl',
        'ksztalt_values' => 'Kształt',
        'material_values' => 'Materiał',
        'rozmiar_values' => 'Rozmiar',
        'kolor_values' => 'Kolor',
    ];

    foreach ($features as $variableName => $featureName) {

        $idFeature = (int) Db::getInstance()->getValue(
            'SELECT f.`id_feature`
             FROM `' . _DB_PREFIX_ . 'feature` f
             INNER JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                ON fl.`id_feature` = f.`id_feature`
                AND fl.`id_lang` = ' . $idLang . '
             WHERE fl.`name` = \'' . pSQL($featureName) . '\''
        );

        if ($idFeature <= 0) {
            continue;
        }

        if ($featureName === 'Kolor') {

            $values = Db::getInstance()->executeS(
                'SELECT
                    fv.`id_feature_value`,
                    fvl.`value`,
                    s24fvi.`image`
                FROM `' . _DB_PREFIX_ . 'feature_value` fv
                INNER JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                    ON fvl.`id_feature_value` = fv.`id_feature_value`
                    AND fvl.`id_lang` = ' . $idLang . '
                LEFT JOIN `' . _DB_PREFIX_ . 's24_feature_value_image` s24fvi
                    ON s24fvi.`id_feature_value` = fv.`id_feature_value`
                WHERE fv.`id_feature` = ' . $idFeature . '
                ORDER BY fv.`id_feature_value` ASC'
            );

        } else {

            $values = Db::getInstance()->executeS(
                'SELECT
                    fv.`id_feature_value`,
                    fvl.`value`
                FROM `' . _DB_PREFIX_ . 'feature_value` fv
                INNER JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                    ON fvl.`id_feature_value` = fv.`id_feature_value`
                    AND fvl.`id_lang` = ' . $idLang . '
                WHERE fv.`id_feature` = ' . $idFeature . '
                ORDER BY fv.`id_feature_value` ASC'
            );
        }

        if ($values) {
            $data[$variableName] = $values;
        }
    }

    /*
     * MAKSYMALNA CENA
     */
    $maxProductPrice = Db::getInstance()->getValue(
        'SELECT MAX(lpi.`price_max`)
         FROM `' . _DB_PREFIX_ . 'layered_price_index` lpi
         WHERE lpi.`id_shop` = ' . (int) $this->context->shop->id . '
         AND lpi.`id_currency` = ' . (int) $this->context->currency->id . '
         AND lpi.`id_country` = ' . (int) $this->context->country->id
    );

    if ($maxProductPrice !== false && $maxProductPrice !== null) {
        $data['max_product_price'] = (float) $maxProductPrice;
    }

    /*
     * ZWRACAMY ZMIENNE BEZPOŚREDNIO DO FRONTU
     */
    return $data;
}



    public function install()
    {
        return parent::install()
            && $this->installDb()
            && $this->registerHook('actionFeatureValueFormBuilderModifier')
            && $this->registerHook('actionAfterCreateFeatureValueFormHandler')
            && $this->registerHook('actionAfterUpdateFeatureValueFormHandler')            
            && $this->installFeatureValueImagesTable()
            && $this->registerHook('displayFeatureValueForm')
            && $this->registerHook('displayFeatureValuePostProcess')
            && $this->registerHook('actionFeatureValueSave')
            && $this->registerHook('actionFeatureValueDelete')
            && $this->registerHook('actionCategoryFormBuilderModifier')
            && $this->registerHook('actionCategoryFormDataProviderData')
            && $this->registerHook('actionAfterCreateCategoryFormHandler')
            && $this->registerHook('actionAfterUpdateCategoryFormHandler')
            && $this->registerHook('actionCategoryUpdate')
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionFrontControllerSetVariables')
            && $this->registerHook('actionFacetedSearchFilters');
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
        return $this->uninstallFeatureValueImagesTable()
            && parent::uninstall();
    }

    public function hookActionFeatureValueFormBuilderModifier(array $params)
    {
        if (!isset($params['form_builder'])) {
            return;
        }

        $idFeatureValue = 0;

        if (isset($params['id'])) {
            $idFeatureValue = (int) $params['id'];
        }

        $help = 'JPG, JPEG, PNG lub WEBP.';

        if ($idFeatureValue > 0) {
            $image = (string) Db::getInstance()->getValue(
                'SELECT `image`
                 FROM `' . _DB_PREFIX_ . 's24_feature_value_image`
                 WHERE `id_feature_value` = ' . $idFeatureValue
            );

            if ($image !== '') {
                $imageUrl = '/img/' . ltrim($image, '/');

                $imageUrl = htmlspecialchars(
                    $imageUrl,
                    ENT_QUOTES,
                    'UTF-8'
                );

                $help =
                    '<div style="margin-top:10px;">' .
                        '<div style="margin-bottom:8px;">' .
                            '<strong>Aktualny obrazek:</strong>' .
                        '</div>' .
                        '<img ' .
                            'src="' . $imageUrl . '" ' .
                            'alt="" ' .
                            'style="max-width:120px; max-height:120px; border:1px solid #ddd; padding:5px; display:block;"' .
                        '>' .
                        '<div style="margin-top:8px;">' .
                            'Jeśli chcesz zmienić obrazek, wybierz nowy plik powyżej.' .
                        '</div>' .
                    '</div>';
            }
        }

        $params['form_builder']->add(
            's24_feature_value_image',
            FileType::class,
            [
                'label' => 'Obrazek',
                'required' => false,
                'mapped' => false,
                'multiple' => false,
                'help' => $help,
                'help_html' => true,
                'attr' => [
                    'accept' => '.jpg,.jpeg,.png,.webp',
                ],
            ]
        );
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
        file_put_contents(
            '/tmp/s24-hook-test.txt',
            'HOOK OK'
        );

        $file = (
            $_FILES['category']['name']['s24_category_page']['hero_background']
            ?? 'NO FILE'
        )
        . ' | TMP: '
        . (
            $_FILES['category']['tmp_name']['s24_category_page']['hero_background']
            ?? 'NO TMP'
        );

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
            'S24 DEBUG hero_background: ' .
            print_r($categoryData['hero_background'] ?? null, true),
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

            $extension = strtolower(
                pathinfo($fileName, PATHINFO_EXTENSION)
            );

            $allowedExtensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            if (in_array($extension, $allowedExtensions, true)) {

                $uploadDir = _PS_IMG_DIR_ . 's24categorypage/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newFileName =
                    'category-' .
                    $idCategory .
                    '-' .
                    $idLang .
                    '-' .
                    time() .
                    '.' .
                    $extension;

                $moveResult = move_uploaded_file(
                    $tmpName,
                    $uploadDir . $newFileName
                );

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
                    $heroBackground =
                        's24categorypage/' . $newFileName;
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

    private function installFeatureValueImagesTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 's24_feature_value_image` (
            `id_feature_value` INT(10) UNSIGNED NOT NULL,
            `image` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id_feature_value`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return Db::getInstance()->execute($sql);
    }

    private function uninstallFeatureValueImagesTable()
    {
        return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 's24_feature_value_image`'
        );
    }

    

public function hookActionFacetedSearchFilters(array $params)
{
    if (!isset($params['search'])) {
        return;
    }

    $search = $params['search'];

    if (!is_object($search)) {
        return;
    }

    if (!method_exists($search, 'getSearchAdapter')) {
        return;
    }

    $searchAdapter = $search->getSearchAdapter();

    if (!is_object($searchAdapter)) {
        return;
    }


    /* =========================================
       FILTRY CECH
       ========================================= */

    $filters = [
        'styl' => 'Styl',
        'ksztalt' => 'Kształt',
        'material' => 'Materiał',
        'rozmiar' => 'Rozmiar',
        'kolor' => 'Kolor',
    ];

    $idLang = (int) $this->context->language->id;


    foreach ($filters as $paramName => $featureName) {

        $values = Tools::getValue($paramName);

        if (!is_array($values)) {

            if ($values === null || $values === '') {
                continue;
            }

            $values = [$values];
        }


        $values = array_map('intval', $values);

        $values = array_values(
            array_unique(
                array_filter($values)
            )
        );


        if (empty($values)) {
            continue;
        }


        $idFeature = (int) Db::getInstance()->getValue(
            'SELECT f.`id_feature`
             FROM `' . _DB_PREFIX_ . 'feature` f
             INNER JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                ON fl.`id_feature` = f.`id_feature`
                AND fl.`id_lang` = ' . $idLang . '
             WHERE fl.`name` = \'' . pSQL($featureName) . '\''
        );


        if ($idFeature <= 0) {
            continue;
        }


        $validValues = Db::getInstance()->executeS(
            'SELECT `id_feature_value`
             FROM `' . _DB_PREFIX_ . 'feature_value`
             WHERE `id_feature` = ' . $idFeature . '
             AND `id_feature_value` IN (' . implode(',', $values) . ')'
        );


        if (!$validValues) {
            continue;
        }


        $validValueIds = [];


        foreach ($validValues as $value) {

            $validValueIds[] = (int) $value['id_feature_value'];

        }


        if (empty($validValueIds)) {
            continue;
        }


        $searchAdapter->addOperationsFilter(
            'with_features_' . $idFeature,
            [
                [
                    [
                        'id_feature_value',
                        $validValueIds
                    ]
                ]
            ]
        );

    }


    /* =========================================
       FILTR CENOWY
       ========================================= */

    $priceMin = Tools::getValue('price_min', null);
    $priceMax = Tools::getValue('price_max', null);


    /*
     * Jeżeli nie ma ceny w URL,
     * nic nie robimy.
     */

    if (
        $priceMin === null
        && $priceMax === null
    ) {
        return;
    }


    /*
     * Walidacja wartości.
     */

    $priceMin = is_numeric($priceMin)
        ? (float) $priceMin
        : null;

    $priceMax = is_numeric($priceMax)
        ? (float) $priceMax
        : null;


    /*
     * Jeżeli zakres jest niepoprawny,
     * nie filtrujemy.
     */

    if (
        $priceMin === null
        && $priceMax === null
    ) {
        return;
    }


    /*
     * Korekta zakresu, gdyby ktoś ręcznie
     * zmienił URL.
     */

    if (
        $priceMin !== null
        && $priceMax !== null
        && $priceMin > $priceMax
    ) {

        $tmp = $priceMin;

        $priceMin = $priceMax;
        $priceMax = $tmp;

    }


    /*
     * Domyślne wartości.
     *
     * Jeżeli brakuje min:
     * 0 zł.
     *
     * Jeżeli brakuje max:
     * bardzo duża wartość.
     */

    if ($priceMin === null) {
        $priceMin = 0;
    }


    if ($priceMax === null) {
        $priceMax = 999999999;
    }


    /*
     * PrestaShop ps_facetedsearch filtruje cenę
     * na indeksie:
     *
     * layered_price_index.price_min
     * layered_price_index.price_max
     *
     * Produkt musi nachodzić na wybrany zakres.
     */

    $searchAdapter->addFilter(
        'price_min',
        [
            floor($priceMax) + 1
        ],
        '<'
    );


    $searchAdapter->addFilter(
        'price_max',
        [
            ceil($priceMin) - 1
        ],
        '>'
    );
}
   

public function hookDisplayHeader(array $params)
{


die('S24 DISPLAY HEADER DZIALA');
    /*
     * Zawsze inicjalizujemy zmienne używane przez category-header.tpl.
     * Dzięki temu layout nie może się wywalić, nawet jeśli PrestaShop
     * nie poda jeszcze kontekstu kategorii.
     */
    $this->context->smarty->assign([
        's24_category_page' => [
            'hero_background' => '',
            'hero_title' => '',
            'hero_text' => '',
            'brands_title' => '',
            'content_title' => '',
            'content_text' => '',
        ],
        'styl_values' => [],
        'ksztalt_values' => [],
        'material_values' => [],
        'rozmiar_values' => [],
        'kolor_values' => [],
        'max_product_price' => 0,
    ]);

    if (
        !isset($this->context->controller)
        || !method_exists($this->context->controller, 'getCategory')
    ) {
        return;
    }

    $category = $this->context->controller->getCategory();

    if (!is_object($category) || !(int) $category->id) {
        return;
    }

    $idCategory = (int) $category->id;
    $idLang = (int) $this->context->language->id;

        /*
         * Frontend JS
         */
        $this->context->controller->registerJavascript(
            'module-s24categorypage-front',
            'modules/' . $this->name . '/views/js/front.js',
            [
                'position' => 'bottom',
                'priority' => 150,
            ]
        );

        /*
         * Dane strony kategorii
         */
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
            $categoryPage = [
                'hero_background' => '',
                'hero_title' => '',
                'hero_text' => '',
                'brands_title' => '',
                'content_title' => '',
                'content_text' => '',
            ];
        }

        /*
         * STYL
         */
        $stylFeatureId = (int) Db::getInstance()->getValue(
            'SELECT
                f.`id_feature`
            FROM `' . _DB_PREFIX_ . 'feature` f
            INNER JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                ON fl.`id_feature` = f.`id_feature`
                AND fl.`id_lang` = ' . $idLang . '
            WHERE fl.`name` = \'' . pSQL('Styl') . '\''
        );

        $stylValues = [];

        if ($stylFeatureId > 0) {
            $stylValues = Db::getInstance()->executeS(
                'SELECT
                    fv.`id_feature_value`,
                    fvl.`value`
                FROM `' . _DB_PREFIX_ . 'feature_value` fv
                INNER JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                    ON fvl.`id_feature_value` = fv.`id_feature_value`
                    AND fvl.`id_lang` = ' . $idLang . '
                WHERE fv.`id_feature` = ' . $stylFeatureId . '
                ORDER BY fv.`id_feature_value` ASC'
            );
        }

        /*
         * MATERIAŁ
         */
        $materialFeatureId = (int) Db::getInstance()->getValue(
            'SELECT
                f.`id_feature`
            FROM `' . _DB_PREFIX_ . 'feature` f
            INNER JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                ON fl.`id_feature` = f.`id_feature`
                AND fl.`id_lang` = ' . $idLang . '
            WHERE fl.`name` = \'' . pSQL('Materiał') . '\''
        );

        $materialValues = [];

        if ($materialFeatureId > 0) {
            $materialValues = Db::getInstance()->executeS(
                'SELECT
                    fv.`id_feature_value`,
                    fvl.`value`
                FROM `' . _DB_PREFIX_ . 'feature_value` fv
                INNER JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                    ON fvl.`id_feature_value` = fv.`id_feature_value`
                    AND fvl.`id_lang` = ' . $idLang . '
                WHERE fv.`id_feature` = ' . $materialFeatureId . '
                ORDER BY fv.`id_feature_value` ASC'
            );
        }

        /*
         * ROZMIAR
         */
        $rozmiarFeatureId = (int) Db::getInstance()->getValue(
            'SELECT
                f.`id_feature`
            FROM `' . _DB_PREFIX_ . 'feature` f
            INNER JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                ON fl.`id_feature` = f.`id_feature`
                AND fl.`id_lang` = ' . $idLang . '
            WHERE fl.`name` = \'' . pSQL('Rozmiar') . '\''
        );

        $rozmiarValues = [];

        if ($rozmiarFeatureId > 0) {
            $rozmiarValues = Db::getInstance()->executeS(
                'SELECT
                    fv.`id_feature_value`,
                    fvl.`value`
                FROM `' . _DB_PREFIX_ . 'feature_value` fv
                INNER JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                    ON fvl.`id_feature_value` = fv.`id_feature_value`
                    AND fvl.`id_lang` = ' . $idLang . '
                WHERE fv.`id_feature` = ' . $rozmiarFeatureId . '
                ORDER BY fv.`id_feature_value` ASC'
            );
        }

        /*
         * KOLOR
         */
        $kolorFeatureId = (int) Db::getInstance()->getValue(
            'SELECT
                f.`id_feature`
            FROM `' . _DB_PREFIX_ . 'feature` f
            INNER JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                ON fl.`id_feature` = f.`id_feature`
                AND fl.`id_lang` = ' . $idLang . '
            WHERE fl.`name` = \'' . pSQL('Kolor') . '\''
        );

        $kolorValues = [];

        if ($kolorFeatureId > 0) {
            $kolorValues = Db::getInstance()->executeS(
                'SELECT
                    fv.`id_feature_value`,
                    fvl.`value`,
                    s24fvi.`image`
                FROM `' . _DB_PREFIX_ . 'feature_value` fv
                INNER JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                    ON fvl.`id_feature_value` = fv.`id_feature_value`
                    AND fvl.`id_lang` = ' . $idLang . '
                LEFT JOIN `' . _DB_PREFIX_ . 's24_feature_value_image` s24fvi
                    ON s24fvi.`id_feature_value` = fv.`id_feature_value`
                WHERE fv.`id_feature` = ' . $kolorFeatureId . '
                ORDER BY fv.`id_feature_value` ASC'
            );
        }

        $maxProductPrice = 0;

            $maxProductPrice = (float) Db::getInstance()->getValue(
                'SELECT MAX(lpi.`price_max`)
                FROM `' . _DB_PREFIX_ . 'layered_price_index` lpi
                WHERE lpi.`id_shop` = ' . (int) $this->context->shop->id . '
                AND lpi.`id_currency` = ' . (int) $this->context->currency->id . '
                AND lpi.`id_country` = ' . (int) $this->context->country->id
            );

        /*
         * KSZTAŁT
         */
        $ksztaltFeatureId = (int) Db::getInstance()->getValue(
            'SELECT
                f.`id_feature`
            FROM `' . _DB_PREFIX_ . 'feature` f
            INNER JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                ON fl.`id_feature` = f.`id_feature`
                AND fl.`id_lang` = ' . $idLang . '
            WHERE fl.`name` = \'' . pSQL('Kształt') . '\''
        );

        $ksztaltValues = [];

        if ($ksztaltFeatureId > 0) {
            $ksztaltValues = Db::getInstance()->executeS(
                'SELECT
                    fv.`id_feature_value`,
                    fvl.`value`
                FROM `' . _DB_PREFIX_ . 'feature_value` fv
                INNER JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                    ON fvl.`id_feature_value` = fv.`id_feature_value`
                    AND fvl.`id_lang` = ' . $idLang . '
                WHERE fv.`id_feature` = ' . $ksztaltFeatureId . '
                ORDER BY fv.`id_feature_value` ASC'
            );
        }

        /*
         * Smarty
         */
        $this->context->smarty->assign([
            's24_category_page' => $categoryPage,
            'styl_values' => $stylValues,
            'ksztalt_values' => $ksztaltValues,
            'material_values' => $materialValues,
            'rozmiar_values' => $rozmiarValues,
            'kolor_values' => $kolorValues,
            'max_product_price' => $maxProductPrice,
        ]);
    }

    public function hookActionCategoryUpdate(array $params)
    {
        file_put_contents(
            '/tmp/s24-category-update-hook.txt',
            print_r($params, true)
        );
    }

    public function hookDisplayFeatureValueForm(array $params)
    {
        return '
            <script>
            document.addEventListener("DOMContentLoaded", function () {

                var form =
                    document.querySelector("form[name=\'feature_value\']")
                    || document.querySelector("form[id=\'feature_value\']")
                    || document.querySelector("form");

                if (!form) {
                    return;
                }

                if (form.querySelector("[name=\'feature_value[s24_feature_value_image]\']")) {
                    return;
                }

                form.setAttribute("enctype", "multipart/form-data");

                var groups = form.querySelectorAll(".form-group, .mb-3, .form-group.row");

                var wrapper = document.createElement("div");
                wrapper.className = "form-group row";
                wrapper.innerHTML =
                    \'<label class="form-control-label col-lg-3">Obrazek</label>\' +
                    \'<div class="col-lg-9">\' +
                        \'<input type="file" \' +
                            \'name="feature_value[s24_feature_value_image]" \' +
                            \'accept=".jpg,.jpeg,.png,.webp" \' +
                            \'class="form-control"\' +
                        \'>\' +
                        \'<small class="form-text text-muted">\' +
                            \'JPG, JPEG, PNG lub WEBP\' +
                        \'</small>\' +
                    \'</div>\';

                if (groups.length > 0) {
                    groups[groups.length - 1].parentNode.insertBefore(
                        wrapper,
                        groups[groups.length - 1].nextSibling
                    );
                } else {
                    form.appendChild(wrapper);
                }
            });
            </script>
        ';
    }

    public function hookDisplayFeatureValuePostProcess(array $params)
    {
        $idFeatureValue = 0;

        if (isset($params['id_feature_value'])) {
            $idFeatureValue = (int) $params['id_feature_value'];
        }

        if ($idFeatureValue <= 0) {
            return;
        }

        $this->saveFeatureValueImage($idFeatureValue);
    }

    public function hookActionFeatureValueSave(array $params)
    {
        file_put_contents(
            '/tmp/s24-feature-save-debug.txt',
            "=== PARAMS ===\n"
            . print_r($params, true)
            . "\n=== FILES ===\n"
            . print_r($_FILES, true)
            . "\n",
            FILE_APPEND
        );

        $idFeatureValue = 0;

        if (isset($params['id_feature_value'])) {
            $idFeatureValue = (int) $params['id_feature_value'];
        } elseif (
            isset($params['object'])
            && is_object($params['object'])
            && isset($params['object']->id)
        ) {
            $idFeatureValue = (int) $params['object']->id;
        }

        file_put_contents(
            '/tmp/s24-feature-save-debug.txt',
            "=== ID FEATURE VALUE ===\n"
            . $idFeatureValue
            . "\n\n",
            FILE_APPEND
        );

        if ($idFeatureValue <= 0) {
            return;
        }

        $this->saveFeatureValueImage($idFeatureValue);
    }

    private function saveFeatureValueImage($idFeatureValue)
    {
        $file = null;

        // Nowy sposób: zwykły input file
        if (isset($_FILES['s24_feature_value_image'])) {
            $file = $_FILES['s24_feature_value_image'];
        }

        // Stary sposób: pole zagnieżdżone w formularzu Symfony
        if (
            $file === null
            && isset($_FILES['feature_value']['tmp_name']['s24_feature_value_image'])
        ) {
            $file = [
                'tmp_name' => $_FILES['feature_value']['tmp_name']['s24_feature_value_image'],
                'name' => $_FILES['feature_value']['name']['s24_feature_value_image'] ?? '',
                'error' => $_FILES['feature_value']['error']['s24_feature_value_image'] ?? UPLOAD_ERR_NO_FILE,
            ];
        }

        if (!is_array($file)) {
            return;
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $fileName = (string) ($file['name'] ?? '');
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if (
            $error !== UPLOAD_ERR_OK
            || $tmpName === ''
            || $fileName === ''
        ) {
            return;
        }

        if (!is_uploaded_file($tmpName)) {
            return;
        }

        $extension = strtolower(
            pathinfo($fileName, PATHINFO_EXTENSION)
        );

        $allowedExtensions = [
            'jpg',
            'jpeg',
            'png',
            'webp',
        ];

        if (!in_array($extension, $allowedExtensions, true)) {
            return;
        }

        $uploadDir = _PS_IMG_DIR_ . 's24featurevalue/';

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return;
            }
        }

        $newFileName =
            'feature-value-' .
            (int) $idFeatureValue .
            '-' .
            time() .
            '.' .
            $extension;

        $targetFile = $uploadDir . $newFileName;

        if (!move_uploaded_file($tmpName, $targetFile)) {
            return;
        }

        $oldImage = (string) Db::getInstance()->getValue(
            'SELECT `image`
             FROM `' . _DB_PREFIX_ . 's24_feature_value_image`
             WHERE `id_feature_value` = ' . (int) $idFeatureValue
        );

        $imagePath = 's24featurevalue/' . $newFileName;

        $result = Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 's24_feature_value_image`
            (
                `id_feature_value`,
                `image`
            )
            VALUES
            (
                ' . (int) $idFeatureValue . ',
                \'' . pSQL($imagePath) . '\'
            )
            ON DUPLICATE KEY UPDATE
                `image` = \'' . pSQL($imagePath) . '\''
        );

        if (!$result) {
            @unlink($targetFile);
            return;
        }

        if ($oldImage !== '') {
            $oldFile = _PS_IMG_DIR_ . $oldImage;

            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }
    }

    public function hookActionFeatureValueDelete(array $params)
    {
        $idFeatureValue = 0;

        if (isset($params['id_feature_value'])) {
            $idFeatureValue = (int) $params['id_feature_value'];
        } elseif (
            isset($params['object'])
            && is_object($params['object'])
        ) {
            if (isset($params['object']->id)) {
                $idFeatureValue = (int) $params['object']->id;
            }
        }

        if ($idFeatureValue <= 0) {
            return;
        }

        $image = (string) Db::getInstance()->getValue(
            'SELECT `image`
             FROM `' . _DB_PREFIX_ . 's24_feature_value_image`
             WHERE `id_feature_value` = ' . $idFeatureValue
        );

        if ($image !== '') {
            $file = _PS_IMG_DIR_ . $image;

            if (is_file($file)) {
                @unlink($file);
            }
        }

        Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 's24_feature_value_image`
             WHERE `id_feature_value` = ' . $idFeatureValue
        );
    }
}