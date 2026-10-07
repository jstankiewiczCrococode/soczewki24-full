<?php
/**
 * Nazwa pliku = wersja DOCELOWA modulu. Presta odpala go automatycznie
 * po podbiciu $this->version w klasie modulu.
 *
 * 1.0.1: karty promocyjne na PDP - tabele, zakladka w BO i hooki.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_1($module)
{
    // Pierwsze podejscie trzymalo dwie stale karty w ps_configuration - juz nieuzywane.
    foreach (['SALON', 'EXAM'] as $card) {
        foreach (['ENABLED', 'TITLE', 'TEXT', 'LINK_LABEL', 'LINK_URL'] as $field) {
            Configuration::deleteByName('CROCO_PROMO_' . $card . '_' . $field);
        }
    }

    return (bool) $module->installPromoCards();
}
