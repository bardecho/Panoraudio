<?php
//Este script coge el texto de una web dada y lo devuelve
require_once 'clases/texto.php';
require_once 'clases/TextoWiki.php';
require_once 'clases/TextoTurGalicia.php';

$respuesta['ok']= FALSE;
if(isset($_GET['url'])) {
    //Obtenemos el texto
    $texto = new TextoTurGalicia();
    $respuesta['texto'] = $texto->getTexto($_GET['url']);
    $respuesta['ok']=TRUE;
}

echo $_GET['callback'].'('.json_encode($respuesta).')';