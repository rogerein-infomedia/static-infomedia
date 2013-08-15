<?php
set_time_limit(0);
$basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$files = scandir($basePath . 'jpgs/');

echo "<pre>";
chdir('c:/image_optimizers');

echo "STARTED: ";
echo "<pre>";
foreach($files as $file)
{
    if($file != '.' && $file != '..')
    {
        $src = '"' . $basePath . 'jpgs/' . $file . '"';
        $dest = '"' . $basePath . 'jpgs-optimized-progressive/' . $file . '"';
        $cmd = 'jpegtran -copy none -progressive ' . $src . ' ' . $dest;
        echo "\r\n ->" . $cmd;
        shell_exec($cmd);
    }
}

echo "\r\n\r\n******READY******";