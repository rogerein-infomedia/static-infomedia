<?php
define('IS_PRODUCTION', true);
chdir('..');
require_once '../libraries/loader.php';

API_StaticApiDispatcher::getInstance()
    ->loadRoutes()
    ->run();
