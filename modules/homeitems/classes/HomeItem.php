<?php

class HomeItem extends ObjectModel
{
    public $id_homeitem;
    public $link;
    public $image;
    public $text;
    public $position;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'homeitems',
        'primary' => 'id_homeitem',
        'multilang' => false,
        'fields' => [
            'link' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isUrl',
                'required' => true,
                'size' => 2048,
            ],
            'image' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isFileName',
                'required' => true,
                'size' => 255,
            ],
            'text' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml',
                'required' => true,
                'size' => 255,
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