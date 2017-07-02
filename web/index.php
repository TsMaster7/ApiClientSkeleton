<?php

//entry point of client application

require __DIR__. '/../src/Utils/autoloader.php';

use Utils\Classes\Controllers\FrontController as FrontController;

$frontController = new FrontController();
$frontController->run();