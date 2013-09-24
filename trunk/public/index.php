<?php
define('IS_PRODUCTION', false);
require_once '../libraries/loader.php';

StaticDispatcher::getInstance()
    ->loadRoutes()
    ->run();

