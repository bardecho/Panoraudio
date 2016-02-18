<?php

/**
 * Reduce the size of a image if needed.
 * @param string $path The path to the image.
 * @param string $originalName The original file name.
 * @param int $maxWidth The maximum width of the result image.
 * @param int $maxHeight The maximum height of the result image.
 * @param int $quality The jpg quality level.
 * @param boolean $cut Cut the bigger side of the image instead of resize.
 * @param boolean $show If TRUE, display the image instead of save to disk.
 * @param string $destination The destination path.
 * @return boolean TRUE on success or FALSE on failure.
 */
function limitImageSize($path, $originalName, $maxWidth, $maxHeight, $quality = 90, $cut = FALSE, $show = TRUE, $destination = NULL) {
    if (is_file($path) || substr($_GET['image'], 0, 7) == 'http://') {
        //Get the image size
        list($width, $height) = getimagesize($path);

        $multiplierW = $maxWidth / $width;
        $multiplierH = $maxHeight / $height;
        if ($multiplierH < 1 || $multiplierW < 1) {
            if ($cut) {
                //If you want to cut, resizing by smaller difference
                if ($multiplierH < $multiplierW)
                    $multiplier = $multiplierW;
                else
                    $multiplier = $multiplierH;
                $newWidth = $maxWidth;
                $newHeight = $maxHeight;
            }
            else {
                //No cut, resizing by bigger difference
                if ($multiplierH < $multiplierW)
                    $multiplier = $multiplierH;
                else
                    $multiplier = $multiplierW;
                $newWidth = $width * $multiplier;
                $newHeight = $height * $multiplier;
            }
        }
        else {
            //Size is ok
            $newWidth = $width;
            $newHeight = $height;
            $multiplier = 1;
        }

        //Resizing
        $ext = explode('.', $originalName);
        switch (strtolower($ext[count($ext) - 1])) {
            case 'jpeg':
            case 'jpg':
                $originalImage = imagecreatefromjpeg($path);
                break;

            case 'png':
                $originalImage = imagecreatefrompng($path);
                break;

            case 'gif':
                $originalImage = imagecreatefromgif($path);
                break;

            default:
                $originalImage = FALSE;
                $result = FALSE;
                break;
        }

        //Create the new one
        if ($originalImage) {
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width * $multiplier, $height * $multiplier, $width, $height);
            //Output
            if ($show) {
                $destination = NULL;
                header('Content-type: image/jpeg');
            }
            $result = imagejpeg($newImage, $destination, $quality);

            imagedestroy($originalImage);
            imagedestroy($newImage);
        }
    }
    else
        $result = FALSE;

    return $result;
}