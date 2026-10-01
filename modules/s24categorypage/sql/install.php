<?php

$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 's24_category_page` (
    `id_s24_category_page` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_category` INT UNSIGNED NOT NULL,
    `id_lang` INT UNSIGNED NOT NULL,
    `hero_background` VARCHAR(255) NULL,
    `hero_title` VARCHAR(255) NULL,
    `hero_text` TEXT NULL,
    `brands_title` VARCHAR(255) NULL,
    `content_title` VARCHAR(255) NULL,
    `content_text` MEDIUMTEXT NULL,
    PRIMARY KEY (`id_s24_category_page`),
    UNIQUE KEY `category_lang` (`id_category`, `id_lang`),
    KEY `id_category` (`id_category`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

return $sql;
