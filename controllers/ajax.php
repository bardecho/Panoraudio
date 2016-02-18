<?php
require_once 'clases/audio.php';
require_once 'clases/categoria.php';
require_once 'clases/idiomaaudio.php';
require_once 'clases/puntuacion.php';
require_once 'clases/preferencia.php';
require_once 'clases/ruta.php';
require_once 'clases/Comentario.php';
require_once 'clases/area.php';
require_once 'utils/correcciones.php';
require_once 'utils/rehash_helper.php';
require_once 'utils/limitimagesize_helper.php';

class Ajax {

    public function obtenerUsuario() {
        $resultado['ok'] = FALSE;

        $user = comprobarLogin();
        if ($user) {
            $resultado['ok'] = TRUE;
            $resultado['usuario'] = $user->getUsuario();
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    public function obtenerAudios() {
        $resultado['ok'] = FALSE;

        $idiomasAudio = array(cargarIdiomaAudio()); //Este sistema tiene preferencia sobre el de la bd
        $user = comprobarLogin();
        if ($user) {
            $preferencia = Preferencia::cargar($user->getIdUser());
            $categorias = $preferencia['preferencia']->getCategoria();
            if (!$idiomasAudio[0])
                $idiomasAudio = $preferencia['preferencia']->getIdiomaAudio();
            $puntuacionMinima = $preferencia['preferencia']->getPuntuacionMinima();
        }
        else {
            $categorias = cargarCategoriasAudio();
            if (!$idiomasAudio[0])
                $idiomasAudio = array(new IdiomaAudio(0, '', ''));
            $puntuacionMinima = 0;
        }
        
        //Devolvemos todos los audios de las categorías
        $categorias = Categoria::listar();
        
        if(post('idArea')) {
            //Por área
            $audios = Audio::buscarPorIdZona($idiomasAudio[0], post('idArea'), 0, $categorias['categorias'], FALSE);
        }
        elseif(post('latSupDer') !== FALSE && post('lonSupDer') !== FALSE && post('latInfIzq') !== FALSE && post('lonInfIzq') !== FALSE) {
            //Por coordenadas
            $audios = Audio::buscarPorZona($idiomasAudio[0], post('latSupDer'), post('lonSupDer'), 
                    post('latInfIzq'), post('lonInfIzq'), 0, $categorias['categorias'], FALSE);
        }
        else {
            //Todos
            $audios = Audio::listar($categorias['categorias'], $idiomasAudio, $puntuacionMinima);
        }

        if ($audios) {
            //Cargamos las areas
            $areas = Area::listar(TRUE);
            //Vamos a cargar los nombres de usuario de los dueños de los audios
            $BDPreparada = User::cargarPreparada();
            //Y los ids de ruta
            $BDPreparadaRutas = Ruta::cargarPreparada();
            foreach ($audios as $audio) {
                $usuario = User::ejecutarPreparada($BDPreparada, $audio->getIdUser());
                if ($usuario) {
                    $idUser = $usuario->getIdUser();
                    $nombreUser = $usuario->getUsuario();
                }
                else {
                    $idUser = 0;
                    $nombreUser = '';
                }
                //Para distribuir los audios en distintos servidores se pueden ir alternando aquí
                $ruta = $audio->getArchivo(); //Le quité la ruta para reducir el consumo de ancho de banda

                if(post('objeto')) {
                    $resultado['marcadores'][] = array('id' => $audio->getIdAudio(), 
                        'la' => $audio->getLatitud(), 'lo' => $audio->getLongitud(), 'ruta' => $ruta, 
                        'positivos' => $audio->getPuntosPositivos(), 'negativos' => $audio->getPuntosNegativos(),
                        'categoria' => $audio->getCategoria()->getIdCategoria(), 'user' => $nombreUser, 
                        'marca' => $audio->getMarca(), 'descripcion' => convertirURL($audio->getDescripcion()), 
                        'descargas' => $audio->getDescargas(), 'fondo' => is_file('img/fondos/' . $audio->getIdAudio() . '.jpg'), 
                        'rutas' => Ruta::ejecutarPreparada($BDPreparadaRutas, $audio->getIdAudio()), 
                        'idUser' => $idUser, 'idArea' => (isset($areas[$audio->getIdArea()]) ? $audio->getIdArea() : ''), 
                        'nombreArea' => (isset($areas[$audio->getIdArea()]) ? $areas[$audio->getIdArea()]->getArea() : ''),
                        'limitesArea' => (isset($areas[$audio->getIdArea()]) ? 
                            array($areas[$audio->getIdArea()]->getLatitudIzquierdaInferior(), 
                                $areas[$audio->getIdArea()]->getLongitudIzquierdaInferior(), 
                                $areas[$audio->getIdArea()]->getLatitudDerechaSuperior(), 
                                $areas[$audio->getIdArea()]->getLongitudDerechaSuperior()) : 
                            array()));
                }
                else {
                    //'id', 'la', 'lo', 'ruta', 'positivos', 'negativos', 'categoria', 'user', 'marca', 
                    //'descripcion', 'descargas', 'fondo', 'rutas', 'idUser', 'idArea', 'nombreArea', 'limitesArea'
                    $resultado['marcadores'][] = array($audio->getIdAudio(), $audio->getLatitud(),
                        $audio->getLongitud(), $ruta, $audio->getPuntosPositivos(), $audio->getPuntosNegativos(),
                        $audio->getCategoria()->getIdCategoria(), $nombreUser, $audio->getMarca(),
                        convertirURL($audio->getDescripcion()), $audio->getDescargas(),
                        is_file('img/fondos/' . $audio->getIdAudio() . '.jpg'), Ruta::ejecutarPreparada($BDPreparadaRutas, $audio->getIdAudio()), 
                        $idUser, (isset($areas[$audio->getIdArea()]) ? $audio->getIdArea() : ''), (isset($areas[$audio->getIdArea()]) ? $areas[$audio->getIdArea()]->getArea() : ''),
                        (isset($areas[$audio->getIdArea()]) ? 
                            array($areas[$audio->getIdArea()]->getLatitudIzquierdaInferior(), 
                                $areas[$audio->getIdArea()]->getLongitudIzquierdaInferior(), 
                                $areas[$audio->getIdArea()]->getLatitudDerechaSuperior(), 
                                $areas[$audio->getIdArea()]->getLongitudDerechaSuperior()) : 
                            array()));
                }
            }

            $resultado['ok'] = TRUE;
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }
    
    public function subirFotoPerfil() {
        $resultado = '0';

        $user=comprobarLogin();
        if($user) {
            $ext=explode('.', $_FILES['fotoPerfil']['name']);
            $ext=strtolower(trim($ext[count($ext)-1]));
            $archivo=$user->getIdUser().'.jpg';
            
            //Comprobamos que es una imagen
            if(stripos($_FILES['fotoPerfil']['type'], 'image') !== FALSE) {
                //Lo movemos
                if(limitImageSize($_FILES['fotoPerfil']['tmp_name'], $_FILES['fotoPerfil']['name'], 800, 800, 85, FALSE, FALSE, 'img/fotosPerfil/'.$archivo)) {
                    $resultado = '1';
                }
            }
        }

        echo $resultado;
    }
    
    public function obtenerComentarios($idAudio) {
        $resultado['ok'] = FALSE;

        if($idAudio) {
            //Devolvemos todos los comentarios
            $comentarios = Comentario::cargarPorAudio($idAudio);
            if ($comentarios) {
                //Vamos a cargar los nombres de usuario de los dueños de los comentarios
                $BDPreparada = User::cargarPreparada();
                foreach ($comentarios as $comentario) {
                    $usuario = User::ejecutarPreparada($BDPreparada, $comentario->getIdUser());
                    if ($usuario) {
                        $idUser = $usuario->getIdUser();
                        $nombreUser = $usuario->getUsuario();
                    }
                    else {
                        $idUser = 0;
                        $nombreUser = '';
                    }

                    //'idUsuario', 'usuario', 'comentario'
                    $resultado['comentarios'][] = array($idUser, $nombreUser, $comentario->getTexto());
                }

                $resultado['ok'] = TRUE;
            }
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }
    
    public function guardarComentario($idAudio, $texto) {
        $resultado['ok'] = FALSE;
        $usuario = comprobarLogin();

        if ($usuario) {
            $comentario = new Comentario(0, $usuario->getIdUser(), $idAudio, $texto);
            if ($comentario->grabar() == ERROR_NO_ERROR) {
                $resultado['ok'] = TRUE;
            }
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    /**
     * Lista categorías o idiomasAudio.
     * @param string $tipo La opción deseada (categorias, idiomasAudio).
     * @return array error=(ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_INGRESO), 
     * items => el índice es el id y el valor el item.
     */
    public function listar($tipo) {
        $consulta = FALSE;
        $resultado['ok'] = FALSE;

        switch ($tipo) {
            case 'idiomasAudio':
                $consulta = 'select * from at_idiomaAudio';
                $selectorId = 'idIdiomaAudio';
                $selector = 'idioma';
                break;

            case 'categorias':
                $consulta = 'select * from at_categoria';
                $selectorId = 'idCategoria';
                $selector = 'categoria';
                break;
        }

        if ($consulta) {
            try {
                $db = new DB();
                $datos = $db->obtainData($consulta);
                if ($datos['rows'] > 0) {
                    $resultado['ok'] = TRUE;
                    foreach ($datos['data'] as $dato)
                        $resultado['items'][$dato[$selectorId]] = traducirLista($dato[$selectorId]);
                }
            } catch (Exception $ex) {
                $resultado['ok'] = FALSE;
            }
        }
        else
            $resultado['ok'] = FALSE;

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    public function nuevaMarca($idCategoria, $idIdiomaAudio, $latitud, $longitud, $descripcion) {
        $resultado['ok'] = FALSE;

        $user = comprobarLogin();
        if ($user) {
            $audio = new Audio(0, new Categoria($idCategoria, ''), $user->getIdUser(), '',
                            new IdiomaAudio($idIdiomaAudio, ''), $latitud, $longitud, 0, FALSE, 1, urldecode($descripcion));
            if ($audio->grabar() == ERROR_NO_ERROR)
                $resultado['ok'] = TRUE;
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    public function quitarMarca($idAudio) {
        $resultado['ok'] = FALSE;

        $user = comprobarLogin();
        if ($user) {
            $resultado['ok'] = Audio::borrar($idAudio);
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    public function marcarInapropiado($idAudio, $tipoDenuncia) {
        $resultado['ok'] = FALSE;

        $user = comprobarLogin();
        if ($user) {
            $resultado['ok'] = Audio::marcarInapropiado($idAudio, $tipoDenuncia);
            
            $mensaje = cargarPlantillaEmail('inapropiado');
            if ($mensaje) {
                switch ($tipoDenuncia) {
                    case 1:
                        $motivo = 'El contenido es fraudulento';
                        break;
                    
                    case 2:
                        $motivo = 'El contenido es inapropiado para Panoraudio';
                        break;
                    
                    case 3:
                        $motivo = 'El contenido supone un riesgo';
                        break;
                    
                    default:
                        $motivo = 'Desconocido';
                        break;
                }
                $mensaje['mensaje'] = str_replace(array('{url}', '{motivo}'), array(BASE_URL."?id=$idAudio", $motivo), $mensaje['mensaje']);
                $mensaje['mensajeTexto'] = str_replace(array('{url}', '{motivo}'), array(BASE_URL."?id=$idAudio", $motivo), $mensaje['mensajeTexto']);

                sendMail(FROM_EMAIL, FROM_NAME, FROM_EMAIL, 'Audio inapropiado', $mensaje['mensaje'], $mensaje['mensajeTexto']);
            }
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    /**
     * Sirve para valorar un audio.
     * @param int $idAudio El id del audio a valorar.
     * @param int $puntos Los puntos que se le otorgan.
     * @return array error (ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO)
     */
    public function valorar($idAudio, $puntos) {
        $resultado['ok'] = FALSE;

        $user = comprobarLogin();
        if ($idAudio && $puntos !== FALSE && $user && $this->puedeValorar($idAudio)) {
            $audio = Audio::cargar($idAudio);

            if ($audio) {
                $puntuacion = new Puntuacion($user->getIdUser(), $idAudio, $puntos);
                if ($puntuacion->actualizar()) {
                    $resultado['ok'] = TRUE;
                    $audio = Audio::cargar($idAudio);
                    $resultado['puntosPositivos'] = $audio->getPuntosPositivos();
                    $resultado['puntosNegativos'] = $audio->getPuntosNegativos();
                }
            }
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    //Comprobamos si no es el dueño o ya ha puntuado
    private function puedeValorar($idAudio) {
        $user = comprobarLogin();
        $resultado = FALSE;

        //¿Está loguado?
        if ($user) {
            $audio = Audio::cargar($idAudio);

            //¿Es el dueño?
            if ($audio->getIdUser() != $user->getIdUser()) {
                $resultado = TRUE;
                //¿Ya ha puntuado antes?
                /* $puntuacion=Puntuacion::cargar($idAudio);
                  if($puntuacion) {
                  foreach($puntuacion as $punto) {
                  if($punto->getIdUser() == $user->getIdUser()) {
                  $resultado=FALSE;
                  break;
                  }
                  }
                  } */
            }
        }

        return $resultado;
    }

    //Cuenta descargas únicas
    public function nuevaDescarga($idAudio) {
        $resultado['ok'] = FALSE;

        if ($idAudio && Audio::nuevaDescarga($idAudio)) {
            $resultado['ok'] = TRUE;
            $resultado['descargas'] = Audio::contarDescargas($idAudio);
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    //Devuelve las coordenadas de un audio
    public function localizarAudio($idAudio) {
        //Comprobamos las correcciones en los ids antes de hacer la solicitud (Los ids de rías altas deben venir con +3000)
        $correcciones = new Correcciones();
        $idAudio = $correcciones->idCorregido($idAudio);

        $audio = Audio::cargar($idAudio);

        if ($audio) {
            $resultado['ok'] = TRUE;
            $resultado['coords'] = array('latitude' => $audio->getLatitud(), 'longitude' => $audio->getLongitud());
            $resultado['marca'] = $audio->getMarca();
            $resultado['id'] = $idAudio;
            $resultado['categoria'] = $audio->getCategoria()->getIdCategoria();
            $idioma = unserialize($_SESSION['claseIdioma']);
            $idiomaAudio = $audio->getIdiomaAudio();
            if ($idioma->idiomaActual() != $idiomaAudio->getSiglasIdioma() || cargarIdiomaAudio()->getIdIdiomaAudio() != $idiomaAudio->getIdIdiomaAudio())
                $resultado['idioma'] = $idiomaAudio->getIdIdiomaAudio();
            else
                $resultado['idioma'] = FALSE;
        }
        else
            $resultado['ok'] = FALSE;


        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    //Devuelve un código javascript para colocar puntuaciones (A los ids de rías altas les resto 3000)
    public function widgetObtenerPuntuaciones($origen) {
        //Seleccionamos el origen
        switch ($origen) {
            case 'baixas':
                $consulta = 'select id,puntuacion from at_puntuacionRBaixas';
                $idUsuario = 95;
                $correccion = 0;
                break;

            case 'altas':
                $consulta = 'select id,puntuacion from at_puntuacionRAltas';
                $idUsuario = 97;
                $correccion = 3000;
                break;

            default:
                exit;
        }

        $audios = Audio::cargarPorUsuario($idUsuario);
        //Cargamos la tabla de puntuaciones
        $db = new DB();
        $datos = $db->obtainData($consulta);
        foreach ($datos['data'] as $punto)
            $puntos[$punto['id']] = $punto['puntuacion'];

        $arrayIds = 'var tellmee_datos={';

        if ($audios) {
            $correcciones = new Correcciones();

            foreach ($audios as $audio) {
                $id = $audio->getIdAudio() - $correccion;
                $puntuacion = $audio->getPuntos();

                if ($puntuacion['cantidadValoraciones'] > 0 && isset($puntos[$id])) {
                    //Hacemos la media
                    $puntuacion['cantidadValoraciones']++;
                    $puntuacion['puntos'] = ($puntos[$id] + $puntuacion['puntos'] * 2) / 2;
                } elseif (isset($puntos[$id])) {
                    //Ponemos la puntuación de Rías
                    $puntuacion['cantidadValoraciones'] = 1;
                    $puntuacion['puntos'] = $puntos[$id];
                }
                //Lo prefieren sin la cantidad de valoraciones
                //$arrayIds .= "$id:[{$puntuacion['puntos']},{$puntuacion['cantidadValoraciones']}],";
                $arrayIds .= "$id:{$puntuacion['puntos']},";

                $ids = $correcciones->idsVacios($id + $correccion);
                if (count($ids) > 0) {
                    //Añadimos los huecos
                    foreach ($ids as $valor) {
                        $valor = $valor - $correccion;
                        //Lo prefieren sin la cantidad de valoraciones
                        //$arrayIds .= "$valor:[{$puntuacion['puntos']},{$puntuacion['cantidadValoraciones']}],";
                        $arrayIds .= "$valor:{$puntuacion['puntos']},";
                    }
                }
            }
            $arrayIds = substr($arrayIds, 0, -1);
        }
        $arrayIds.='};';

        //Lo prefieren sin la cantidad de valoraciones
        //echo '$(document).ready(function() {'.$arrayIds.'$("[id|=\'tellmee_p\']").each(function(indice, elemento) {var id=$(elemento).attr("id").substr(10);if(tellmee_datos[id] != undefined)$(elemento).html("Valoración: "+tellmee_datos[id][0]+" ("+tellmee_datos[id][1]+" votos)");else $(elemento).html("Valoración: - (0 votos)");});});';
        echo '$(document).ready(function() {' . $arrayIds . '$("[id|=\'tellmee_p\']").each(function(indice, elemento) {var id=$(elemento).attr("id").substr(10);if(tellmee_datos[id] != undefined)$(elemento).html("Valoración: "+tellmee_datos[id]);else $(elemento).html("Valoración: Sin valorar");});});';
    }

    //Cambia la categoría en las preferencias del usuario usando ajax
    public function modificarCategoriasPreferidas($categorias) {
        $resultado['ok'] = FALSE;

        if ($categorias) {
            $user = comprobarLogin();
            if ($user) {
                $categorias = explode(',', $categorias);
                $preferencia = Preferencia::cargar($user->getIdUser());
                if ($preferencia['error'] == ERROR_NO_ERROR) {
                    //Las categorías
                    foreach ($categorias as $categoriaId)
                        $categoriasArray[] = new Categoria($categoriaId, '');
                    $preferencia['preferencia']->setCategoria($categoriasArray);
                    //Guardamos los cambios
                    if ($preferencia['preferencia']->actualizar())
                        $resultado['ok'] = TRUE;
                }
            }
            else {
                $categorias = explode(',', $categorias);
                //Las categorías
                foreach ($categorias as $categoriaId)
                    $categoriasArray[] = new Categoria($categoriaId, '');
                grabarCategoriasAudio($categoriasArray);

                $resultado['ok'] = TRUE;
            }
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    //Cambia el idioma de la aplicación y de los audios usando ajax
    public function modificarIdiomasPreferidos($idIdioma) {
        $user = comprobarLogin();
        $idiomasAudioArray[] = IdiomaAudio::cargar($idIdioma);

        if ($idiomasAudioArray[0] != null) {
            //Cambiamos el idioma de la aplicación
            $idioma = unserialize($_SESSION['claseIdioma']);
            $idioma->establecerIdioma($idiomasAudioArray[0]->getSiglasIdioma());

            //Con usuario
            if ($user) {
                $preferencia = Preferencia::cargar($user->getIdUser());
                if ($preferencia['error'] == ERROR_NO_ERROR) {
                    //El idioma de los audios
                    $preferencia['preferencia']->setIdiomaAudio($idiomasAudioArray);
                    //Guardamos los cambios
                    if ($preferencia['preferencia']->actualizar())
                        $resultado['ok'] = TRUE;
                    else
                        $resultado['ok'] = FALSE;

                    grabarIdiomaAudio($idiomasAudioArray[0]);
                }
                else
                    $resultado['ok'] = FALSE;
            }
            else {
                //Sin usuario
                grabarIdiomaAudio($idiomasAudioArray[0]);
                $resultado['ok'] = TRUE;
            }
        }
        else
            $resultado['ok'] = FALSE;

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    /**
     * Busca texto en los audios.
     * @param string $texto
     * @param float $latSupDer
     * @param float $lonSupDer
     * @param float $latInfIzq
     * @param float $lonInfIzq
     */
    public function buscarMarca($texto, $latSupDer = FALSE, $lonSupDer = FALSE, $latInfIzq = FALSE, $lonInfIzq = FALSE) {
        $resultado['ok'] = TRUE;
        
        $categorias = cargarCategoriasAudio();
        if($categorias) {
            $idiomasAudio = array(cargarIdiomaAudio());
            $user = comprobarLogin();
            if ($user) {
                $preferencia = Preferencia::cargar($user->getIdUser());
                if (!$idiomasAudio[0]) {
                    $idiomasAudio = $preferencia['preferencia']->getIdiomaAudio();
                }
            }
            else {
                if (!$idiomasAudio[0]) {
                    $idiomasAudio = array(new IdiomaAudio(0, '', ''));
                }
            }

            $ids = Audio::buscarPorDescripcion($texto, $idiomasAudio[0], $latSupDer, $lonSupDer, $latInfIzq, $lonInfIzq, $categorias);
        }
        else {
            $ids = FALSE;
        }
        
        if ($ids === FALSE) {
            $resultado['ok'] = FALSE;
        }
        else {
            //Vamos a quedarnos con el primero
            $resultado['id'] = $ids[0];
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }
    
    /**
     * Devuelve los audios de una zona ordenados al azar.
     * @param float $latSupDer
     * @param float $lonSupDer
     * @param float $latInfIzq
     * @param float $lonInfIzq
     * @param int $cantidad
     */
    public function vistaPrevia($latSupDer, $lonSupDer, $latInfIzq, $lonInfIzq, $cantidad) {
        $resultado['ok'] = TRUE;
        
        $categorias = cargarCategoriasAudio();
        if($categorias) {
            $idiomasAudio = array(cargarIdiomaAudio());
            $user = comprobarLogin();
            if ($user) {
                $preferencia = Preferencia::cargar($user->getIdUser());
                if (!$idiomasAudio[0]) {
                    $idiomasAudio = $preferencia['preferencia']->getIdiomaAudio();
                }
            }
            else {
                if (!$idiomasAudio[0]) {
                    $idiomasAudio = array(new IdiomaAudio(0, '', ''));
                }
            }

            $ids = Audio::buscarPorZona($idiomasAudio[0], $latSupDer, $lonSupDer, $latInfIzq, $lonInfIzq, $cantidad, $categorias);
        }
        else {
            $ids = FALSE;
        }
        
        if ($ids === FALSE) {
            $resultado['ok'] = FALSE;
        }
        else {
            $resultado['ids'] = $ids;
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }
    
    /**
     * Devuelve datos para generar la ventana de perfil.
     * @param int $idUser
     */
    public function obtenerDatosPerfil($idUser) {
        $resultado['ok'] = TRUE;
        
        if($idUser) {
            $audios = Audio::cargarPorUsuario($idUser);
            $cantidadComentarios = Comentario::contarComentarios($idUser);
            if($audios) {
                foreach($audios as $audio) {
                    //Deben tener imagen
                    if(is_file("img/fondos/{$audio->getIdAudio()}.jpg")) {
                        $resultado['datos'][] = array($audio->getIdAudio(), $audio->getPuntosPositivos(), 
                                (isset($cantidadComentarios[$audio->getIdAudio()]) ? $cantidadComentarios[$audio->getIdAudio()] : 0));
                    }
                }
            }
        }
        else {
            $resultado['ok'] = FALSE;
        }
        
        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    /**
     * Genera una nueva contraseña para el email indicado.
     * @param string $email
     */
    public function recuperar($email) {
        cargarFrases('vacceso');

        $resultado['ok'] = TRUE;

        $nuevoPass = User::passRecover($email);
        $mensaje = cargarPlantillaEmail('lostpass');
        if ($nuevoPass['error'] == ERROR_NO_ERROR && $mensaje) {
            $mensaje['mensaje'] = str_replace(array('{pass}'), array($nuevoPass['pass']), $mensaje['mensaje']);
            $mensaje['mensajeTexto'] = str_replace(array('{pass}'), array($nuevoPass['pass']), $mensaje['mensajeTexto']);

            sendMail($email, FROM_NAME, FROM_EMAIL, $GLOBALS['textos']['recuperar'], $mensaje['mensaje'], $mensaje['mensajeTexto']);

            $resultado['menu'] = "{$GLOBALS['textos']['recuperarEmail']}";
        }
        else
            $resultado['menu'] = "{$GLOBALS['errores'][ERROR_GENERICO]}";

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }
    
    /**
     * Cambia la contraseña del usuario actual.
     * @param string $nuevoPass
     */
    public function cambiarPass($nuevoPass) {
        cargarFrases('vacceso');
        
        $resultado['ok'] = FALSE;
        $resultado['mensaje'] = $GLOBALS['errores'][ERROR_GENERICO];
        $usuario = comprobarLogin();
        
        if($usuario) {
            $nuevoPass = $usuario->update(FALSE, $nuevoPass);
            if($nuevoPass['error'] == ERROR_NO_ERROR) {
                $resultado['ok'] = TRUE;
                
                $resultado['mensaje'] = $GLOBALS['textos']['cambiadoPass'];
            }
            else {
                $resultado['mensaje'] = $GLOBALS['errores'][$nuevoPass['error']];
            }
        }
        
        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    public function guardarRutas($idsAudio) {
        $resultado['ok'] = FALSE;
        $usuario = comprobarLogin();

        if ($idsAudio && $usuario) {
            $arrayIds = explode(',', $idsAudio);
            $ruta = new Ruta(0, '', $arrayIds, $usuario->getIdUser());
            if ($ruta->grabar() == ERROR_NO_ERROR)
                $resultado['ok'] = TRUE;
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    public function borrarRutas($idsRuta) {
        $resultado['ok'] = FALSE;
        $usuario = comprobarLogin();

        if ($idsRuta && $usuario) {
            $arrayIds = explode(',', $idsRuta);
            foreach ($arrayIds as $idRuta)
                if (Ruta::borrar($idRuta))
                    $resultado['ok'] = TRUE;
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    public function cargarRutas() {
        $resultado['ok'] = FALSE;
        $usuario = comprobarLogin();

        if ($usuario) {
            $rutas = Ruta::cargarPorUsuario($usuario->getIdUser());
            if ($rutas) {
                $resultado['ok'] = TRUE;
                foreach ($rutas as $ruta) {
                    $resultado['datos'][$ruta->getIdRuta()] = $ruta->getIdsAudio();
                }
            }
        }

        $rutas = Ruta::listar();
        if ($rutas) {
            $resultado['ok'] = TRUE;
            foreach ($rutas as $ruta) {
                $resultado['datos'][$ruta->getIdRuta()] = $ruta->getIdsAudio();
            }
        }

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    /**
     * Indica si el usuario se logueó con facebook.
     */
    public function usoFacebook() {
        $usuario = comprobarLogin();

        if ($usuario && $usuario->getUsoFacebook())
            $resultado['ok'] = TRUE;
        else
            $resultado['ok'] = FALSE;

        echo get('callback') . '(' . json_encode($resultado) . ')';
    }

    public function enlazarArea($idAudio, $idArea, $area, $latitudIzquierdaInferior, $longitudIzquierdaInferior, 
            $latitudDerechaSuperior, $longitudDerechaSuperior) {
        $usuario = comprobarLogin();
        $audio = Audio::cargar($idAudio);
        $resultado = FALSE;

        if($audio && $audio->getIdUser() == $usuario->getIdUser()) {
        //if($audio) {
            $areaObj = Area::cargar($idArea);
            if(!$areaObj) {
                //Creamos el area porque no existe
                $areaObj = new Area($idArea, $area, $latitudIzquierdaInferior, $longitudIzquierdaInferior, $latitudDerechaSuperior, $longitudDerechaSuperior);
                if($areaObj->grabar() != ERROR_NO_ERROR) {
                    $areaObj = FALSE;
                }
            }

            if($areaObj) {
                //La asignamos
                $resultado = Audio::enlazarArea($audio->getIdAudio(), $areaObj->getIdArea());
            }
        }
        
        echo get('callback') . '(' . json_encode($resultado) . ')';
    }
}