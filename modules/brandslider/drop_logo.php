<?php

require '/var/www/html/config/config.inc.php';

$sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'brandslider`
        DROP COLUMN `logo`';

echo Db::getInstance()->execute($sql)
    ? 'LOGO USUNIETE'
    : 'BLAD: ' . Db::getInstance()->getMsgError();