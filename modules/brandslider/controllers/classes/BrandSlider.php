<?php

class BrandSliderItem extends ObjectModel
{
    public $id_brandslider;
    public $id_manufacturer;
    public $position;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'brandslider',
        'primary' => 'id_brandslider',
        'multilang' => false,

        'fields' => [
            'id_manufacturer' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],



            'position' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],

            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],

            'date_add' => [
                'type' => self::TYPE_DATE,
            ],

            'date_upd' => [
                'type' => self::TYPE_DATE,
            ],
        ],
    ];
}