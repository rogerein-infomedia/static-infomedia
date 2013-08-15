<?php
$config = array(
    'mongoDB' => array(
        'host' => '127.0.0.1',
        'dbname' => 'static'
    ),

    'env' => ENV_DEVELOPMENT,

    'supportedMimeTypes' => array(
        'imagen' => array(
            'image/jpeg' => 'jpg',
        ),

        'audio' => array(
            'audio/mpeg' => 'mp3',
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
            'image/jpeg' => 'jpg',
        ),
    ),

    'routes' => array(
        'image' => '/:owner/imagen/[:size/:background|original]/i:id-:title.jpg',
        'asset' => '/:owner/:type/i:id-:title.:ext',
    ),
);