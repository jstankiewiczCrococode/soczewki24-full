<?php

require_once dirname(__DIR__) . '/classes/BrandSlider.php';

class AdminBrandSliderController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'brandslider';
        $this->identifier = 'id_brandslider';
        $this->className = 'BrandSliderItem';
        $this->lang = false;
        $this->bootstrap = true;

        $this->addRowAction('edit');
        $this->addRowAction('delete');

   $this->_select = '
    m.name AS manufacturer_name,
    m.id_manufacturer AS manufacturer_logo
';

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m
                ON m.`id_manufacturer` = a.`id_manufacturer`
        ';

        $this->fields_list = [
            'id_brandslider' => [
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],

            'manufacturer_logo' => [
                'title' => 'Logo',
                'align' => 'center',
                'orderby' => false,
                'search' => false,
                'callback' => 'displayManufacturerLogo',
            ],

            'manufacturer_name' => [
                'title' => 'Marka',
                'filter_key' => 'm!name',
            ],

            'position' => [
                'title' => 'Pozycja',
                'type' => 'number',
            ],

            'active' => [
                'title' => 'Aktywna',
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'orderby' => false,
            ],
        ];

        parent::__construct();

        $this->syncManufacturers();
    }

public function displayManufacturerLogo($value, $row)
{
    $idManufacturer = (int) $value;

    if (!$idManufacturer) {
        return '';
    }

    $imageUrl = $this->context->link->getBaseLink()
        . 'img/m/' . $idManufacturer . '.jpg';

    return '<img src="' . htmlspecialchars(
        $imageUrl,
        ENT_QUOTES,
        'UTF-8'
    ) . '"
        alt=""
        style="max-width:120px; max-height:50px; object-fit:contain;">';
}

    private function syncManufacturers()
    {
        $manufacturers = Manufacturer::getManufacturers(
            $this->context->language->id,
            true
        );

        foreach ($manufacturers as $manufacturer) {
            $idManufacturer = (int) $manufacturer['id_manufacturer'];

            $exists = (int) Db::getInstance()->getValue(
                'SELECT `id_brandslider`
                 FROM `' . _DB_PREFIX_ . 'brandslider`
                 WHERE `id_manufacturer` = ' . $idManufacturer
            );

            if (!$exists) {
                Db::getInstance()->insert(
                    'brandslider',
                    [
                        'id_manufacturer' => $idManufacturer,
                        'position' => 0,
                        'active' => 0,
                        'date_add' => date('Y-m-d H:i:s'),
                        'date_upd' => date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => 'Marka w sliderze',
                'icon' => 'icon-tags',
            ],

            'input' => [
                [
                    'type' => 'select',
                    'label' => 'Marka',
                    'name' => 'id_manufacturer',
                    'required' => true,
                    'options' => [
                        'query' => Manufacturer::getManufacturers(
                            $this->context->language->id,
                            true
                        ),
                        'id' => 'id_manufacturer',
                        'name' => 'name',
                    ],
                ],

                [
                    'type' => 'text',
                    'label' => 'Pozycja',
                    'name' => 'position',
                    'required' => true,
                    'class' => 'fixed-width-sm',
                ],

                [
                    'type' => 'switch',
                    'label' => 'Aktywna',
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => 'Tak',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => 'Nie',
                            'id' => 'active_off',
                        ],
                    ],
                ],
            ],

            'submit' => [
                'title' => 'Zapisz',
            ],
        ];

        return parent::renderForm();
    }
}