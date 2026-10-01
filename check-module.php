<?php
require '/var/www/html/config/config.inc.php';

$m = Module::getInstanceByName('aboutcontent');

echo get_class($m) . PHP_EOL;
echo $m->name . PHP_EOL;
echo $m->displayName . PHP_EOL;
