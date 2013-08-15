<?php
class API_StaticApiDispatcher extends StaticDispatcher
{
    private static $_Instance;

    public static  function getInstance()
    {
        if(is_null(self::$_Instance))
            self::$_Instance = new self;

        return self::getInstance();
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
        dispatch_post('uploadAsset', array(API_StaticApiProvider::getInstance(), 'uploadAsset'));

        return $this;
    }

    public static function testUploadImage()
    {
        //    MongoDBWrapper::getMongoDBInstance()->remoteUser->insert(array(
        //        'name' => 'Revista El Federal',
        //        'username' => 'elfederal',
        //        'password' => '4ad5d1622908863a5e25512a6e6cffddaca738cb',
        //        'ownerName' => 'el-federal',
        //    ));

//        require_once ROOT_PATH . '/staticapi/StaticApi.php';
//        $api = new StaticApi('elfederal', 'nahasapeemapetilon-revistalelfederal-123456789123456789', '51ed6c5379cd3b1834000001');
//        $api->uploadImage('123456789', 'jose-cuervos','c:/server/web/static-infomedia/trunk/jpgs/016669177cd18f9b994f78dc619533a9.jpg');
    }

    public static function testUploadAsset()
    {
        //    MongoDBWrapper::getMongoDBInstance()->remoteUser->insert(array(
        //        'name' => 'Revista El Federal',
        //        'username' => 'elfederal',
        //        'password' => '4ad5d1622908863a5e25512a6e6cffddaca738cb',
        //        'ownerName' => 'el-federal',
        //    ));

//        require_once ROOT_PATH . '/staticapi/StaticApi.php';
//
//        $api = new StaticApi('elfederal', 'nahasapeemapetilon-revistalelfederal-123456789123456789', '51ed6c5379cd3b1834000001');
//        $api->uploadAsset('123456789', 'KORG','c:/server/web/static-infomedia/trunk/jpgs/AX5G_OM_EFG2_633661622631280000.pdf');
    }
}