<?php

use app\Application;

$params = [];
$params['database'] = require_once(__DIR__.'/params/db.php');

spl_autoload_extensions(".php");
spl_autoload_register();

$app = new Application($params);
$app->run();

