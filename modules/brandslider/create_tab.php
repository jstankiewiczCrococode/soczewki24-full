<?php

require '/var/www/html/config/config.inc.php';

$tab = new Tab();

$tab->active = 1;
$tab->class_name = 'AdminBrandSlider';
$tab->module = 'brandslider';
$tab->id_parent = 34;
$tab->name = [];

foreach (Language::getLanguages(true) as $lang) {
    $tab->name[(int) $lang['id_lang']] = 'Slider marek';
}

echo $tab->add() ? 'TAB OK' : 'TAB NIEUDANA';