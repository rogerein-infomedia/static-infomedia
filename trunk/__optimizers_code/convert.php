<?php
$basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$files = scandir($basePath . 'jpgs/');

foreach($files as $file)
{
    if($file != '.' && $file != '..')
    {
        $imageObject = imagecreatefromjpeg($basePath . 'jpgs/' . $file);
        imagepng($imageObject, $basePath . 'pngs/'. $file . '.png', 9);
    }
}