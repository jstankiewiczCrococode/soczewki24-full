<?php

$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'brandslider` (
    `id_brandslider` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_manufacturer` INT UNSIGNED NOT NULL,
    `position` INT UNSIGNED NOT NULL DEFAULT 0,
    `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_brandslider`),
    UNIQUE KEY `uniq_manufacturer` (`id_manufacturer`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

return Db::getInstance()->execute($sql);