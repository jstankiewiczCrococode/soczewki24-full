<?php
/**
 * Przyklad migracji. Nazwa pliku = wersja DOCELOWA modulu.
 * Presta odpala go automatycznie po podbiciu $this->version w klasie modulu.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    return (bool) Db::getInstance()->execute(
        'ALTER TABLE `' . _DB_PREFIX_ . 'croco_soczewki_log`
         ADD COLUMN `id_customer` INT UNSIGNED NULL AFTER `id_shop`'
    );
}
