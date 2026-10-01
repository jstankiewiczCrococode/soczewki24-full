<?php

require '/var/www/html/config/config.inc.php';

$link = new Link();

$manufacturer = new Manufacturer(1);

echo 'NAME: ' . $manufacturer->name . PHP_EOL;
echo 'IMAGE LINK: ' . $link->getManufacturerImageLink(
    $manufacturer->id,
    'manufacturer_default'
) . PHP_EOL;