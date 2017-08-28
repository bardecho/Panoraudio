<?php
if(isset($_GET['cantidad']) && (int)$_GET['cantidad']) {
    $cantidad = (int)$_GET['cantidad'];
    
    if(is_file("img/temp/grupo_c{$cantidad}.png")) {
        //Ya está creada
        enviarImagen("img/temp/grupo_c{$cantidad}.png");
    }
    else {
        //Vamos a añadir el número de cantidad de agrupados y guardar la imagen en disco
        $imgBase = imagecreatefrompng('img/grupo_c.png');
        imageAlphaBlending($imgBase, true);
        imageSaveAlpha($imgBase, true);
        $color = imagecolorallocate($imgBase, 30, 118, 238);
        if($cantidad > 99) {
            $x = 11;
            $y = 18;
            $size = 7;
        }
        elseif($cantidad > 9) {
            $x = 12;
            $y = 19;
            $size = 9;
        }
        else {
            $x = 15;
            $y = 19;
            $size = 10;
        }
        imagettftext($imgBase, $size, 0, $x, $y, $color, 'img/mplus-1m-regular.ttf', $cantidad);
        imagepng($imgBase, "img/temp/grupo_c{$cantidad}.png", 9);
        imagedestroy($imgBase);

        enviarImagen("img/temp/grupo_c{$cantidad}.png");
    }
}
else {
    //Mandamos la imagen sin número
    enviarImagen('img/grupo.png');
}


/**
 * Envía una imagen que está en disco.
 * @param string $ruta
 */
function enviarImagen($ruta) {
    header('Content-Type: image/png');
    header('Content-Length: '.filesize($ruta));
    readfile($ruta);
    exit;
}
