<?php
class Ruta {
    private $idRuta, $descripcion, $idsAudio, $idUser;
    
    function __construct($idRuta, $descripcion, $idsAudio, $idUser) {
        $this->idRuta = $idRuta;
        $this->descripcion = $descripcion;
        $this->idsAudio = $idsAudio;
        $this->idUser = $idUser;
    }
    
    public function getIdRuta() {
        return $this->idRuta;
    }

    public function setIdRuta($idRuta) {
        $this->idRuta = $idRuta;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
    
    public function getIdsAudio() {
        return $this->idsAudio;
    }
    
    public function setIdsAudio($idsAudio) {
        $this->idsAudio = $idsAudio;
    }


    /**
     * Carga una ruta a partir de su id.
     * @param int $idRuta El id de la ruta.
     * @return Ruta La ruta obtenida.
     */
    public static function cargar($idRuta) {
        $resultado=FALSE;        
        $idRuta=intval($idRuta);
        
        try {
            $db=new DB();
            $datos=$db->obtainData("select * from at_ruta left join at_punto using(idRuta) where idRuta=$idRuta order by idPunto");
            if($datos['rows'] > 0) {
                //Tenemos la ruta
                $puntos = array();
                foreach($datos['data'] as $punto) {
                    if($punto['idAudio'])
                        $puntos[] = $punto['idAudio'];
                }
                $resultado=new Ruta($datos['data'][0]['idRuta'], $datos['data'][0]['descripcion'], $puntos, $datos['data'][0]['idUser']);
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Carga las rutas de un usuario.
     * @param int $idUsuario El id del usuario.
     * @return array Un array de rutas.
     */
    public static function cargarPorUsuario($idUsuario) {
        $resultado=FALSE;        
        $idUsuario=intval($idUsuario);
        
        try {
            $db=new DB();
            $datosRutas=$db->obtainData("select * from at_ruta where idUser=$idUsuario");
            $datosPuntos=$db->obtainData("select * from at_ruta left join at_punto using(idRuta) where idUser=$idUsuario order by idPunto");
            if($datosRutas['rows'] > 0) {
                //Tenemos los puntos
                foreach($datosPuntos['data'] as $punto) {
                    if($punto['idAudio'])
                        $puntos[$punto['idRuta']][] = $punto['idAudio'];
                }
                
                //Tenemos las rutas
                foreach($datosRutas['data'] as $ruta) {
                    $resultado[] = new Ruta($ruta['idRuta'], $ruta['descripcion'], $puntos[$ruta['idRuta']], $ruta['idUser']);
                }
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Introduce una nueva ruta en la base de datos.
     * @return int ERROR_GENERICO, ERROR_NO_ERROR
     */
    public function grabar() {
        $resultado['error']=ERROR_GENERICO;

        try {
            $db = new DB();
            $info = $db->escapeData(array($this->descripcion));
            $idUser = intval($this->idUser);
            $datos = $db->alterData("insert into at_ruta (descripcion, idUser)".
                " values ('$info[0]', $idUser)");

            if($datos['rows'] > 0) {
                $this->idRuta = $datos['insert_id'];
                
                if(is_array($this->idsAudio) && count($this->idsAudio)) {
                    $consulta = '';
                    foreach($this->idsAudio as $idAudio) {
                        $idAudio = intval($idAudio);
                        $consulta .= "($this->idRuta, $idAudio),";
                    }
                    
                    if($consulta) {
                        $consulta = substr($consulta, 0, -1);
                    
                        $datos = $db->alterData("insert into at_punto (idRuta, idAudio) values $consulta");
                        if($datos['rows'] > 0)
                            $resultado['error']=ERROR_NO_ERROR;
                    }
                }
                else
                    $resultado['error']=ERROR_NO_ERROR;
            }
        }
        catch(Exception $ex) {
            $resultado['error']=ERROR_GENERICO;
        }

        return $resultado['error'];
    }
    
    /**
     * Devuelve todas las rutas.
     * @return array
     */
    public static function listar() {
        $resultado=FALSE;        

        try {
            $db=new DB();
            $datosRutas=$db->obtainData("select * from at_ruta");
            $datosPuntos=$db->obtainData("select * from at_ruta left join at_punto using(idRuta) order by idPunto");
            if($datosRutas['rows'] > 0) {
                //Tenemos los puntos
                foreach($datosPuntos['data'] as $punto) {
                    if($punto['idAudio'])
                        $puntos[$punto['idRuta']][] = $punto['idAudio'];
                }
                
                //Tenemos las rutas
                foreach($datosRutas['data'] as $ruta) {
                    $resultado[] = new Ruta($ruta['idRuta'], $ruta['descripcion'], $puntos[$ruta['idRuta']], $ruta['idUser']);
                }
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Borra una ruta.
     * @param int $idRuta
     * @return boolean
     */
    public static function borrar($idRuta) {
        $resultado=FALSE;
        $user=comprobarLogin();
        
        try {
            $db=new DB();
            $idRuta=intval($idRuta);
            $idUser=intval($user->getIdUser());
            
            $datos=$db->alterData("delete from at_ruta where idRuta=$idRuta and idUser=$idUser");
            if($datos['rows'] > 0) $resultado=TRUE;
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Devuelve los ids de ruta que pasan por el idAudio del parámetro.
     * @param int $idAudio
     * @return array
     */
    public static function idsPorAudio($idAudio) {
        $resultado=array();

        try {
            $db=new DB();
            $idAudio=intval($idAudio);
            
            $datos=$db->obtainData("select distinct idRuta from at_ruta left join at_punto using(idRuta) where idAudio = $idAudio");
            if($datos['rows'] > 0) 
                foreach($datos['data'] as $data)
                    $resultado[] = $data['idRuta'];
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Crea una consulta preparada para cargar los ids de ruta que pasan por el idAudio del parámetro.
     * @return BDPreparada La consulta para cargar un usuario.
     */
    public static function cargarPreparada() {
        try {
            $resultado=new BDPreparada('select distinct idRuta from at_ruta left join at_punto using(idRuta) where idAudio = ?');
            $resultado->meterParametros(array('idAudio' => BDPreparada::INTEGER));
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Ejecuta la consulta preparada con cargarPreparada().
     * @param BDPreparada $BDPreparada La consulta que devuelve cargarPreparada().
     * @param int $idAudio
     * @return array
     */
    public static function ejecutarPreparada($BDPreparada, $idAudio) {
        $resultado=array();
        
        try {
            $BDPreparada->rellenarParametros(array('idAudio' => $idAudio));
            $datos=$BDPreparada->obtenerDatos();
            if($datos['filas'] > 0) 
                foreach($datos['datos'] as $data)
                    $resultado[] = $data['idRuta'];
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
}