<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function uninstallPscustomHomeSliderDatabase()
{
    $db = Db::getInstance();

    $queries = [
        'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'custom_home_slider_lang`',
        'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'custom_home_slider`',
    ];

    foreach ($queries as $query) {
        if (!$db->execute($query)) {
            return false;
        }
    }

    return true;
}