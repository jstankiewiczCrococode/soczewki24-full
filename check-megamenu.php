<?php
require '/var/www/html/config/config.inc.php';

$m = Module::getInstanceByName('croco_megamenu');

var_dump([
    'module' => (bool) $m,
    'database' => $m ? $m->installDatabase() : false,
    'tab_parent' => (int) Tab::getIdFromClassName('AdminParentThemes'),
    'hook' => $m ? $m->registerHook('displayNav1') : false,
]);
