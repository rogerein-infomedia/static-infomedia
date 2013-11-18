<?php
define('IS_PRODUCTION', true);
require_once '../libraries/loader.php';

StaticDispatcher::getInstance()
    ->loadRoutes()
    ->run();

