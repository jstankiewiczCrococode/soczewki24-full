<?php

$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'homeitems` (
    `id_homeitem` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `link` VARCHAR(2048) NOT NULL,
    `image` VARCHAR(255) NOT NULL,
    `text` VARCHAR(255) NOT NULL,
    `position` INT UNSIGNED NOT NULL DEFAULT 0,
    `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_homeitem`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

return Db::getInstance()->execute($sql);