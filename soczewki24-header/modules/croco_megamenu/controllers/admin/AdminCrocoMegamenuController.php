<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'croco_megamenu/classes/CrocoMegamenuItem.php';

class AdminCrocoMegamenuController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'croco_megamenu_item';
        $this->identifier = 'id_item';
        $this->className = 'CrocoMegamenuItem';
        $this->lang = false;
        $this->allow_export = false;
        $this->position_identifier = 'id_item';
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';

        parent::__construct();

        $this->fields_list = [
            'id_item' => [
                'title' => $this->trans('ID', [], 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'label' => [
                'title' => $this->trans('Etykieta', [], 'Modules.Crocomegamenu.Admin'),
            ],
            'custom_url' => [
                'title' => $this->trans('URL', [], 'Modules.Crocomegamenu.Admin'),
            ],
            'is_highlighted' => [
                'title' => $this->trans('Wyrozniona', [], 'Modules.Crocomegamenu.Admin'),
                'align' => 'center',
                'type' => 'bool',
                'active' => 'highlight',
                'ajax' => true,
                'orderby' => false,
                'search' => false,
            ],
            'active' => [
                'title' => $this->trans('Aktywna', [], 'Modules.Crocomegamenu.Admin'),
                'align' => 'center',
                'type' => 'bool',
                'active' => 'status',
                'ajax' => true,
                'orderby' => false,
                'search' => false,
            ],
            'position' => [
                'title' => $this->trans('Kolejnosc', [], 'Modules.Crocomegamenu.Admin'),
                'align' => 'center',
                'position' => 'position',
                'filter_key' => 'a!position',
                'search' => false,
            ],
        ];

        $this->actions = ['edit', 'delete'];
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->trans('Usun zaznaczone', [], 'Modules.Crocomegamenu.Admin'),
                'confirm' => $this->trans('Usunac zaznaczone pozycje?', [], 'Modules.Crocomegamenu.Admin'),
                'icon' => 'icon-trash',
            ],
        ];
    }

    public function initPageHeaderToolbar(): void
    {
        $this->page_header_toolbar_btn['new'] = [
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->trans('Dodaj pozycje menu', [], 'Modules.Crocomegamenu.Admin'),
            'icon' => 'process-icon-new',
        ];

        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function renderForm()
    {
        $item = $this->loadObject(true);

        $inputs = [
            [
                'type' => 'text',
                'label' => $this->trans('Etykieta', [], 'Modules.Crocomegamenu.Admin'),
                'name' => 'label',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->trans('URL', [], 'Modules.Crocomegamenu.Admin'),
                'name' => 'custom_url',
                'desc' => $this->trans('Uzywany tylko gdy pozycja nie ma kolumn - wtedy jest zwyklym linkiem.', [], 'Modules.Crocomegamenu.Admin'),
            ],
            [
                'type' => 'switch',
                'label' => $this->trans('Wyrozniona', [], 'Modules.Crocomegamenu.Admin'),
                'name' => 'is_highlighted',
                'desc' => $this->trans('Inny kolor, jak "Promocje!" w makiecie.', [], 'Modules.Crocomegamenu.Admin'),
                'values' => $this->getSwitchValues('is_highlighted'),
            ],
            [
                'type' => 'switch',
                'label' => $this->trans('Aktywna', [], 'Modules.Crocomegamenu.Admin'),
                'name' => 'active',
                'values' => $this->getSwitchValues('active'),
            ],
            [
                'type' => 'html',
                'label' => $this->trans('Mega-panel', [], 'Modules.Crocomegamenu.Admin'),
                'name' => 'megamenu_builder',
                'html_content' => $this->renderBuilder($item),
            ],
        ];

        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('Pozycja menu', [], 'Modules.Crocomegamenu.Admin'),
                'icon' => 'icon-list-ul',
            ],
            'input' => array_map(
                static fn (array $input): array => $input + ['col' => 12],
                $inputs
            ),
            'submit' => [
                'title' => $this->trans('Zapisz', [], 'Admin.Actions'),
            ],
        ];

        return parent::renderForm();
    }

    private function renderBuilder(?ObjectModel $item): string
    {
        $json = Validate::isLoadedObject($item)
            ? CrocoMegamenuItem::columnsToJson((int) $item->id)
            : '[]';

        $labels = json_encode([
            'column' => $this->trans('Kolumna', [], 'Modules.Crocomegamenu.Admin'),
            'addBlock' => $this->trans('Dodaj blok', [], 'Modules.Crocomegamenu.Admin'),
            'addLinks' => $this->trans('Grupa linkow', [], 'Modules.Crocomegamenu.Admin'),
            'addCta' => $this->trans('Link z strzalka (CTA)', [], 'Modules.Crocomegamenu.Admin'),
            'addBanner' => $this->trans('Baner z obrazkiem', [], 'Modules.Crocomegamenu.Admin'),
            'addLink' => $this->trans('Dodaj link', [], 'Modules.Crocomegamenu.Admin'),
            'blockTitle' => $this->trans('Naglowek grupy, np. Marka', [], 'Modules.Crocomegamenu.Admin'),
            'linkLabel' => $this->trans('Tekst linku', [], 'Modules.Crocomegamenu.Admin'),
            'linkUrl' => $this->trans('Adres URL', [], 'Modules.Crocomegamenu.Admin'),
            'bannerImage' => $this->trans('Adres obrazka', [], 'Modules.Crocomegamenu.Admin'),
            'bannerText' => $this->trans('Tekst na banerze', [], 'Modules.Crocomegamenu.Admin'),
            'remove' => $this->trans('Usun', [], 'Admin.Actions'),
            'emptyColumn' => $this->trans('Pusta kolumna - przeciagnij tu blok albo dodaj nowy', [], 'Modules.Crocomegamenu.Admin'),
            'typeLinks' => $this->trans('Grupa linkow', [], 'Modules.Crocomegamenu.Admin'),
            'typeCta' => $this->trans('CTA', [], 'Modules.Crocomegamenu.Admin'),
            'typeBanner' => $this->trans('Baner', [], 'Modules.Crocomegamenu.Admin'),
        ], JSON_UNESCAPED_UNICODE);

        return '
            <div class="megamenu-builder"
                 data-columns="' . (int) CrocoMegamenuItem::COLUMN_COUNT . '"
                 data-labels="' . htmlspecialchars((string) $labels, ENT_QUOTES) . '">
                <p class="help-block">'
                    . $this->trans(
                        'Kolumny odpowiadaja kolumnom w rozwijanym panelu. Bloki i linki przeciagasz uchwytem, takze miedzy kolumnami. Zmiany zapisuja sie razem z formularzem.',
                        [],
                        'Modules.Crocomegamenu.Admin'
                    )
                . '</p>
                <div class="megamenu-builder__columns"></div>
                <input type="hidden" name="columns_json" id="columns_json" value="' . htmlspecialchars($json, ENT_QUOTES) . '">
            </div>
        ';
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJS(_PS_JS_DIR_ . 'vendor/Sortable.min.js');
        $this->addJS($this->module->getPathUri() . 'views/js/megamenu-builder.js');
        $this->addCSS($this->module->getPathUri() . 'views/css/megamenu-builder.css');
    }

    private function getSwitchValues(string $name): array
    {
        return [
            [
                'id' => $name . '_on',
                'value' => 1,
                'label' => $this->trans('Tak', [], 'Admin.Global'),
            ],
            [
                'id' => $name . '_off',
                'value' => 0,
                'label' => $this->trans('Nie', [], 'Admin.Global'),
            ],
        ];
    }


    protected function afterAdd($object)
    {
        $this->saveColumnsFromPost((int) $object->id);

        return parent::afterAdd($object);
    }

    protected function afterUpdate($object)
    {
        $this->saveColumnsFromPost((int) $object->id);

        return parent::afterUpdate($object);
    }

    private function saveColumnsFromPost(int $idItem): void
    {
        $json = trim((string) Tools::getValue('columns_json'));

        if ($json === '') {
            CrocoMegamenuItem::deleteColumns($idItem);

            return;
        }

        $columns = json_decode($json, true);

        if (!is_array($columns)) {
            $this->errors[] = $this->trans('Niepoprawny JSON w polu "Kolumny" - kolumny nie zostaly zapisane.', [], 'Modules.Crocomegamenu.Admin');

            return;
        }

        CrocoMegamenuItem::saveColumns($idItem, $columns);
    }

    public function ajaxProcessUpdatePositions(): void
    {
        $idItem = (int) Tools::getValue('id');
        $way = (int) Tools::getValue('way');

        $positions = Tools::getValue(substr($this->identifier, 3));

        if (!is_array($positions)) {
            return;
        }

        $page = (int) Tools::getValue('page');
        $pagination = (int) Tools::getValue('selected_pagination');
        $offset = $page > 1 ? ($page - 1) * $pagination : 0;

        foreach ($positions as $index => $value) {
            $parts = explode('_', $value);

            if (!isset($parts[2]) || (int) $parts[2] !== $idItem) {
                continue;
            }

            $newPosition = (int) $index + $offset;

            $item = new CrocoMegamenuItem($idItem);

            if (!Validate::isLoadedObject($item)) {
                echo '{"hasError" : true, "errors" : "Pozycja nie istnieje"}';

                return;
            }

            echo $item->updatePosition($way, (int) $newPosition)
                ? 'ok position ' . (int) $newPosition
                : '{"hasError" : true, "errors" : "Nie udalo sie zapisac kolejnosci"}';

            return;
        }
    }
}
