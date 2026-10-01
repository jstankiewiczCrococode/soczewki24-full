<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function installPscustomHomeSliderDatabase()
{
    $db = Db::getInstance();

    $sql = [];

    $sql[] = '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'custom_home_slider` (
            `id_slide` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `image` VARCHAR(255) DEFAULT NULL,
            `url` VARCHAR(2048) DEFAULT NULL,
            `position` INT UNSIGNED NOT NULL DEFAULT 0,
            `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_slide`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;
    ';

    $sql[] = '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'custom_home_slider_lang` (
            `id_slide` INT UNSIGNED NOT NULL,
            `id_lang` INT UNSIGNED NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `button_text` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`id_slide`, `id_lang`),
            KEY `id_lang` (`id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;
    ';

    foreach ($sql as $query) {
        if (!$db->execute($query)) {
            return false;
        }
    }

    return true;
}