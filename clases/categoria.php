<?php
class Categoria {
    private $idCategoria, $categoria;

    public function __construct($idCategoria, $categoria) {
        $this->setIdCategoria($idCategoria);
        $this->setCategoria($categoria);
    }

    public function getIdCategoria() {
        return $this->idCategoria;
    }

    public function getCategoria() {
        return $this->categoria;
    }

    private function setIdCategoria($idCategoria) {
        $resultado=FALSE;

        if($idCategoria == 0) {
            $this->idCategoria=0;
            $resultado=TRUE;
        }
        elseif(intval($idCategoria) != 0) {
            $this->idCategoria=intval($idCategoria);
            $resultado=TRUE;
        }

        return $resultado;
    }

    public function setCategoria($categoria) {
        $resultado=FALSE;

        if(is_scalar($categoria)) {
            $this->categoria=(string)$categoria;
            $resultado=TRUE;
        }

        return $resultado;
    }
    
    /**
     * Carga una categoría a partir del id de una preferencia o del id de un usuario.
     * @param int $idPreferencia El id de una preferencia.
     * @param int $idUser El id de un usuario.
     * @return Categoria La categoría cargada, si es un usuario será un array.
     */
    public static function cargar($idPreferencia=FALSE, $idUser=FALSE, $idCategoria=FALSE) {
        $resultado=FALSE;
        
        try {
            if($idPreferencia !== FALSE) {
                $idPreferencia=intval($idPreferencia);        

                $db=new DB();
                $datos=$db->obtainData("select * from at_categoria inner join at_prefCat using (idCategoria) where idPreferencia = $idPreferencia");
                if($datos['rows'] > 0)
                    foreach($datos['data'] as $dato)
                        $resultado[]=new Categoria($dato['idCategoria'], $dato['categoria']);
            }
            elseif($idUser !== FALSE) {
                $idUser=intval($idUser);

                $db=new DB();
                $datos=$db->obtainData("select at_categoria.idCategoria, categoria from at_categoria ".
                        "inner join at_prefCat using (idCategoria) inner join at_preferencia using (idPreferencia)".
                        " where idUser = $idUser");
                if($datos['rows'] > 0)
                    foreach($datos['data'] as $dato)
                        $resultado[]=new Categoria($dato['idCategoria'], $dato['categoria']);
            }
            elseif($idCategoria !== FALSE) {
                $idCategoria=intval($idCategoria);
                
                $db=new DB();
                $datos=$db->obtainData("select * from at_categoria where idCategoria = $idCategoria");
                if($datos['rows'] > 0)
                    $resultado=new Categoria($datos['data'][0]['idCategoria'], $datos['data'][0]['categoria']);
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Crea una consulta preparada para cargar una categoría.
     * @return BDPreparada La consulta para cargar una categoría.
     */
    public static function cargarPreparada() {
        try {
            $resultado=new BDPreparada('select * from at_categoria where idCategoria = ?');
            $resultado->meterParametros(array('idCategoria' => BDPreparada::INTEGER));
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Ejecuta la consulta preparada con cargarPreparada().
     * @param BDPreparada $BDPreparada La consulta que devuelve cargarPreparada().
     * @param int $idCategoria El id de la categoría que se quiere cargar.
     * @return Categoria La categoría solicitada.
     */
    public static function ejecutarPreparada($BDPreparada, $idCategoria) {
        $resultado=FALSE;
        
        try {
            $BDPreparada->rellenarParametros(array('idCategoria' => $idCategoria));
            $datos=$BDPreparada->obtenerDatos();
            if($datos['filas'] > 0) 
                $resultado=new Categoria($datos['datos'][0]['idCategoria'], $datos['datos'][0]['categoria']);
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    public static function listar($ordenadas=FALSE) {
        $resultado['error']=ERROR_GENERICO;

        try {
            $db=new DB();
            $datos=$db->obtainData('select * from at_categoria');
            if($datos['rows'] > 0) {
                $resultado['error']=ERROR_NO_ERROR;
                if($ordenadas)
                    foreach($datos['data'] as $dato)
                        $resultado['categorias'][$dato['idCategoria']]=new Categoria($dato['idCategoria'], $dato['categoria']);
                else
                    foreach($datos['data'] as $dato)
                        $resultado['categorias'][]=new Categoria($dato['idCategoria'], $dato['categoria']);
            }
        }
        catch(Exception $ex) {
            $resultado['error']=ERROR_GENERICO;
        }
        
        return $resultado;
    }
}
