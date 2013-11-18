<?php
class API_StaticApiDispatcher extends StaticDispatcher
{
    private static $_Instance;

    public static function getInstance()
    {
        if(is_null(self::$_Instance))
            self::$_Instance = new self;

        return self::$_Instance;
    }

    public function registerNotFoundCallback()
    {
        function not_found()
        {
            echo API_StaticApiProvider::getInstance()->error(HTTP_NOT_FOUND, 'Method not Found');
            exit;
        }
    }

    public function loadRoutes()
    {
        dispatch_post('uploadImage', array(API_StaticApiProvider::getInstance(), 'uploadImage'));
        dispatch_post('deleteImage', array(API_StaticApiProvider::getInstance(), 'deleteImage'));
        dispatch_post('uploadAsset', array(API_StaticApiProvider::getInstance(), 'uploadAsset'));
        dispatch_post('deleteAsset', array(API_StaticApiProvider::getInstance(), 'deleteAsset'));

        return $this;
    }
}