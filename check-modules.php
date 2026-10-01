<?php
require '/var/www/html/config/config.inc.php';

$modules = Module::getModulesOnDisk();

foreach ($modules as $module) {
    if ($module->name === 'aboutcontent') {
        echo "ZNALEZIONO: " . $module->name . PHP_EOL;
        echo "CLASS: " . get_class($module) . PHP_EOL;
        echo "ACTIVE: " . (int)$module->active . PHP_EOL;
    }
}
