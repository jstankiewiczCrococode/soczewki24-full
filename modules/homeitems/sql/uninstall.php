<?php

$sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'homeitems`';

return Db::getInstance()->execute($sql);