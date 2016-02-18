<?php
class IdiomaAudio {
    private $idIdiomaAudio, $idioma, $siglasIdioma;

    public function __construct($idIdiomaAudio, $idioma, $siglasIdioma) {
        $this->setIdIdiomaAudio($idIdiomaAudio);
        $this->setIdioma($idioma);
        $this->setSiglasIdioma($siglasIdioma);
    }

    public function getIdIdiomaAudio() {
        return $this->idIdiomaAudio;
    }

    public function getIdioma() {
        return $this->idioma;
    }
    
    public function getSiglasIdioma() {
        return $this->siglasIdioma;
    }

    public function setIdIdiomaAudio($idIdiomaAudio) {
        $resultado=FALSE;

        if($idIdiomaAudio == 0) {
            $this->idIdiomaAudio=0;
            $resultado=TRUE;
        }
        elseif(intval($idIdiomaAudio) != 0) {
            $this->idIdiomaAudio=intval($idIdiomaAudio);
            $resultado=TRUE;
        }

        return $resultado;
    }

    public function setIdioma($idioma) {
        $resultado=FALSE;

        if(is_scalar($idioma)) {
            $this->idioma=(string)$idioma;
            $resultado=TRUE;
        }

        return $resultado;
    }
    
    public function setSiglasIdioma($siglas) {
        $resultado=FALSE;

        if(is_scalar($siglas)) {
            $this->siglasIdioma=(string)$siglas;
            $resultado=TRUE;
        }

        return $resultado;
    }
    
    /**
     * Carga un IdiomaAudio o varios.
     * @param int $idIdiomaAudio La clave primaria del idiomaAudio.
     * @param int $idPreferencia La clave primaria de la preferencia de la que se desean los idiomaAudio. (Tiene preferencia sobre idIdiomaAudio)
     * @return IdiomaAudio Un objeto IdiomaAudio o un array de ellos.
     */
    public static function cargar($idIdiomaAudio, $idPreferencia=FALSE) {
        $resultado=FALSE;
        
        if($idPreferencia !== FALSE) {
            $idPreferencia=intval($idPreferencia);
            
            try {
                $db=new DB();
                $datos=$db->obtainData("select * from at_idiomaAudio inner join at_prefIdioma using(idIdiomaAudio) where idPreferencia = $idPreferencia");
                if($datos['rows'] > 0) 
                    foreach($datos['data'] as $dato)
                        $resultado[]=new IdiomaAudio($dato['idIdiomaAudio'], $dato['idioma'], $dato['siglasIdioma']);
            }
            catch(Exception $ex) {
                $resultado=FALSE;
            }
        }
        else {
            $idIdiomaAudio=intval($idIdiomaAudio);

            try {
                $db=new DB();
                $datos=$db->obtainData("select * from at_idiomaAudio where idIdiomaAudio = $idIdiomaAudio");
                if($datos['rows'] > 0) 
                    $resultado=new IdiomaAudio($datos['data'][0]['idIdiomaAudio'], $datos['data'][0]['idioma'], $datos['data'][0]['siglasIdioma']);
            }
            catch(Exception $ex) {
                $resultado=FALSE;
            }
        }
        
        return $resultado;
    }
    
    /**
     * Devuelve una consulta preparada para cargar un IdiomaAudio. El parámetro es idIdiomaAudio.
     * @return BDPreparada La consulta preparada.
     */
    public static function cargarPreparada() {
        try {
            $resultado=new BDPreparada('select * from at_idiomaAudio where idIdiomaAudio = ?');
            $resultado->meterParametros(array('idIdiomaAudio' => BDPreparada::INTEGER));
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Carga un idiomaAudio a partir de su id.
     * @param int $idIdiomaAudio El id del idiomaAudio.
     * @return IdiomaAudio El idiomaAudio obtenido.
     */
    public static function ejecutarPreparada($BDPreparada, $idIdiomaAudio) {
        $resultado=FALSE;
        
        try {
            $BDPreparada->rellenarParametros(array('idIdiomaAudio' => $idIdiomaAudio));
            $datos=$BDPreparada->obtenerDatos();
            if($datos['filas'] > 0) 
                $resultado=new IdiomaAudio($datos['datos'][0]['idIdiomaAudio'], $datos['datos'][0]['idioma'], $datos['data'][0]['siglasIdioma']);
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Lista idiomasAudio.
     * @return array error=(ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_INGRESO), 
     * idiomasAudio => Un array de IdiomasAudio.
     */
    public static function listar($ordenadas=FALSE) {
        $resultado['error']=ERROR_GENERICO;

        try {
            $db=new DB();
            $datos=$db->obtainData('select * from at_idiomaAudio order by idIdiomaAudio');
            if($datos['rows'] > 0) {
                $resultado['error']=ERROR_NO_ERROR;
                if($ordenadas)
                    foreach($datos['data'] as $dato)
                        $resultado['idiomasAudio'][$dato['idIdiomaAudio']]=new IdiomaAudio($dato['idIdiomaAudio'], $dato['idioma'], $dato['siglasIdioma']);
                else
                    foreach($datos['data'] as $dato)
                        $resultado['idiomasAudio'][]=new IdiomaAudio($dato['idIdiomaAudio'], $dato['idioma'], $dato['siglasIdioma']);
            }
        }
        catch(Exception $ex) {
            $resultado['error']=ERROR_GENERICO;
        }
        
        return $resultado;
    }
    
    /**
     * Carga un idiomaAudio por las siglas del idioma.
     * @param string $sigla
     * @return array error=(ERROR_NO_ERROR, ERROR_GENERICO), idiomaAudio => Un IdiomasAudio.
     */
    public static function cargarPorSiglas($sigla) {
        $resultado['error']=ERROR_GENERICO;

        try {
            $db=new DB();
            $sigla = $db->escapeData(array($sigla));
            $datos=$db->obtainData("select * from at_idiomaAudio where siglasIdioma = '{$sigla[0]}'");
            if($datos['rows'] > 0) {
                $resultado['error']=ERROR_NO_ERROR;
                $resultado['idiomaAudio']=new IdiomaAudio($datos['data'][0]['idIdiomaAudio'], 
                        $datos['data'][0]['idioma'], $datos['data'][0]['siglasIdioma']);
            }
        }
        catch(Exception $ex) {
            $resultado['error']=ERROR_GENERICO;
        }
        
        return $resultado;
    }
}