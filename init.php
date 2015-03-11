<?php

use app\DbConnector;
use app\helpers\AppConstants;

$params = [];
$params['database'] = require_once(__DIR__.'/params/db.php');

spl_autoload_extensions(".php");
spl_autoload_register();

$sql[0] = <<< SQL0
  CREATE DATABASE IF NOT EXISTS `mius` /*!40100 DEFAULT CHARACTER SET utf8 */;
SQL0;
$sql[1] = <<< SQL1
  CREATE TABLE IF NOT EXISTS `clients` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `create_time` int(11) DEFAULT '0',
    `name` varchar(200) DEFAULT NULL,
    `person` varchar(200) DEFAULT NULL,
    `phone` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
SQL1;
$sql[2] = <<< SQL2
  CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `format` int(10) unsigned NOT NULL,
  `order_cars` int(10) unsigned NOT NULL,
  `order_time` int(10) unsigned NOT NULL,
  `finish_time` int(10) unsigned DEFAULT NULL,
  `pay` tinyint(3) unsigned DEFAULT NULL,
  `deprecate` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`,`client_id`),
  KEY `second` (`format`),
  KEY `therd` (`create_time`,`format`,`order_cars`,`order_time`,`finish_time`,`pay`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
SQL2;

$sql[3] = <<< SQL3
CREATE TABLE IF NOT EXISTS `params` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
SQL3;

$db = new DbConnector($params['database']);
foreach ($sql as $sql_txt){
  $result = $db->query($sql_txt);
  echo "Result ".implode("\r\n", $db->_error_list)."\r\n";
}

$const = new AppConstants($db);
$const->car_count = 10;
$const->pay3 = 3000;
$const->pay4 = 1200;
$const->pay5 = 800;

    