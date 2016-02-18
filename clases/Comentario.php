<?php
class Comentario {
    private $idComentario, $idUser, $idAudio, $texto;
    
    public function __construct($idComentario, $idUser, $idAudio, $texto) {
        $this->idComentario = $idComentario;
        $this->idUser = $idUser;
        $this->texto = $texto;
        $this->idAudio = $idAudio;
    }

    public function getIdComentario() {
        return $this->idComentario;
    }

    public function getIdUser() {
        return $this->idUser;
    }

    public function getTexto() {
        return $this->texto;
    }
    
    public function getIdAudio() {
        return $this->idAudio;
    }

    public function setIdComentario($idComentario) {
        $this->idComentario = (int)$idComentario;
    }

    public function setIdUser($idUser) {
        $this->idUser = (int)$idUser;
    }

    public function setTexto($texto) {
        $this->texto = trim($texto);
    }
    
    public function setIdAudio($idAudio) {
        $this->idAudio = $idAudio;
    }

    /**
     * Carga un comentario a partir de su id.
     * @param int $idComentario
     * @return Comentario
     */
    public static function cargar($idComentario) {
        $resultado=FALSE;        
        $idComentario=intval($idComentario);
        
        try {
            $db=new DB();
            $datos=$db->obtainData("select * from at_comentario where idComentario=$idComentario");
            if($datos['rows'] > 0) {
                //Tenemos el comentario
                $resultado=new Comentario($datos['data'][0]['idComentario'], $datos['data'][0]['idUser'], $datos['data'][0]['idAudio'], $datos['data'][0]['texto']);
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Obtiene los comentarios para un audio.
     * @param int $idAudio
     * @return array
     */
    public static function cargarPorAudio($idAudio) {
        $resultado=FALSE;        
        $idAudio=intval($idAudio);
        
        try {
            $db=new DB();
            $datos=$db->obtainData("select * from at_comentario where idAudio=$idAudio");
            if($datos['rows'] > 0) {
                //Tenemos los comentario
                foreach($datos['data'] as $dato) {
                    $resultado[] = new Comentario($dato['idComentario'], $dato['idUser'], $dato['idAudio'], $dato['texto']);
                }
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Devuelve un array con la cantidad de comentarios para cada audio de un usuario.
     * @param int $idUsuario
     * @return array [idAudio => cantidad]
     */
    public static function contarComentarios($idUsuario) {
        $resultado=FALSE;        
        $idUsuario=intval($idUsuario);
        
        try {
            $db=new DB();
            $datos=$db->obtainData(
                    "SELECT a.idAudio, COUNT(*) as cantidad FROM at_comentario c INNER JOIN 
                        at_audio a USING (idAudio) 
                        WHERE a.idUser = $idUsuario GROUP BY idAudio");
            if($datos['rows'] > 0) {
                //Tenemos las cantidades
                foreach($datos['data'] as $dato) {
                    $resultado[$dato['idAudio']] = $dato['cantidad'];
                }
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Introduce un nuevo comentario en la base de datos.
     * @return int ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_ERROR
     */
    public function grabar() {
        $resultado['error']=ERROR_GENERICO;

        //Datos obligatorios
        if($this->idUser && $this->idAudio && $this->texto) {
            try {
                $db=new DB();
                $archivo=$db->escapeData(array($this->texto));
                $datos=$db->alterData("INSERT INTO at_comentario (idUser, idAudio, texto)".
                    " VALUES ($this->idUser, $this->idAudio, '$this->texto')");
                    
                if($datos['rows'] > 0) {
                    $resultado['error']=ERROR_NO_ERROR;
                    $this->idComentario = $datos['insert_id'];
                }
                else
                    $resultado['error']=ERROR_GENERICO;
            }
            catch(Exception $ex) {
                $resultado['error']=ERROR_GENERICO;
            }
        }
        else 
            $resultado['error']=ERROR_FALTA_DATO;

        return $resultado['error'];
    }
}
