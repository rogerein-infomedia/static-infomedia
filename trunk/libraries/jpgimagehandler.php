<?
class JPGImageHandler
{
    public static function compressImage($src)
    {
        shell_exec('jpegtran -copy none -progressive "' . $src . '" "' . $src . '"');
    }

    public static function saveImageProcessed($srcImage, $dstImage, $defaultImage, $width, $height, $backgroundColor)
    {
        if(!file_exists($srcImage))
            $srcImage = $defaultImage;

        self::getImageResampled($width, $height, $srcImage, $dstImage, $backgroundColor, true, 75);
        self::compressImage($dstImage);
    }

    public static function outputImage($path)
    {
        header('Pragma: public');
        header("Cache-Control: private");
        header('Expires: 0');
        header('Content-Type: image/jpg');
        header('Etag: "image-' . sha1($path) . '"');
        header("Last-Modified: ".gmstrftime("%a, %d %b %Y %T %Z", filemtime($path)));
        readfile($path);
    }

    protected static function getImageResampled($newWidth, $newHeight, $srcImage, $dstImage, $hexBackgroundColor, $useFullSize = true, $quality = 90)
    {
        $imageInfo = GetImageSize($srcImage);

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];

        if (is_null($newHeight) || is_null($newHeight)) {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

        //
        // Resample Size Calculation
        //
        $resampledSize = self::getImageSizeProportional($originalWidth, $originalHeight, $newWidth, $newHeight);

        $srcX = 0;
        $srcY = 0;
        $destX = 0;
        $destY = 0;

        if ($useFullSize) {
            $newImage = ImageCreateTrueColor($newWidth, $newHeight);

            //
            // Color Background & Center Calculation
            //
            list ($r, $g, $b) = sscanf("#$hexBackgroundColor", '#%2x%2x%2x');
            $color = imagecolorallocate($newImage, $r, $g, $b);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $color);
            $destX = $newWidth / 2 - $resampledSize[0] / 2;
            $destY = $newHeight / 2 - $resampledSize[1] / 2;
        }
        else
        {
            $newImage = ImageCreateTrueColor($resampledSize[0], $resampledSize[1]);
        }

        $type = substr(strrchr($imageInfo['mime'], '/'), 1);

        switch ($type)
        {
            case 'jpeg':
                $oldImage = ImageCreateFromJPEG($srcImage);
                imagecopyresampled($newImage, $oldImage, $destX, $destY, $srcX, $srcY, $resampledSize[0], $resampledSize[1], $originalWidth, $originalHeight);
                break;

            case 'png':
                $oldImage = ImageCreateFromPNG($srcImage);
                imagecopyresampled($newImage, $oldImage, $destX, $destY, $srcX, $srcY, $resampledSize[0], $resampledSize[1], $originalWidth, $originalHeight);
                break;

            case 'gif':
                $oldImage = ImageCreateFromGIF($srcImage);
                imagecopyresampled($newImage, $oldImage, $destX, $destY, $srcX, $srcY, $resampledSize[0], $resampledSize[1], $originalWidth, $originalHeight);
                break;
        }

        imagejpeg($newImage, $dstImage, $quality);
        imagedestroy($newImage);
    }

    /**
     * Returns the best size for the image, or the same in case the old size is lower than the new one.
     *
     * @static
     * @param  $originalWidth
     * @param  $originalHeight
     * @param  $newWidth
     * @param  $newHeight
     * @return array(0 => calculatedWidth, 1 => calculatedHeight);
     */
    protected static function getImageSizeProportional($originalWidth, $originalHeight, $newWidth, $newHeight)
    {
        if ($newWidth > $originalWidth && $newHeight > $originalHeight) {
            $rWidth = $originalWidth;
            $rHeight = $originalHeight;
        }
        elseif ($originalWidth >= $originalHeight)
        {
            $rHeight = $originalHeight * $newWidth / $originalWidth;
            $rWidth = $newWidth;
        }
        else
        {
            $rWidth = $originalWidth * $newHeight / $originalHeight;
            $rHeight = $newHeight;
        }

        return array($rWidth, $rHeight);
    }
}