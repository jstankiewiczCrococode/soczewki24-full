<?php
require '/var/www/html/config/config.inc.php';

$m = Module::getInstanceByName('croco_megamenu');

if (!$m) {
    echo "MODULE_NOT_FOUND\n";
    exit(1);
}

var_dump($m);
echo "LAST ERROR:\n";
var_dump($m->getErrors());
