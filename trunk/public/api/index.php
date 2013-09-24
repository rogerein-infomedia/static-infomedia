<?php
define('IS_PRODUCTION', false);
chdir('..');
require_once '../libraries/loader.php';

API_StaticApiDispatcher::getInstance()
    ->loadRoutes()
    ->run();
