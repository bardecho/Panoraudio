<?php
class Preferencia {
    private $idPreferencia, $idiomaAudio, $puntuacionMinima, $idUser, $categoria;

    public function __construct($idPreferencia=NULL, $idiomaAudio=NULL, $puntuacionMinima=NULL, $idUser=NULL, $categoria=NULL) {
        $this->setIdPreferencia($idPreferencia);
        $this->setIdiomaAudio($idiomaAudio);
        $this->setPuntuacionMinima($puntuacionMinima);
        $this->setIdUser($idUser);
        $this->setCategoria($categoria);
    }

    public function getIdPreferencia() {
        return $this->idPreferencia;
    }

    public function getIdiomaAudio() {
        return $this->idiomaAudio;
    }

    public function getPuntuacionMinima() {
        return $this->puntuacionMinima;
    }

    public function getIdUser() {
        return $this->idUser;
    }

    public function getCategoria() {
        return $this->categoria;
    }

    private function setIdPreferencia($idPreferencia) {
        $resultado=FALSE;

        if($idPreferencia == 0) {
            $this->idPreferencia=0;
            $resultado=TRUE;
        }
        elseif(intval($idPreferencia) != 0) {
            $this->idPreferencia=intval($idPreferencia);
            $resultado=TRUE;
        }

        return $resultado;
    }

    public function setIdiomaAudio($idiomaAudio) {
        $resultado=FALSE;

        if(is_array($idiomaAudio)) {
            $this->idiomaAudio=$idiomaAudio;
            $resultado=TRUE;
        }

        return $resultado;
    }

    public function setPuntuacionMinima($puntuacionMinima) {
        $resultado=FALSE;

        if($puntuacionMinima == 0) {
            $this->puntuacionMinima=0;
            $resultado=TRUE;
        }
        elseif(intval($puntuacionMinima) != 0) {
            $this->puntuacionMinima=intval($puntuacionMinima);
            $resultado=TRUE;
        }

        return $resultado;
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

    public function setCategoria($categoria) {
        $resultado=FALSE;

        if(is_array($categoria)) {
            $this->categoria=$categoria;
            $resultado=TRUE;
        }

        return $resultado;
    }
    
    /**
     * Carga una preferencia a partir del id de un Usuario.
     * @param int $idUser El id del usuario.
     * @return array 'preferencia' -> La preferencia del usuario, 'error' -> (ERROR_GENERICO, ERROR_NO_ERROR).
     */
    public static function cargar($idUser) {
        $resultado['error']=ERROR_GENERICO;  
        $idUser=intval($idUser);        

        try {
            $db=new DB();
            $datos=$db->obtainData("select * from at_preferencia where idUser = $idUser");
            $resultado['error']=ERROR_NO_ERROR;
            if($datos['rows'] > 0) 
                $resultado['preferencia']=new Preferencia($datos['data'][0]['idPreferencia'], 
                        IdiomaAudio::cargar(FALSE, $datos['data'][0]['idPreferencia']), $datos['data'][0]['puntuacionMinima'], 
                        $datos['data'][0]['idUser'], Categoria::cargar($datos['data'][0]['idPreferencia']));
            else {
                $resultado['preferencia']=new Preferencia();
                $resultado['preferencia']->setIdUser($idUser);
                $categorias = Categoria::listar();
                if($categorias['error'] == ERROR_NO_ERROR)
                    $resultado['preferencia']->setCategoria($categorias['categorias']);
                $idioma = cargarIdiomaAudio();
                if($idioma)
                    $resultado['preferencia']->setIdiomaAudio(array($idioma));
            }
        }
        catch(Exception $ex) {
            $resultado['error']=ERROR_GENERICO;
        }
        
        return $resultado;
    }
    
    /**
     * Graba o actualiza la preferencia actual.
     * @return boolean TRUE en caso correcto o FALSE si hay error.
     */
    public function actualizar() {
        $resultado=FALSE;
        
        try {
            $db=new DB();
            $datos=$db->alterData("replace into at_preferencia (idPreferencia, puntuacionMinima, idUser)".
                    " values ('$this->idPreferencia', $this->puntuacionMinima, $this->idUser)");
            if($this->idPreferencia == NULL) $this->idPreferencia=$datos['insert_id'];
            //Ahora colocamos las categorias
            $db->alterData("delete from at_prefCat where idPreferencia = $this->idPreferencia");
            $cat=0;
            foreach($this->categoria as $categoria) {
                $datosCat=$db->alterData("insert into at_prefCat (idPreferencia, idCategoria) values ($this->idPreferencia, {$categoria->getIdCategoria()})");
                $cat+=$datosCat['rows'];
            }
            //Ahora los idiomaAudio
            $db->alterData("delete from at_prefIdioma where idPreferencia = $this->idPreferencia");
            $idioma=0;
            foreach($this->idiomaAudio as $idiomaAudio) {
                $datosIdioma=$db->alterData("insert into at_prefIdioma (idIdiomaAudio, idPreferencia) values ({$idiomaAudio->getIdIdiomaAudio()}, $this->idPreferencia)");
                $idioma+=$datosIdioma['rows'];
            }
            
            if($datos['rows'] > 0 && $cat > 0 && $idioma > 0) $resultado=TRUE;
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }

        return $resultado;
    }
}
