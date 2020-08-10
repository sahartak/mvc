<?php
/* loading composer autoloader file*/
require dirname(__DIR__).'/vendor/autoload.php';

/* getting application configs*/
$configs = require dirname(__DIR__).'/configs/main.php';


/* initializing application instance */
$app = new \app\framework\classes\App($configs);
$app->run();



