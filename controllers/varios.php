<?php
class Varios {
    /**
     * Lista categorías o idiomasAudio.
     * @param string $tipo La opción deseada (categorias, idiomasAudio).
     * @return array error=(ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_INGRESO), 
     * items => el índice es el id y el valor el item.
     */
    public function listar($tipo) {
        $consulta=FALSE;
        $resultado['error']=ERROR_GENERICO;

        switch($tipo) {
            case 'idiomasAudio':
                $consulta='select * from at_idiomaAudio';
                $selectorId='idIdiomaAudio';
                $selector='idioma';
                break;
            
            case 'categorias':
                $consulta='select * from at_categoria';
                $selectorId='idCategoria';
                $selector='categoria';
                break;
        }
        
        if($consulta) {
            try {
                $db=new DB();
                $datos=$db->obtainData($consulta);
                if($datos['rows'] > 0) {
                    $resultado['error']=ERROR_NO_ERROR;
                    foreach($datos['data'] as $dato)
                        $resultado['items'][$dato[$selectorId]]=$dato[$selector];
                }
            }
            catch(Exception $ex) {
                $resultado['error']=ERROR_GENERICO;
            }
        }
        else $resultado['error']=ERROR_FALTA_DATO;
        
        return $resultado;
    }
    
    /**
     * Inserta un nuevo audio en la base de datos.
     * @param int $idCategoria El id de la categoría del nuevo audio.
     * @param int $idIdiomaAudio El id del idioma del nuevo audio.
     * @param float $latitud La latitud del audio.
     * @param float $longitud La longitud del audio.
     * @param base64 $sonido El archivo de sonido en formato base64.
     * @return string error -> (ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_ERROR)
     */
    public function nuevoAudio($idCategoria, $idIdiomaAudio, $latitud, $longitud, $sonido, $descripcion) {
        $resultado['error']=ERROR_GENERICO;
        $user=unserialize($_SESSION['user']);
        $archivoOK=FALSE;
        
        if($sonido) {
            //Procesamos el archivo de audio
            $datos=base64_decode($sonido, TRUE);
            if($datos && strlen($datos) < AUDIO_MAX) {
                $archivo=uniqid();
                $archivoOK=file_put_contents('sonido/'.$archivo, $datos);
            }
        }
        
        if($archivoOK) {
            $audio = new Audio(0, new Categoria($idCategoria, ''), $user->getIdUser(), $archivo, 
                    new IdiomaAudio($idIdiomaAudio, '', ''), $latitud, $longitud, 0, FALSE, $descripcion);
            $resultado['error']=$audio->grabar();
        }
        else
            $resultado['error']=ERROR_FALTA_DATO;
        
        return $resultado;
    }
    
    public function nuevaMarca($idCategoria, $idIdiomaAudio, $latitud, $longitud, $descripcion) {
        $resultado['error']=ERROR_GENERICO;
        $user=unserialize($_SESSION['user']);
        
        $audio = new Audio(0, new Categoria($idCategoria, ''), $user->getIdUser(), '', 
                new IdiomaAudio($idIdiomaAudio, '', ''), $latitud, $longitud, 1, FALSE, 1, $descripcion);
        $resultado['error']=$audio->grabar();
        
        return $resultado;
    }
}
