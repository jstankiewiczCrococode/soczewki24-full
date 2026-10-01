<?php

require '/var/www/html/config/config.inc.php';

$module = Module::getInstanceByName('brandslider');

if (!$module) {
    exit('MODUL NIE ZNALEZIONY');
}

echo $module->registerHook('displayBrandSlider')
    ? 'HOOK OK'
    : 'HOOK NIEUDANY';