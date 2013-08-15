<?php
set_time_limit(0);
$basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$files = scandir($basePath . 'pngs/');

echo "<pre>";
chdir('c:/image_optimizers');

echo "STARTED: ";
echo "<pre>";
foreach($files as $file)
{
    if($file != '.' && $file != '..')
    {
        $src = '"' . $basePath . 'pngs/' . $file . '"';
        $dest = '"' . $basePath . 'pngs-crushed/' . $file . '"';
        echo "\r\n ->" . 'pngcrush -rem -alla -brute -reduce ' . $src . ' ' . $dest . '';
        shell_exec('pngcrush -rem -alla -brute -reduce ' . $src . ' ' . $dest);
    }
}

echo "\r\n\r\n******READY******";