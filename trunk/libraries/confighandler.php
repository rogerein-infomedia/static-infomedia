<?php
class ConfigHandler
{
    private static $_Config;

    protected function __construct() { }

    public static function item($name)
    {
        self::load();

        if(isset(self::$_Config[$name]))
            return self::$_Config[$name];
        return false;
    }

    private static function load()
    {
        if(is_null(self::$_Config))
        {
            if(!(self::$_Config = apc_fetch('STATIC_ASSETS_CONFIG')) || !IS_PRODUCTION)
            {
                include CONFIG_PATH . '/config.php';
                self::$_Config = $config;
                apc_store('STATIC_ASSETS_CONFIG', self::$_Config);
            }
        }
    }
}