<?php
/**
 * Cachea los puntos.
 */
class CachePuntos {
    private $consulta;

    public function __construct() {
        try {
            $this->consulta = new BDPreparada('SELECT resultado FROM `at_cachePuntos` WHERE idAudio = ? AND objeto = ?');
            $this->consulta->meterParametros(array('idAudio' => BDPreparada::INTEGER, 'objeto' => BDPreparada::INTEGER));
        }
        catch(Exception $ex) { }
    }

    /**
     * Devuelve la información del punto o FALSE.
     * @param int $idAudio
     * @param boolean $objeto
     * @return array
     */
    public function cargarPunto($idAudio, $objeto) {
        $resultado = FALSE;
        
        try {
            $this->consulta->rellenarParametros(array('idAudio' => $idAudio, 'objeto' => $objeto));
            $datos = $this->consulta->obtenerDatos();
            if($datos['filas'] > 0) {
                $resultado = unserialize($datos['datos'][0]['resultado']);
            }
        }
        catch(Exception $ex) {
            $resultado = FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Graba un punto en caché.
     * @param int $idAudio
     * @param boolean $objeto
     * @param array $datos
     * @return boolean
     */
    public function grabarPunto($idAudio, $objeto, $datos) {
        try {
            $db = new DB();
            $datosEscapados = $db->escapeData(array($idAudio, $objeto, serialize($datos)));
            $resultado = $db->alterData("INSERT INTO `at_cachePuntos` (idAudio, objeto, resultado) VALUES ('$datosEscapados[0]', '$datosEscapados[1]', '$datosEscapados[2]')");
            $resultado = (boolean)$resultado['rows'];
        }
        catch(Exception $ex) {
            $resultado = FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Elimina un punto para mantener la caché actualizada.
     * @param int $idAudio
     * @return boolean
     */
    public function eliminarPunto($idAudio) {
        try {
            $db = new DB();
            $datosEscapados = $db->escapeData(array($idAudio));
            $resultado = $db->alterData("DELETE FROM `at_cachePuntos` WHERE idAudio = '$datosEscapados[0]'");
            $resultado = (boolean)$resultado['rows'];
        }
        catch(Exception $ex) {
            $resultado = FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Actualiza el punto sin borrarlo para recalcular solamente lo cambiado. Si un parámetro es FALSE no se actualiza.
     * @param int $idAudio
     * @param int $positivos
     * @param int $negativos
     * @param int $descargas
     * @param mixed $rutas
     * @return boolean
     */
    public function actualizarPunto($idAudio, $positivos = FALSE, $negativos = FALSE, $descargas = FALSE, $rutas = FALSE) {
        $resultado = true;
        
        $this->eliminarPunto($idAudio);
        
        $puntoNoObjeto = $this->cargarPunto($idAudio, 0);
        if($puntoNoObjeto) {
            if($positivos !== FALSE) {
                $puntoNoObjeto[4] = $positivos;
            }
            if($negativos !== FALSE) {
                $puntoNoObjeto[5] = $negativos;
            }
            if($descargas !== FALSE) {
                $puntoNoObjeto[10] = $descargas;
            }
            if($rutas !== FALSE) {
                $puntoNoObjeto[12] = $rutas;
            }
            
            $resultado = $this->grabarPunto($idAudio, 0, $puntoNoObjeto);
        }
        
        $puntoObjeto = $this->cargarPunto($idAudio, 1);
        if($puntoObjeto) {
            if($positivos !== FALSE) {
                $puntoObjeto['positivos'] = $positivos;
            }
            if($negativos !== FALSE) {
                $puntoObjeto['negativos'] = $negativos;
            }
            if($descargas !== FALSE) {
                $puntoObjeto['descargas'] = $descargas;
            }
            if($rutas !== FALSE) {
                $puntoObjeto['rutas'] = $rutas;
            }

            $resultado = $this->grabarPunto($idAudio, 1, $puntoObjeto);
        }
        
        return $resultado;
    }
}
