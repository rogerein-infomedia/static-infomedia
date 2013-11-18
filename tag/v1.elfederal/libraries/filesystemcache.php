<?php
class FileSystemCache
{
    /**
     * @var FileSystemCache
     */
    private static $_Instance;

    private function __construct() { }

    public static function getInstance()
    {
        if(is_null(self::$_Instance))
            self::$_Instance = new self;

        return self::$_Instance;
    }

    public function store($key, $basepath, $value, $ext = '')
    {
        $key = md5($key);
        $cachePath = $this->getCachePath($key, true, $ext);
        $fullCachePath =  str_replace('\\', '/', rtrim($basepath, '/')). $cachePath;

	if(!file_exists(dirname($fullCachePath)))
		mkdir(dirname($fullCachePath), 0777, true);

        file_put_contents($fullCachePath, $value);

        return $cachePath;
    }

    private function getCachePath($key, $hashed = true, $ext = '')
    {
        if(!$hashed)
            $key = md5($key);

        $NESTING_LEVEL = 2;
        $path = '';
        for($i = 0; $i < $NESTING_LEVEL; $i++){
            $path .= '/' . $key[$i];
        }


        $cacheExt = '.cache';
        if(!empty($ext))
            $cacheExt = ".$ext" . $cacheExt;

        return $path . '/' . substr($key, $NESTING_LEVEL) . $cacheExt;
    }

    public function getCachePathFromString($key, $ext = '')
    {
        return $this->getCachePath($key, false, $ext);
    }
}