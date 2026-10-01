<?php

require '/var/www/html/config/config.inc.php';

echo 'Category class: ';
var_dump(class_exists('Category'));

echo 'DB prefix: ';
echo _DB_PREFIX_;
echo PHP_EOL;

$category = new Category(2, 1, 1);

echo 'Category ID: ';
var_dump($category->id);

echo 'Category name: ';
var_dump($category->name);

echo 'Parent ID: ';
var_dump($category->id_parent);

echo 'Shop: ';
var_dump($category->id_shop_list);
