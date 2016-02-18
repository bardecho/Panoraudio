<?php
class Puntuacion {
    private $idUser, $idAudio, $puntuacion;

    public function __construct($idUser, $idAudio, $puntuacion) {
        $this->setIdUser($idUser);
        $this->setIdAudio($idAudio);
        $this->setPuntuacion($puntuacion);
    }

    public function getIdUser() {
        return $this->idUser;
    }

    public function getIdAudio() {
        return $this->idAudio;
    }

    public function getPuntuacion() {
        return $this->puntuacion;
    }

    public function setIdUser($idUser) {
        $resultado=FALSE;

        if($idUser == 0) {
            $this->idUser=0;
            $resultado=TRUE;
        }
        elseif(intval($idUser) != 0) {
            $this->idUser=intval($idUser);
            $resultado=TRUE;
        }

        return $resultado;
    }

    public function setIdAudio($idAudio) {
        $resultado=FALSE;

        if($idAudio == 0) {
            $this->idAudio=0;
            $resultado=TRUE;
        }
        elseif(intval($idAudio) != 0) {
            $this->idAudio=intval($idAudio);
            $resultado=TRUE;
        }

        return $resultado;
    }

    public function setPuntuacion($puntuacion) {
        $this->puntuacion=intval($puntuacion);
        if($this->puntuacion < 0) $this->puntuacion=0;
        elseif($this->puntuacion > 1) $this->puntuacion=1;

        return TRUE;
    }
    
    /**
     * Carga una puntuación a partir de un idAudio.
     * @param int $idAudio El id del audio del que se desea la puntuación.
     * @return boolean TRUE en caso correcto o FALSE en caso de error.
     */
    public static function cargar($idAudio) {
        $idAudio=intval($idAudio);
        $resultado=FALSE;
        
        try {
            $db=new DB();
            $datos=$db->obtainData("select * from at_puntuacion where idAudio = $idAudio");
            if($datos['rows'] > 0) 
                foreach($datos['data'] as $dato)
                    $resultado[]=new Puntuacion($dato['idUser'], $dato['idAudio'], $dato['puntuacion']);
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }

        return $resultado;
    }
    
    /**
     * Devuelve una consulta preparada para cargar una puntuación. El parámetro es idAudio.
     * @return BDPreparada La consulta preparada.
     */
    public static function cargarPreparada() {
        try {
            $resultado=new BDPreparada('select * from at_puntuacion where idAudio = ?');
            $resultado->meterParametros(array('idAudio' => BDPreparada::INTEGER));
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Carga una puntuación a partir del idAudio.
     * @param int $idAudio El id del Audio.
     * @return Puntuacion La puntuacion del audio.
     */
    public static function ejecutarPreparada($BDPreparada, $idAudio) {
        $resultado=FALSE;
        
        try {
            $BDPreparada->rellenarParametros(array('idAudio' => $idAudio));
            $datos=$BDPreparada->obtenerDatos();
            if($datos['filas'] > 0) 
                foreach($datos['datos'] as $dato)
                    $resultado[]=new Puntuacion($dato['idUser'], $dato['idAudio'], $dato['puntuacion']);
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Graba la puntuación actual.
     * @return boolean TRUE en caso correcto o FALSE en caso contrario.
     */
    public function actualizar() {
        $resultado=FALSE;
        
        try {
            $db=new DB();
            $datos=$db->alterData("replace into at_puntuacion (idAudio, idUser, puntuacion) values ($this->idAudio, $this->idUser, $this->puntuacion)");
            if($datos['rows'] > 0) $resultado=TRUE;
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
}
