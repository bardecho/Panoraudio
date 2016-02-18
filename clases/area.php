<?php
class Area {
    private $idArea, $area, $latitudIzquierdaInferior, $longitudIzquierdaInferior, 
            $latitudDerechaSuperior, $longitudDerechaSuperior;
    
    public function __construct($idArea, $area, $latitudIzquierdaInferior, $longitudIzquierdaInferior, $latitudDerechaSuperior, $longitudDerechaSuperior) {
        $this->setIdArea($idArea);
        $this->setArea($area);
        $this->setLatitudDerechaSuperior($latitudDerechaSuperior);
        $this->setLatitudIzquierdaInferior($latitudIzquierdaInferior);
        $this->setLongitudDerechaSuperior($longitudDerechaSuperior);
        $this->setLongitudIzquierdaInferior($longitudIzquierdaInferior);
    }
    
    public function getIdArea() {
        return $this->idArea;
    }

    public function getArea() {
        return $this->area;
    }

    public function getLatitudIzquierdaInferior() {
        return $this->latitudIzquierdaInferior;
    }

    public function getLongitudIzquierdaInferior() {
        return $this->longitudIzquierdaInferior;
    }

    public function getLatitudDerechaSuperior() {
        return $this->latitudDerechaSuperior;
    }

    public function getLongitudDerechaSuperior() {
        return $this->longitudDerechaSuperior;
    }

    public function setIdArea($idArea) {
        $this->idArea = trim($idArea);
    }

    public function setArea($area) {
        $this->area = trim($area);
    }

    public function setLatitudIzquierdaInferior($latitudIzquierdaInferior) {
        $this->latitudIzquierdaInferior = (float)$latitudIzquierdaInferior;
    }

    public function setLongitudIzquierdaInferior($longitudIzquierdaInferior) {
        $this->longitudIzquierdaInferior = (float)$longitudIzquierdaInferior;
    }

    public function setLatitudDerechaSuperior($latitudDerechaSuperior) {
        $this->latitudDerechaSuperior = (float)$latitudDerechaSuperior;
    }

    public function setLongitudDerechaSuperior($longitudDerechaSuperior) {
        $this->longitudDerechaSuperior = (float)$longitudDerechaSuperior;
    }

    /**
     * Carga un area por su id.
     * @param string $idArea El id del area.
     * @return Area El area obtenida.
     */
    public static function cargar($idArea) {
        $resultado=FALSE;        

        try {
            $db=new DB();
            $idArea = $db->escapeData(array($idArea));
            $datos=$db->obtainData("select * from at_area where idArea='{$idArea[0]}'");
            if($datos['rows'] > 0) {
                //Tenemos el area
                $resultado = new Area($datos['data'][0]['idArea'], $datos['data'][0]['area'], 
                        $datos['data'][0]['latitudIzquierdaInferior'], $datos['data'][0]['longitudIzquierdaInferior'], 
                        $datos['data'][0]['latitudDerechaSuperior'], $datos['data'][0]['longitudDerechaSuperior']);
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Introduce un nuevo area en la base de datos.
     * @return int ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_ERROR
     */
    public function grabar() {
        $resultado['error']=ERROR_GENERICO;

        //Datos obligatorios
        if($this->idArea && $this->area) {
            try {
                $db=new DB();
                $info=$db->escapeData(array($this->idArea, $this->area));
                $datos=$db->alterData("insert into at_area (idArea, area, latitudIzquierdaInferior, longitudIzquierdaInferior, latitudDerechaSuperior, longitudDerechaSuperior)".
                    " values ('$info[0]', '$info[1]', '$this->latitudIzquierdaInferior', '$this->longitudIzquierdaInferior', '$this->latitudDerechaSuperior', '$this->longitudDerechaSuperior')");
                    
                if($datos['rows'] > 0) {
                    $resultado['error']=ERROR_NO_ERROR;
                }
                else {
                    $resultado['error']=ERROR_GENERICO;
                }
            }
            catch(Exception $ex) {
                $resultado['error']=ERROR_GENERICO;
            }
        }
        else {
            $resultado['error']=ERROR_FALTA_DATO;
        }

        return $resultado['error'];
    }
    
    /**
     * Borra un area.
     * @param string $idArea
     * @return boolean
     */
    public static function borrar($idArea) {
        $resultado=FALSE;
        
        try {
            $db=new DB();
            $info=$db->escapeData(array($this->idArea));
            
            $datos=$db->alterData("delete from at_area where idArea = '$info[0]'");
            if($datos['rows'] > 0) {
                $resultado=TRUE;
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }

    /**
     * Devuelve todos los areas.
     * @param boolean $ordenados El campo clave será el idArea.
     * @return array
     */
    public static function listar($ordenados = FALSE) {
        $resultado=FALSE;
        
        try {
            $db=new DB();
            $datos=$db->obtainData("select * from at_area");
            if($datos['rows'] > 0) {
                foreach($datos['data'] as $numerico => $data) {
                    //Tenemos los areas
                    if($ordenados) {
                        $clave = $data['idArea'];
                    }
                    else {
                        $clave = $numerico;
                    }
                    
                    $resultado[$clave]=new Area($data['idArea'], $data['area'], 
                            $data['latitudIzquierdaInferior'], $data['longitudIzquierdaInferior'], 
                            $data['latitudDerechaSuperior'], $data['longitudDerechaSuperior']);
                }
            }
        }
        catch(Exception $ex) { }
        
        return $resultado;
    }
}
