<?php
//die(sha1(uniqid() . '_' . uniqid() . '_' . uniqid() . '_' . uniqid() . '_'. uniqid() . '_' . uniqid() . '_' . uniqid() . '_' . uniqid()));
//die(sha1('nahasapeemapetilon-revistalelfederal-123456789123456789'));

define('IS_PRODUCTION', false);
require_once '../libraries/loader.php';

StaticDispatcher::getInstance()
    ->loadRoutes()
    ->run();

