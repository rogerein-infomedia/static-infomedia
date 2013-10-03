<?php
class StaticDispatcher
{
    private static $_Instance;

    protected function __construct()
    {
        function configure()
        {
            $env = ConfigHandler::item('env');
            option('env', $env);
            option('debug', $env == ENV_DEVELOPMENT);
        }

        $this->registerNotFoundCallback();
    }

    public static function getInstance()
    {
        if(is_null(self::$_Instance))
            self::$_Instance = new self;

        return self::$_Instance;
    }

    protected function registerNotFoundCallback()
    {
        function not_found()
        {
            StaticDispatcher::getInstance()->assetNotFound();
        }
    }

    public function assetNotFound()
    {
        header("HTTP/1.0 404 Not Found");
        exit;
    }

    public function loadRoutes()
    {
        dispatch('^/([a-zA-Z0-9_\-]+)/imagen/((\d+)x(\d+)/([0-9a-fA-F]{6})/i(\d+)\-([a-zA-Z0-9_\-]+))\.jpg', array($this, 'handleImageRequest'));
        dispatch('^/([a-zA-Z0-9_\-]+)/imagen/((\d+)x(\d+)/([0-9a-fA-F]{6})/i(\d+)\-([a-zA-Z0-9_\-]+))\.jpg', array($this, 'handleImageRequest'));
        dispatch('^/([a-zA-Z0-9_\-]+)/imagen/(original/i(\d+)\-([a-zA-Z0-9_\-]+))\.jpg$', array($this, 'handleOriginalImageRequest'));

        foreach(ConfigHandler::item('supportedMimeTypes') as $type => $formats)
        {
            $formats = implode('|', array_values($formats));
            dispatch('^/([a-zA-Z0-9_\-]+)/asset/((' . $type .')/i(\d+)\-([a-zA-Z0-9_\-\.]+)\.(' . $formats . '))$', array($this, 'handleAssetRequest'));
        }

        return $this;
    }

    public function run()
    {
        try
        {
            run();
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function handleImageRequest($owner, $url, $width, $height, $background, $remoteId, $trash)
    {
        $relativePath = str_replace(':owner', $owner, CACHE_STORE_RELATIVE_PATH);
        $absolutPath = ASSETS_PATH . $relativePath;
        $cacheFile = $absolutPath . FileSystemCache::getInstance()->getCachePathFromString($url);

        if(file_exists($cacheFile))
        {
            JPGImageHandler::outputImage($cacheFile);
            exit;
        }
        else
        {
            if(($remoteUser = Config_OwnerConfig::load($owner)))
            {
                $image = MongoDBWrapper::getMongoDBInstance()->image->findOne(array(
                    'owner' => $remoteUser->id,
                    'refId' => (int)$remoteId
                ));

                if($image)
                {
                    $cacheFileName = FileSystemCache::getInstance()->store($url, $absolutPath, '<!-- NONE -->');

                    JPGImageHandler::saveImageProcessed(
                        ASSETS_PATH . '/' . $image['path'],
                        $cacheFile,
                        ASSETS_PATH . str_replace(':owner', $owner, DEFAULT_IMAGE_PATH),
                        $width,
                        $height,
                        $background
                    );

                    MongoDBWrapper::getMongoDBInstance()->image->update(
                        array('_id' => $image['_id']),
                        array('$addToSet' => array(
                            'thumbs' => array(
                                'path' => $relativePath . $cacheFileName,
                                'size' => array((int)$width, (int)$height),
                                'background' => $background))
                        ));

                    JPGImageHandler::outputImage($cacheFile);
                    exit;
                }
            }

            $this->assetNotFound();
        }
    }

    public function handleOriginalImageRequest($owner, $url, $remoteId, $title)
    {
        $relativePath = str_replace(':owner', $owner, STORE_RELATIVE_PATH);
        $cacheFile = ASSETS_PATH . $relativePath . FileSystemCache::getInstance()->getCachePathFromString($url);

        if(file_exists($cacheFile))
        {
            JPGImageHandler::outputImage($cacheFile);
            exit;
        }
        else
        {
            $this->assetNotFound();
        }
    }

    public function handleAssetRequest($owner, $assetURL, $type, $remoteId, $title, $ext)
    {
        $relativePath = str_replace(':owner', $owner, STORE_RELATIVE_PATH);
        $cacheFile = ASSETS_PATH . $relativePath . FileSystemCache::getInstance()->getCachePathFromString($assetURL, $ext);

        if(file_exists($cacheFile))
        {
            $mimeTypes = ConfigHandler::item('supportedMimeTypes');
            $mimeTypes = array_flip($mimeTypes[$type]);

            header('Pragma: public');
            header("Cache-Control: private");
            header('Expires: 0');
            header('Content-Type: ' . $mimeTypes[$ext]);
            header('Etag: "asset-' . sha1($assetURL) . '"');
            header("Last-Modified: " . gmstrftime("%a, %d %b %Y %T %Z", filemtime($cacheFile)));
            readfile($cacheFile);
        }
        else
        {
            $this->assetNotFound();
        }
    }
}