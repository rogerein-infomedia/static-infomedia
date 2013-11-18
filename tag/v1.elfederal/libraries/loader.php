<?php
$key = 'STATIC_ASSETS_CONSTANTS';

if (!apc_load_constants($key, true) || !IS_PRODUCTION)
{
    $rootPath = dirname(dirname(__FILE__));
    $appPath = $rootPath . '/public';

    apc_define_constants($key, array(
        'ROOT_PATH'         => $rootPath,
        'APPPATH'           => $appPath,
        'LIB_PATH'          => $rootPath . '/libraries',
        'CONFIG_PATH'       => $rootPath . '/config',
        'ASSETS_PATH'       => $appPath . '/assets',
        'MOD_IMAGES_PATH'   => $appPath . '/cache',

        'STORE_RELATIVE_PATH'        => '/:owner/assets',
        'CACHE_STORE_RELATIVE_PATH'  => '/:owner/cache',
        'DEFAULT_IMAGE_PATH'         => '/:owner/default.jpg',
    ), true);

}

require_once ROOT_PATH . '/limonade.php';

spl_autoload_register('staticLibraryAutoload');
function staticLibraryAutoload($file)
{
    require_once LIB_PATH . '/' . strtolower(str_replace('_', '/', $file)) . '.php';
}

