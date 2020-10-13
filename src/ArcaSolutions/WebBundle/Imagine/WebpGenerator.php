<?php

namespace ArcaSolutions\WebBundle\Imagine;

class WebpGenerator {

    function getWebpPath($path) {
        $re = '/^(.+\.)([a-z]+)?/m';
        $subst = '$1webp';

        return preg_replace($re, $subst, $path);
    }

    function createWebpFromImagePath($imagePath, $destPath) {
        $im = $this->imageCreateFromAny($imagePath);
        if ($im) {
            // Creates a webp version of that image
            imagewebp($im, $destPath);

            // Free up memory
            imagedestroy($im);
        }
        unset($im);
    }

    function imageCreateFromAny($filepath) {
        $type = exif_imagetype($filepath);
        $allowedTypes = array(
            1,  // [] gif
            2,  // [] jpg
            3,  // [] png
            6   // [] bmp
        );
        if (!in_array($type, $allowedTypes)) {
            unset($type,$allowedTypes);
            return false;
        }
        switch ($type) {
            case 1 :
                $imgif = imagecreatefromgif($filepath);
                if($imgif!==false){
                    $tmpFileDir = sys_get_temp_dir();
                    $tmpFileName = tempnam($tmpFileDir, 'webpgentmppng');
                    $im = imagejpeg($imgif,$tmpFileName);
                    if($im!==false) {
                        $im = imagecreatefromjpeg($tmpFileName);
                    }
                    imagedestroy($imgif);
                    unlink($tmpFileName);
                    unset($imgif,$tmpFileDir,$tmpFileName);
                }
            break;
            case 2 :
                $im = imagecreatefromjpeg($filepath);
            break;
            case 3 :
                $im = imagecreatefrompng($filepath);
            break;
            case 6 :
                $im = imagecreatefrombmp($filepath);
            break;
        }
        unset($type,$allowedTypes);
        return $im;
    }

}