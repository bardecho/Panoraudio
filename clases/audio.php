<?php
class Audio {
    private $idAudio, $categoria, $idUser, $archivo, $idiomaAudio, $latitud, $longitud, $bloqueado, 
            $puntuacion, $marca, $descripcion, $descargas, $idArea;

    public function __construct($idAudio, $categoria, $idUser, $archivo, $idiomaAudio, $latitud, $longitud, 
            $bloqueado, $puntuacion, $marca=0, $descripcion='', $descargas=0, $idArea = '') {
        $this->setIdAudio($idAudio);
        $this->setCategoria($categoria);
        $this->setIdUser($idUser);
        $this->setArchivo($archivo);
        $this->setIdiomaAudio($idiomaAudio);
        $this->setLatitud($latitud);
        $this->setLongitud($longitud);
        $this->setBloqueado($bloqueado);
        $this->setPuntuacion($puntuacion);
        $this->setMarca($marca);
        $this->setDescripcion($descripcion);
        $this->setDescargas($descargas);
        $this->setIdArea($idArea);
    }

    public function getIdAudio() {
        return $this->idAudio;
    }

    public function getCategoria() {
        return $this->categoria;
    }

    public function getIdUser() {
        return $this->idUser;
    }

    public function getArchivo() {
        return $this->archivo;
    }

    public function getIdiomaAudio() {
        return $this->idiomaAudio;
    }

    public function getLatitud() {
        return $this->latitud;
    }

    public function getLongitud() {
        return $this->longitud;
    }

    public function getBloqueado() {
        return $this->bloqueado;
    }

    public function getPuntuacion() {
        return $this->puntuacion;
    }
    
    public function getMarca() {
        return $this->marca;
    }
    
    public function getDescripcion() {
        return $this->descripcion;
    }
    
    public function getDescargas() {
        return $this->descargas;
    }
    
    public function getIdArea() {
        return $this->idArea;
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

    public function setCategoria($categoria) {
        $resultado=FALSE;

        if(is_a($categoria, 'Categoria')) {
            $this->categoria=$categoria;
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

    public function setArchivo($archivo) {
        $resultado=FALSE;

        if(is_scalar($archivo)) {
            $this->archivo=(string)$archivo;
            $resultado=TRUE;
        }

        return $resultado;
    }

    public function setIdiomaAudio($idiomaAudio) {
        $resultado=FALSE;

        if(is_a($idiomaAudio, 'IdiomaAudio')) {
            $this->idiomaAudio=$idiomaAudio;
            $resultado=TRUE;
        }

        return $resultado;
    }

    public function setLatitud($latitud) {
        $resultado=FALSE;

        if($latitud == 0) {
            $this->latitud=0;
            $resultado=TRUE;
        }
        elseif(floatval($latitud) != 0) {
            $this->latitud=floatval($latitud);
            $resultado=TRUE;
        }

        return $resultado;
    }

    public function setLongitud($longitud) {
        $resultado=FALSE;

        if($longitud == 0) {
            $this->longitud=0;
            $resultado=TRUE;
        }
        elseif(floatval($longitud) != 0) {
            $this->longitud=floatval($longitud);
            $resultado=TRUE;
        }

        return $resultado;
    }

    public function setBloqueado($bloqueado) {
        if($bloqueado) $this->bloqueado=1;
        else $this->bloqueado=0;

        return TRUE;
    }

    public function setPuntuacion($puntuacion) {
        $resultado=FALSE;

        if(is_array($puntuacion)) {
            $this->puntuacion=$puntuacion;
            $resultado=TRUE;
        }

        return $resultado;
    }
    
    public function setMarca($marca) {
        if($marca) $this->marca=1;
        else $this->marca=0;

        return TRUE;
    }
    
    public function setDescripcion($descripcion) {
        $resultado=FALSE;

        if(is_scalar($descripcion)) {
            $this->descripcion=(string)$descripcion;
            $resultado=TRUE;
        }

        return $resultado;
    }
    
    public function setDescargas($descargas) {
        $resultado=FALSE;

        if($descargas == 0) {
            $this->descargas=0;
            $resultado=TRUE;
        }
        elseif(intval($descargas) != 0) {
            $this->descargas=intval($descargas);
            $resultado=TRUE;
        }

        return $resultado;
    }
    
    public function setIdArea($idArea) {
        $resultado=FALSE;

        if(is_scalar($idArea)) {
            $this->idArea=(string)$idArea;
            $resultado=TRUE;
        }

        return $resultado;
    }
    
    /**
     * Encuentra los audios al alcance del usuario.
     * @param float $latitud La latitud del individuo.
     * @param float $longitud La longitud del individuo.
     * @param Array $categorias Un array de Categorías localizables.
     * @param int $puntuacionMinima La puntuación mínima requerida para el audio.
     * @return Array error=(ERROR_NO_ERROR, ERROR_GENERICO), audios -> los audios
     */
    public static function localizarAudios($latitud, $longitud, $categorias, $idiomasAudio, $puntuacionMinima=0) {
        $resultado['error']=ERROR_GENERICO;
        $latitud=floatval($latitud);
        $longitud=floatval($longitud);
        //Concatenamos las categorías
        $idCat='';
        foreach($categorias as $categoria) {
            $idCat.=intval($categoria->getIdCategoria()).', ';
        }
        $idCat=substr($idCat, 0, -2);
        //Ahora los idiomaAudio
        $idIdiomaAudio='';
        foreach($idiomasAudio as $idiomaAudio) {
            $idIdiomaAudio.=intval($idiomaAudio->getIdIdiomaAudio()).', ';
        }
        $idIdiomaAudio=substr($idIdiomaAudio, 0, -2);
        
        try {
            $db=new DB();
            $datos=$db->obtainData("select * from at_audio where latitud between $latitud-0.0031 and $latitud+0.0031 ".
                    "and longitud between $longitud-0.0031 and $longitud+0.0031 and bloqueado = 0 and marca = 0 and idCategoria in ($idCat) and idIdiomaAudio in ($idIdiomaAudio)");
            if($datos['rows'] > 0) {
                $resultado['error']=ERROR_NO_ERROR;
                //Preparamos las consultas
                $consultaCategoria=Categoria::cargarPreparada();
                $consultaIdiomaAudio=IdiomaAudio::cargarPreparada();
                $consultaPuntuacion=Puntuacion::cargarPreparada();
                //Tenemos los audios
                foreach($datos['data'] as $dato) {
                    //Comprobamos que los puntos llegan al mínimo deseado
                    $puntos=Puntuacion::ejecutarPreparada($consultaPuntuacion, $dato['idAudio']);
                    if($puntos !== FALSE) {
                        $cantidad=0;
                        foreach($puntos as $punto) {
                            $cantidad+=$punto->getPuntuacion();
                        }
                        if($cantidad/count($puntos) < $puntuacionMinima) continue;
                    }

                    $resultado['audios'][]=new Audio($dato['idAudio'], Categoria::ejecutarPreparada($consultaCategoria, $dato['idCategoria']), 
                            $dato['idUser'], $dato['archivo'], IdiomaAudio::ejecutarPreparada($consultaIdiomaAudio, $dato['idIdiomaAudio']), 
                            $dato['latitud'], $dato['longitud'], 0, Puntuacion::ejecutarPreparada($consultaPuntuacion, $dato['idAudio']), 
                            $dato['descripcion'], Audio::contarDescargas($dato['idAudio']), $dato['idArea']);
                }
            }
            else {
                $resultado['error']=ERROR_NO_ERROR;
            }
        }
        catch(Exception $ex) {
            $resultado['error']=ERROR_GENERICO;
        }
        
        return $resultado;
    }
    
    /**
     * Calcula la media de puntos de este audio.
     * @return array La media de 'puntos' de este audio y la 'cantidadValoraciones'.
     */
    public function getPuntos() {
        $resultado=array('puntos' => 0, 'cantidadValoraciones' => 0);
        
        if(is_array($this->puntuacion)) {
            $cantidad=0;
            foreach($this->puntuacion as $punto) {
                $cantidad+=$punto->getPuntuacion();
            }
            $resultado['cantidadValoraciones']=count($this->puntuacion);
            $resultado['puntos']=round($cantidad/$resultado['cantidadValoraciones']*5);
        }
        
        return $resultado;
    }
    
    /**
     * Devuelve la cantidad de votaciones positivas.
     * @return int
     */
    public function getPuntosPositivos() {
        $resultado=0;
        
        if(is_array($this->puntuacion)) {
            foreach($this->puntuacion as $punto) {
                if($punto->getPuntuacion()) {
                    $resultado++;
                }
            }
        }
        
        return $resultado;
    }
    
    /**
     * Devuelve la cantidad de votaciones negativas.
     * @return int
     */
    public function getPuntosNegativos() {
        $resultado=0;
        
        if(is_array($this->puntuacion)) {
            foreach($this->puntuacion as $punto) {
                if(!$punto->getPuntuacion()) {
                    $resultado++;
                }
            }
        }
        
        return $resultado;
    }
    
    /**
     * Carga un audio a partir de su id.
     * @param int $idAudio El id del audio.
     * @return Audio El audio obtenido.
     */
    public static function cargar($idAudio) {
        $resultado=FALSE;        
        $idAudio=intval($idAudio);
        
        try {
            $db=new DB();
            $datos=$db->obtainData("select * from at_audio where idAudio=$idAudio");
            if($datos['rows'] > 0) {
                //Tenemos el audio
                $resultado=new Audio($datos['data'][0]['idAudio'], Categoria::cargar(FALSE, FALSE, $datos['data'][0]['idCategoria']), 
                        $datos['data'][0]['idUser'], $datos['data'][0]['archivo'], 
                        IdiomaAudio::cargar($datos['data'][0]['idIdiomaAudio']), $datos['data'][0]['latitud'], 
                        $datos['data'][0]['longitud'], $datos['data'][0]['bloqueado'], 
                        Puntuacion::cargar($datos['data'][0]['idAudio']), $datos['data'][0]['descripcion'], 
                        Audio::contarDescargas($datos['data'][0]['idAudio']), $datos['data'][0]['idArea']);
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Carga los audios de un usuario.
     * @param int $idUsuario El id del usuario.
     * @return array Un array de audios.
     */
    public static function cargarPorUsuario($idUsuario) {
        $resultado=FALSE;        
        $idUsuario=intval($idUsuario);
        
        try {
            $db=new DB();
            $datos=$db->obtainData("select * from at_audio where idUser=$idUsuario");
            if($datos['rows'] > 0) {
                //Tenemos los audios
                //Obtenemos las categorías y los idiomasAudio
                $categoriasOrdenadas=Categoria::listar(TRUE);
                $categoriasOrdenadas=$categoriasOrdenadas['categorias'];
                $idiomasAudioOrdenados=IdiomaAudio::listar(TRUE);
                $idiomasAudioOrdenados=$idiomasAudioOrdenados['idiomasAudio'];
                //Preparamos las consulta para la puntuación y las descargas
                $BDPreparadaP=Puntuacion::cargarPreparada();
                $BDPreparadaD=Audio::cargarPreparadaDescargas();
   
                foreach($datos['data'] as $data)
                    //Tenemos los audios
                    $resultado[]=new Audio($data['idAudio'], $categoriasOrdenadas[$data['idCategoria']], 
                            $data['idUser'], $data['archivo'], $idiomasAudioOrdenados[$data['idIdiomaAudio']], 
                            $data['latitud'], $data['longitud'], $data['bloqueado'], 
                            Puntuacion::ejecutarPreparada($BDPreparadaP, $data['idAudio']), $data['marca'], 
                            $data['descripcion'], Audio::ejecutarPreparadaDescargas($BDPreparadaD, $data['idAudio']), 
                            $data['idArea']);
            }
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Introduce un nuevo audio en la base de datos.
     * @return int ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_ERROR
     */
    public function grabar() {
        $resultado['error']=ERROR_GENERICO;

        //Datos obligatorios
        if($this->categoria->getIdCategoria() && $this->idUser && $this->idiomaAudio->getIdIdiomaAudio() && $this->latitud && $this->longitud) {
            try {
                $db=new DB();
                $archivo=$db->escapeData(array($this->archivo, $this->descripcion, $this->idArea));
                $datos=$db->alterData("insert into at_audio (idCategoria, idUser, archivo, idIdiomaAudio, latitud, longitud, bloqueado, marca, descripcion, idArea)".
                    " values ({$this->categoria->getIdCategoria()}, $this->idUser, '$archivo[0]', {$this->idiomaAudio->getIdIdiomaAudio()}, '$this->latitud', '$this->longitud', $this->bloqueado, $this->marca, '$archivo[1]', '$archivo[2]')");
                    
                if($datos['rows'] > 0) {
                    $resultado['error']=ERROR_NO_ERROR;
                    $this->idAudio = $datos['insert_id'];
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
    
    /**
     * Devuelve todos los audios que cumplan los criterios.
     * @param array $categorias
     * @param array $idiomasAudio
     * @param int $puntuacion
     * @param boolean $bloqueados
     * @return array
     */
    public static function listar($categorias, $idiomasAudio, $puntuacion, $bloqueados=FALSE) {
        $resultado=FALSE;
        $user=comprobarLogin();
        if(!$user) $user=new User(0, '', '', '', '');
        if(!$bloqueados) $bloqueados='and bloqueado=0';
        else $bloqueados='';
        //Concatenamos las categorías
        $idCat='and idCategoria in (';
        foreach($categorias as $categoria) {
            if($categoria->getIdCategoria() == 0) {
                //Una categoría cualquiera
                $idCat = '';
                break;
            }
            $idCat.=intval($categoria->getIdCategoria()).', ';
        }
        if($idCat) $idCat=substr($idCat, 0, -2).')';
        //Ahora los idiomaAudio
        $idIdiomaAudio='and idIdiomaAudio in (';
        foreach($idiomasAudio as $idiomaAudio) {
            if($idiomaAudio->getIdIdiomaAudio() == 0) {
                //Está seleccionada la opción cualquiera
                $idIdiomaAudio='';
                break;
            }

            $idIdiomaAudio.=intval($idiomaAudio->getIdIdiomaAudio()).', ';
        }
        if($idIdiomaAudio) $idIdiomaAudio=substr($idIdiomaAudio, 0, -2).')';
        
        try {
            $db=new DB();
            $datos=$db->obtainData("select * from at_audio where (marca=0 or (marca=1 and idUser={$user->getIdUser()})) $idCat $idIdiomaAudio $bloqueados");
            if($datos['rows'] > 0) {
                //Obtenemos las categorías y los idiomasAudio
                $categoriasOrdenadas=Categoria::listar(TRUE);
                $categoriasOrdenadas=$categoriasOrdenadas['categorias'];
                $idiomasAudioOrdenados=IdiomaAudio::listar(TRUE);
                $idiomasAudioOrdenados=$idiomasAudioOrdenados['idiomasAudio'];
                //Preparamos las consulta para la puntuación y las descargas
                $BDPreparadaP=Puntuacion::cargarPreparada();
                $BDPreparadaD=Audio::cargarPreparadaDescargas();
   
                foreach($datos['data'] as $data)
                    //Tenemos los audios
                    $resultado[]=new Audio($data['idAudio'], $categoriasOrdenadas[$data['idCategoria']], 
                            $data['idUser'], $data['archivo'], $idiomasAudioOrdenados[$data['idIdiomaAudio']], 
                            $data['latitud'], $data['longitud'], $data['bloqueado'], 
                            Puntuacion::ejecutarPreparada($BDPreparadaP, $data['idAudio']), $data['marca'], 
                            $data['descripcion'], Audio::ejecutarPreparadaDescargas($BDPreparadaD, $data['idAudio']), 
                            $data['idArea']);
            }
        }
        catch(Exception $ex) { }
        
        return $resultado;
    }
    
    /**
     * Une una marca con su audio.
     * @param int $idAudio El id de la marca.
     * @param string $archivo La ruta del archivo.
     * @return boolean
     */
    public static function enlazar($idAudio, $archivo) {
        $resultado=FALSE;
        $user=comprobarLogin();
        if(!$user) {
            if(post('entrar_email') && post('entrar_pass')) {
                $user = User::login(post('entrar_email'), post('entrar_pass'));
                $user = $user['user'];
            }
            elseif(post('facebookID')) {
                $user=User::loginFacebook(post('facebookID'));
                $user = $user['user'];
                if($user->getUsuario() && $user->getUsuario() != post('facebookName')) {
                    $user = false;
                }
            }
        }
        
        try {
            $db=new DB();
            $archivo=$db->escapeData(array($archivo));
            $idAudio=intval($idAudio);
            $idUser=intval($user->getIdUser());
            
            $datos=$db->alterData("update at_audio set marca=0, archivo='$archivo[0]' where idAudio=$idAudio and idUser=$idUser");
            if($datos['rows'] > 0) $resultado=TRUE;
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Une una marca con su area.
     * @param int $idAudio El id de la marca.
     * @param string $idArea El id del area.
     * @return boolean
     */
    public static function enlazarArea($idAudio, $idArea) {
        $resultado=FALSE;
        $user=comprobarLogin();
        
        try {
            $db=new DB();
            $idArea=$db->escapeData(array($idArea));
            $idAudio=intval($idAudio);
            $idUser=intval($user->getIdUser());

            $datos=$db->alterData("update at_audio set idArea='$idArea[0]' where idAudio=$idAudio and idUser=$idUser");
            //$datos=$db->alterData("update at_audio set idArea='$idArea[0]' where idAudio=$idAudio");
            if($datos['rows'] > 0) $resultado=TRUE;
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Borra un audio.
     * @param int $idAudio
     * @return boolean
     */
    public static function borrar($idAudio) {
        $resultado=FALSE;
        $user=comprobarLogin();
        if(!$user) {
            if(post('entrar_email') && post('entrar_pass')) {
                $user = User::login(post('entrar_email'), post('entrar_pass'));
                $user = $user['user'];
            }
            elseif(post('facebookID')) {
                $user=User::loginFacebook(post('facebookID'));
                $user = $user['user'];
                if($user->getUsuario() && $user->getUsuario() != post('facebookName')) {
                    $user = false;
                }
            }
        }
        
        try {
            $db=new DB();
            $idAudio=intval($idAudio);
            $idUser=intval($user->getIdUser());
            
            $datos=$db->alterData("delete from at_audio where idAudio=$idAudio and idUser=$idUser");
            if($datos['rows'] > 0) $resultado=TRUE;
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Borra el audio de la base de datos y del disco, incluidas fotos.
     * @return boolean
     */
    public function borradoCompleto() {
        $resultado = FALSE;
        
        if(self::borrar($this->idAudio)) {
            unlink('img/fondos/'.$this->idAudio.'.jpg');
            unlink('img/fondos/'.$this->idAudio.'_mini.jpg');
            $resultado = unlink('sonido/'.$this->getArchivo());
        }
        
        return $resultado;
    }
    
    /**
     * Marca un audio como inapropiado.
     * @param int $idAudio El id del audio a marcar.
     * @param int $tipoDenuncia 
     * @return boolean
     */
    public static function marcarInapropiado($idAudio, $tipoDenuncia) {
        $resultado=FALSE;
        $user=comprobarLogin();
        
        try {
            $db=new DB();
            $idAudio=intval($idAudio);
            $idUser=intval($user->getIdUser());
            $tipoDenuncia = intval($tipoDenuncia);
            
            $datos=$db->alterData("replace into at_inapropiado (idAudio, idUser, tipoDenuncia) values ($idAudio, $idUser, $tipoDenuncia)");
            if($datos['rows'] > 0) $resultado=TRUE;
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Devuelve la cantidad de descargas de un audio.
     * @param int $idAudio El audio objetivo.
     * @return int
     */
    public static function contarDescargas($idAudio) {
        $resultado=FALSE;
        
        try {
            $db=new DB();
            $idAudio=intval($idAudio);
            
            $datos=$db->obtainData("select count(*) as cantidad from at_descargas where idAudio = $idAudio");
            if($datos['rows'] > 0) $resultado=$datos['data'][0]['cantidad'];
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Devuelve una consulta preparada para contar las descargas. El parámetro es idAudio.
     * @return BDPreparada La consulta preparada.
     */
    public static function cargarPreparadaDescargas() {
        try {
            $resultado=new BDPreparada('select count(*) as cantidad from at_descargas where idAudio = ?');
            $resultado->meterParametros(array('idAudio' => BDPreparada::INTEGER));
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Carga la cantidad de descargas a partir del idAudio.
     * @param int $idAudio El id del Audio.
     * @return int Las descargas del audio.
     */
    public static function ejecutarPreparadaDescargas($BDPreparada, $idAudio) {
        $resultado=FALSE;
        
        try {
            $BDPreparada->rellenarParametros(array('idAudio' => $idAudio));
            $datos=$BDPreparada->obtenerDatos();
            if($datos['filas'] > 0) 
                $resultado=$datos['datos'][0]['cantidad'];
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Cuenta una nueva descarga.
     * @param int $idAudio
     * @return boolean
     */
    public static function nuevaDescarga($idAudio) {
        $resultado=FALSE;
        
        try {
            $db=new DB();
            $idAudio=intval($idAudio);
            $ip=$db->escapeData(array($_SERVER['REMOTE_ADDR']));
            
            $datos=$db->alterData("insert into at_descargas (idAudio, ip) values ($idAudio, '$ip[0]')");
            if($datos['rows'] > 0) $resultado=TRUE;
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Devuelve una array con los idAudio que tengan el texto en la descripción.
     * @param string $descripcion
     * @param IdiomaAudio $idiomaAudio
     * @param float $latSupDer
     * @param float $lonSupDer
     * @param float $latInfIzq
     * @param float $lonInfIzq
     * @param array $categorias
     * @return array
     */
    public static function buscarPorDescripcion($descripcion, $idiomaAudio, $latSupDer = FALSE, $lonSupDer = FALSE, $latInfIzq = FALSE, $lonInfIzq = FALSE, $categorias = array()) {
        $resultado=FALSE;

        $descripcion = trim($descripcion);
        if($descripcion && $idiomaAudio instanceof IdiomaAudio) {
            try {
                $db=new DB();
                $descripcion=$db->escapeData(array($descripcion));
                
                if($latSupDer !== FALSE && $lonSupDer !== FALSE && $latInfIzq !== FALSE && $lonInfIzq !== FALSE) {
                    //Delimitamos la zona
                    $latSupDer = (float)$latSupDer;
                    $lonSupDer = (float)$lonSupDer;
                    $latInfIzq = (float)$latInfIzq;
                    $lonInfIzq = (float)$lonInfIzq;
                    
                    $limites = "and latitud <= '$latSupDer' and longitud <= '$lonSupDer' and latitud >= '$latInfIzq' and longitud >= '$lonInfIzq'";
                }
                else {
                    $limites = '';
                }
                
                $limitesCat = '';
                if($categorias) {
                    //Delimitamos por categoría
                    foreach($categorias as $categoria) {
                        $idCategoria = (int)$categoria->getIdCategoria();
                        $limitesCat .= "$idCategoria,";
                    }
                    
                    if($limitesCat) {
                        $limitesCat = 'and idCategoria in ('.substr($limitesCat, 0, -1).')';
                    }
                }

                $datos=$db->obtainData("select * from at_audio where idIdiomaAudio = '{$idiomaAudio->getIdIdiomaAudio()}' and bloqueado = 0 and marca = 0 and descripcion like '%$descripcion[0]%' $limites $limitesCat");
                if($datos['rows'] > 0) {
                    foreach($datos['data'] as $dato) {
                        $resultado[] = $dato['idAudio'];
                    }
                }
                elseif($limites) {
                    //Repetimos la consulta sin los límites
                    $datos=$db->obtainData("select * from at_audio where idIdiomaAudio = '{$idiomaAudio->getIdIdiomaAudio()}' and bloqueado = 0 and marca = 0 and descripcion like '%$descripcion[0]%' $limitesCat");
                    if($datos['rows'] > 0) {
                        foreach($datos['data'] as $dato) {
                            $resultado[] = $dato['idAudio'];
                        }
                    }
                }
            }
            catch(Exception $ex) {
                $resultado=FALSE;
            }
        }

        return $resultado;
    }
    
    /**
     * Devuelve los audios de una zona.
     * @param IdiomaAudio $idiomaAudio
     * @param float $latSupDer
     * @param float $lonSupDer
     * @param float $latInfIzq
     * @param float $lonInfIzq
     * @param int $cantidad
     * @param array $categorias
     * @param boolean $soloIds
     * @return array Los ids de los audios.
     */
    public static function buscarPorZona($idiomaAudio, $latSupDer, $lonSupDer, $latInfIzq, $lonInfIzq, $cantidad, $categorias = array(), $soloIds = TRUE) {
        $resultado=FALSE;

        if($idiomaAudio instanceof IdiomaAudio) {
            try {
                $db=new DB();

                $limitesCat = '';
                if($categorias) {
                    //Delimitamos por categoría
                    foreach($categorias as $categoria) {
                        $idCategoria = (int)$categoria->getIdCategoria();
                        if($idCategoria) {
                            $limitesCat .= "$idCategoria,";
                        }
                    }
                    
                    if($limitesCat) {
                        $limitesCat = 'and idCategoria in ('.substr($limitesCat, 0, -1).')';
                    }
                }
                
                $latSupDer = (float)$latSupDer;
                $lonSupDer = (float)$lonSupDer;
                $latInfIzq = (float)$latInfIzq;
                $lonInfIzq = (float)$lonInfIzq;
                $cantidad = (int)$cantidad;
                $cantidadString = ($cantidad > 0 ? "limit $cantidad" : '');

                $datos=$db->obtainData("select * from at_audio where idIdiomaAudio = '{$idiomaAudio->getIdIdiomaAudio()}' and bloqueado = 0 and marca = 0 and latitud <= '$latSupDer' and longitud <= '$lonSupDer' and latitud >= '$latInfIzq' and longitud >= '$lonInfIzq' $limitesCat order by rand() $cantidadString");
                if($datos['rows'] > 0) {
                    //Obtenemos las categorías y los idiomasAudio
                    $categoriasOrdenadas=Categoria::listar(TRUE);
                    $categoriasOrdenadas=$categoriasOrdenadas['categorias'];
                    $idiomasAudioOrdenados=IdiomaAudio::listar(TRUE);
                    $idiomasAudioOrdenados=$idiomasAudioOrdenados['idiomasAudio'];
                    //Preparamos las consulta para la puntuación y las descargas
                    $BDPreparadaP=Puntuacion::cargarPreparada();
                    $BDPreparadaD=Audio::cargarPreparadaDescargas();
                    
                    foreach($datos['data'] as $data) {
                        if($soloIds) {
                            $resultado[] = $data['idAudio'];
                        }
                        else {
                            $resultado[]=new Audio($data['idAudio'], $categoriasOrdenadas[$data['idCategoria']], 
                                    $data['idUser'], $data['archivo'], $idiomasAudioOrdenados[$data['idIdiomaAudio']], 
                                    $data['latitud'], $data['longitud'], $data['bloqueado'], 
                                    Puntuacion::ejecutarPreparada($BDPreparadaP, $data['idAudio']), $data['marca'], 
                                    $data['descripcion'], Audio::ejecutarPreparadaDescargas($BDPreparadaD, $data['idAudio']), 
                                    $data['idArea']);
                        }
                    }
                }
            }
            catch(Exception $ex) {
                $resultado=FALSE;
            }
        }
        
        return $resultado;
    }
    
    /**
     * Devuelve los audios de una zona.
     * @param IdiomaAudio $idiomaAudio
     * @param string $idArea
     * @param int $cantidad
     * @param array $categorias
     * @param boolean $soloIds
     * @return array Los ids de los audios.
     */
    public static function buscarPorIdZona($idiomaAudio, $idArea, $cantidad, $categorias = array(), $soloIds = FALSE) {
        $resultado=FALSE;

        if($idiomaAudio instanceof IdiomaAudio) {
            try {
                $db=new DB();

                $limitesCat = '';
                if($categorias) {
                    //Delimitamos por categoría
                    foreach($categorias as $categoria) {
                        $idCategoria = (int)$categoria->getIdCategoria();
                        if($idCategoria) {
                            $limitesCat .= "$idCategoria,";
                        }
                    }
                    
                    if($limitesCat) {
                        $limitesCat = 'and idCategoria in ('.substr($limitesCat, 0, -1).')';
                    }
                }
                
                $idArea = $db->escapeData(array($idArea));
                $cantidad = (int)$cantidad;
                $cantidadString = ($cantidad > 0 ? "limit $cantidad" : '');
                
                $datos=$db->obtainData("select * from at_audio where idIdiomaAudio = '{$idiomaAudio->getIdIdiomaAudio()}' and bloqueado = 0 and marca = 0 and idArea = '$idArea[0]' $limitesCat order by rand() $cantidadString");
                if($datos['rows'] > 0) {
                    //Obtenemos las categorías y los idiomasAudio
                    $categoriasOrdenadas=Categoria::listar(TRUE);
                    $categoriasOrdenadas=$categoriasOrdenadas['categorias'];
                    $idiomasAudioOrdenados=IdiomaAudio::listar(TRUE);
                    $idiomasAudioOrdenados=$idiomasAudioOrdenados['idiomasAudio'];
                    //Preparamos las consulta para la puntuación y las descargas
                    $BDPreparadaP=Puntuacion::cargarPreparada();
                    $BDPreparadaD=Audio::cargarPreparadaDescargas();
                    
                    foreach($datos['data'] as $data) {
                        if($soloIds) {
                            $resultado[] = $data['idAudio'];
                        }
                        else {
                            $resultado[]=new Audio($data['idAudio'], $categoriasOrdenadas[$data['idCategoria']], 
                                    $data['idUser'], $data['archivo'], $idiomasAudioOrdenados[$data['idIdiomaAudio']], 
                                    $data['latitud'], $data['longitud'], $data['bloqueado'], 
                                    Puntuacion::ejecutarPreparada($BDPreparadaP, $data['idAudio']), $data['marca'], 
                                    $data['descripcion'], Audio::ejecutarPreparadaDescargas($BDPreparadaD, $data['idAudio']), 
                                    $data['idArea']);
                        }
                    }
                }
            }
            catch(Exception $ex) {
                $resultado=FALSE;
            }
        }
        
        return $resultado;
    }
}
