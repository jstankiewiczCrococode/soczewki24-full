<?php

require '/var/www/html/config/config.inc.php';

$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'brandslider` (
    `id_brandslider` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_manufacturer` INT UNSIGNED NOT NULL,
    `logo` VARCHAR(255) NOT NULL,
    `position` INT UNSIGNED NOT NULL DEFAULT 0,
    `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_brandslider`),
    KEY `idx_manufacturer` (`id_manufacturer`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

echo Db::getInstance()->execute($sql)
    ? 'TABELA OK'
    : 'TABELA NIEUTWORZONA';