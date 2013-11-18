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
        if(is_null(self::$_Config) && IS_PRODUCTION)
        {
            self::$_Config = apc_fetch('STATIC_ASSETS_CONFIG');
        }


        if(self::$_Config == false || is_null(self::$_Config))
        {
            include CONFIG_PATH . '/config.php';
            self::$_Config = $config;

            if(IS_PRODUCTION)
                apc_store('STATIC_ASSETS_CONFIG', self::$_Config);
        }
    }}