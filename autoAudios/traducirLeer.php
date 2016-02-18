<?php
//Este script traduce un texto dado y lo graba como audio
require_once 'clases/traductor.php';
require_once 'clases/TraductorGoogle.php';
require_once 'clases/lector.php';
require_once 'clases/LectorI.php';

$resultado['ok'] = FALSE;
$resultado['idioma'] = $_POST['idioma'];

if(isset($_POST['texto']) && isset($_POST['idioma']) && isset($_POST['nombre'])) {
    //Traducimos el texto si no está en castellano
    if($_POST['idioma'] != 'es') {
        $traductor = new TraductorGoogle();
        $textoTraducido = $traductor->traducirTexto($_POST['texto'], $_POST['idioma']);
    }
    else
        $textoTraducido = $_POST['texto'];
    //Lo leemos
var_dump($textoTraducido);

    if($textoTraducido) {
        $lector = new LectorI();
        $archivo = $lector->leer($textoTraducido, $_POST['idioma']);



        if($archivo)
            $resultado['ok'] = file_put_contents('audios/'.$_POST['nombre']."_{$_POST['idioma']}.mp3" , $archivo);
    }
}

echo json_encode($resultado);
