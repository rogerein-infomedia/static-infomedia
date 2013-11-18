<?php
$config = array(
    'mongoDB' => array(
        'host' => '127.0.0.1',
        'dbname' => 'static_elfederal_PRODUCCION'
    ),

//    'env' => ENV_DEVELOPMENT,
    'env' => ENV_PRODUCTION,


    'supportedMimeTypes' => array(
        'imagen' => array(
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
        ),

        'audio' => array(
            'audio/mpeg' => 'mp3',
        ),

        'video' => array(
            'application/x-shockwave-flash' => 'swf',
            'video/x-flv' => 'flv',
        ),

        'documento' => array(
            'application/zip' => 'zip',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/rtf' => 'rtf',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.oasis.opendocument.text' => 'odt',
            'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template' => 'xltx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template' => 'dotx',
        ),
    ),

    'routes' => array(
//        'image' => '/:owner/imagen/[:size/:background|original]/i:id-:title.jpg',
        'image' => '/imagen/[:size/:background|original]/i:id-:title.jpg',
//        'asset' => '/:owner/:type/i:id-:title.:ext',
        'asset' => '/asset/:type/i:id-:title.:ext',
    ),

    'authLog' => true, // Loggeo de errores de autenticación y checkeos exitosos.
    'actionLog' => true // Loggeo de acciones
);