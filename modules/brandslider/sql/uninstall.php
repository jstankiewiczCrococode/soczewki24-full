<?php

$sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'brandslider`';

return Db::getInstance()->execute($sql);