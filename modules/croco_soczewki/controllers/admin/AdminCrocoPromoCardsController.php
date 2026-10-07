<?php
/**
 * Lista i formularz kart promocyjnych na stronie produktu.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'croco_soczewki/classes/CrocoPromoCard.php';

class AdminCrocoPromoCardsController extends ModuleAdminController
{
    private const IMAGE_MAX_SIZE = 2097152;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'croco_promo_card';
        $this->identifier = 'id_card';
        $this->className = 'CrocoPromoCard';
        $this->lang = true;
        $this->allow_export = false;
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';

        parent::__construct();

        $this->fields_list = [
            'id_card' => [
                'title' => $this->trans('ID', [], 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'title' => [
                'title' => $this->trans('Tytul', [], 'Modules.Crocosoczewki.Admin'),
                'filter_key' => 'b!title',
            ],
            'slot' => [
                'title' => $this->trans('Miejsce', [], 'Modules.Crocosoczewki.Admin'),
                'callback' => 'renderSlot',
                'filter_key' => 'a!slot',
                'orderby' => false,
                'search' => false,
            ],
            'position' => [
                'title' => $this->trans('Kolejnosc', [], 'Modules.Crocosoczewki.Admin'),
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!position',
                'search' => false,
            ],
            'active' => [
                'title' => $this->trans('Aktywna', [], 'Modules.Crocosoczewki.Admin'),
                'align' => 'center',
                'type' => 'bool',
                'active' => 'status',
                'ajax' => true,
                'orderby' => false,
                'search' => false,
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->trans('Usun zaznaczone', [], 'Modules.Crocosoczewki.Admin'),
                'confirm' => $this->trans('Usunac zaznaczone karty?', [], 'Modules.Crocosoczewki.Admin'),
                'icon' => 'icon-trash',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getSlotLabels(): array
    {
        return [
            CrocoPromoCard::SLOT_SIDEBAR => $this->trans('Prawa kolumna, nad cena', [], 'Modules.Crocosoczewki.Admin'),
            CrocoPromoCard::SLOT_DESCRIPTION => $this->trans('Opis produktu', [], 'Modules.Crocosoczewki.Admin'),
        ];
    }

    public function renderSlot(string $slot): string
    {
        return $this->getSlotLabels()[$slot] ?? $slot;
    }

    /**
     * @param array<string, string> $labels
     *
     * @return array<int, array{id: string, name: string}>
     */
    private function toOptions(array $labels): array
    {
        $options = [];

        foreach ($labels as $id => $name) {
            $options[] = ['id' => (string) $id, 'name' => $name];
        }

        return $options;
    }

    public function initPageHeaderToolbar(): void
    {
        $this->page_header_toolbar_btn['new'] = [
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->trans('Dodaj karte', [], 'Modules.Crocosoczewki.Admin'),
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
        /** @var CrocoPromoCard|false $card */
        $card = $this->loadObject(true);
        $selected = $card && $card->id ? $card->getCategoryIds() : [];

        $icons = ['' => $this->trans('Brak ikony', [], 'Modules.Crocosoczewki.Admin')];
        foreach (CrocoPromoCard::ICONS as $icon) {
            $icons[$icon] = $icon;
        }

        $inputs = [
            [
                'type' => 'text',
                'lang' => true,
                'label' => $this->trans('Tytul', [], 'Modules.Crocosoczewki.Admin'),
                'name' => 'title',
                'required' => true,
            ],
            [
                'type' => 'textarea',
                'lang' => true,
                'rows' => 3,
                'label' => $this->trans('Tekst', [], 'Modules.Crocosoczewki.Admin'),
                'name' => 'text',
            ],
            [
                'type' => 'text',
                'lang' => true,
                'label' => $this->trans('Tekst linku', [], 'Modules.Crocosoczewki.Admin'),
                'name' => 'link_label',
                'desc' => $this->trans('Link pojawia sie tylko, gdy wypelnione sa i tekst, i adres.', [], 'Modules.Crocosoczewki.Admin'),
            ],
            [
                'type' => 'text',
                'lang' => true,
                'label' => $this->trans('Adres linku', [], 'Modules.Crocosoczewki.Admin'),
                'name' => 'link_url',
                'desc' => $this->trans('Zaczyna sie od http://, https://, / albo #.', [], 'Modules.Crocosoczewki.Admin'),
            ],
            [
                'type' => 'select',
                'label' => $this->trans('Miejsce na stronie produktu', [], 'Modules.Crocosoczewki.Admin'),
                'name' => 'slot',
                'options' => ['query' => $this->toOptions($this->getSlotLabels()), 'id' => 'id', 'name' => 'name'],
            ],
            [
                'type' => 'select',
                'label' => $this->trans('Tlo karty', [], 'Modules.Crocosoczewki.Admin'),
                'name' => 'variant',
                'options' => ['query' => $this->toOptions([
                    'default' => $this->trans('Biale', [], 'Modules.Crocosoczewki.Admin'),
                    'subtle' => $this->trans('Kremowe', [], 'Modules.Crocosoczewki.Admin'),
                ]), 'id' => 'id', 'name' => 'name'],
            ],
            [
                'type' => 'select',
                'label' => $this->trans('Ikona', [], 'Modules.Crocosoczewki.Admin'),
                'name' => 'icon',
                'options' => ['query' => $this->toOptions($icons), 'id' => 'id', 'name' => 'name'],
            ],
            [
                'type' => 'select',
                'label' => $this->trans('Kolor ikony', [], 'Modules.Crocosoczewki.Admin'),
                'name' => 'icon_tone',
                'options' => ['query' => $this->toOptions([
                    'primary' => $this->trans('Bursztynowy', [], 'Modules.Crocosoczewki.Admin'),
                    'dark' => $this->trans('Ciemny', [], 'Modules.Crocosoczewki.Admin'),
                ]), 'id' => 'id', 'name' => 'name'],
            ],
            [
                'type' => 'file',
                'label' => $this->trans('Obraz', [], 'Modules.Crocosoczewki.Admin'),
                'name' => 'image',
                'desc' => $this->trans('Opcjonalny. JPG, PNG lub WebP, do 2 MB.', [], 'Modules.Crocosoczewki.Admin')
                    . ($card && $card->image
                        ? '<br><img src="' . Tools::safeOutput(CrocoPromoCard::getImageUrl($card->image)) . '" alt="" style="max-width:240px;margin-top:8px">'
                        : ''),
            ],
        ];

        if ($card && $card->image) {
            $inputs[] = [
                'type' => 'switch',
                'label' => $this->trans('Usun obraz', [], 'Modules.Crocosoczewki.Admin'),
                'name' => 'delete_image',
                'is_bool' => true,
                'values' => [
                    ['id' => 'delete_image_on', 'value' => 1, 'label' => $this->trans('Tak', [], 'Admin.Global')],
                    ['id' => 'delete_image_off', 'value' => 0, 'label' => $this->trans('Nie', [], 'Admin.Global')],
                ],
            ];
        }

        $inputs[] = [
            'type' => 'categories',
            'label' => $this->trans('Kategorie', [], 'Modules.Crocosoczewki.Admin'),
            'name' => 'categoryBox',
            'desc' => $this->trans('Karta pokaze sie w zaznaczonych kategoriach i ich podkategoriach. Bez zaznaczenia - we wszystkich.', [], 'Modules.Crocosoczewki.Admin'),
            'tree' => [
                'id' => 'croco-promo-categories',
                'use_checkbox' => true,
                'use_search' => true,
                'selected_categories' => $selected,
                'root_category' => (int) Configuration::get('PS_HOME_CATEGORY'),
            ],
        ];
        $inputs[] = [
            'type' => 'text',
            'label' => $this->trans('Kolejnosc', [], 'Modules.Crocosoczewki.Admin'),
            'name' => 'position',
            'class' => 'fixed-width-sm',
            'desc' => $this->trans('Mniejsza liczba = wyzej. Puste przy dodawaniu: na koniec.', [], 'Modules.Crocosoczewki.Admin'),
        ];
        $inputs[] = [
            'type' => 'switch',
            'label' => $this->trans('Aktywna', [], 'Modules.Crocosoczewki.Admin'),
            'name' => 'active',
            'is_bool' => true,
            'values' => [
                ['id' => 'active_on', 'value' => 1, 'label' => $this->trans('Tak', [], 'Admin.Global')],
                ['id' => 'active_off', 'value' => 0, 'label' => $this->trans('Nie', [], 'Admin.Global')],
            ],
        ];

        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('Karta promocyjna', [], 'Modules.Crocosoczewki.Admin'),
                'icon' => 'icon-bullhorn',
            ],
            'input' => $inputs,
            'submit' => ['title' => $this->trans('Zapisz', [], 'Admin.Actions')],
        ];

        if (!$card || !$card->id) {
            $this->fields_value = ['active' => 1, 'delete_image' => 0];
        } else {
            $this->fields_value = ['delete_image' => 0];
        }

        return parent::renderForm();
    }

    /**
     * Walidacja przed zapisem: adresy linkow i obraz. Zrobiona tu, a nie w postImage(),
     * bo tam obiekt jest juz zapisany i blad zostawilby duplikat po ponownym wyslaniu.
     */
    public function processSave()
    {
        foreach (Language::getLanguages(false) as $lang) {
            $url = trim((string) Tools::getValue('link_url_' . (int) $lang['id_lang']));

            if (!CrocoPromoCard::isSafeUrl($url)) {
                $this->errors[] = $this->trans('Adres linku musi zaczynac sie od http://, https://, / albo #.', [], 'Modules.Crocosoczewki.Admin');
                break;
            }
        }

        if (!empty($_FILES['image']['tmp_name'])) {
            $error = ImageManager::validateUpload($_FILES['image'], self::IMAGE_MAX_SIZE);

            if ($error) {
                $this->errors[] = $error;
            }
        }

        if ($this->errors) {
            $this->display = Tools::getValue($this->identifier) ? 'edit' : 'add';

            return false;
        }

        return parent::processSave();
    }

    /**
     * Wolane przez AdminController po zapisie obiektu (add i update).
     *
     * @param int $id
     */
    protected function postImage($id)
    {
        $card = new CrocoPromoCard((int) $id);

        if (!Validate::isLoadedObject($card)) {
            return true;
        }

        $changed = false;

        if ((int) Tools::getValue('delete_image') && $card->image) {
            $card->deleteImageFile();
            $card->image = '';
            $changed = true;
        }

        if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $dir = CrocoPromoCard::getImageDir();

            if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                $this->errors[] = $this->trans('Nie mozna utworzyc katalogu na obrazy.', [], 'Modules.Crocosoczewki.Admin');

                return true;
            }

            $extension = strtolower(pathinfo((string) $_FILES['image']['name'], PATHINFO_EXTENSION));
            $name = 'card-' . (int) $id . '-' . bin2hex(random_bytes(4)) . '.' . $extension;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dir . $name)) {
                $this->errors[] = $this->trans('Nie udalo sie zapisac obrazu.', [], 'Modules.Crocosoczewki.Admin');

                return true;
            }

            $card->deleteImageFile();
            $card->image = $name;
            $changed = true;
        }

        if ($changed) {
            $card->update();
        }

        return true;
    }

    protected function afterAdd($object)
    {
        return $this->saveCategories($object);
    }

    protected function afterUpdate($object)
    {
        return $this->saveCategories($object);
    }

    /**
     * @param CrocoPromoCard $object
     */
    private function saveCategories($object): bool
    {
        $object->setCategories((array) Tools::getValue('categoryBox', []));

        return true;
    }
}
