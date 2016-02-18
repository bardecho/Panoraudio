<?php
require_once 'clases/idiomaaudio.php';
require_once 'clases/categoria.php';
require_once 'clases/preferencia.php';
cargarFrases('vconfig');

class Config {
    private $js;
    
    public function __construct() {
        $this->js='<script type="text/javascript" src="'.BASE_URL.'texto/'.$_SESSION['idioma'].'/cliente/vconfig.js"></script>'.
                '<script type="text/javascript" src="'.BASE_URL.'js/vconfig.js"></script>';
    }
    
    public function index() {
        $user=comprobarLogin();
        if($user) {
            //La lista de idiomasAudio
            $idiomasAudio=IdiomaAudio::listar();
            if($idiomasAudio['error'] != ERROR_NO_ERROR) $idiomasAudio['idiomasAudio']=FALSE;
            //La lista de categorías
            $categorias=Categoria::listar();
            if($categorias['error'] != ERROR_NO_ERROR) $categorias['categorias']=FALSE;
            //Las preferencias del usuario
            $configuracion = Preferencia::cargar($user->getIdUser());
            if($configuracion['error'] != ERROR_NO_ERROR) $configuracion['preferencia']=FALSE;

            mostrar('vconfig', array('js' => $this->js,'idiomasAudio' => $idiomasAudio['idiomasAudio'], 
                'categorias' => $categorias['categorias'], 'configuracion' => $configuracion['preferencia']));
        }
        else
            mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['errores'][ERROR_NO_INGRESO]), BASE_URL.'index.php/acceso'), 'textoMensaje' => array($GLOBALS['errores'][ERROR_NO_INGRESO])));
    }
    
    /**
     * Modifica la configuración de la app en la base de datos.
     * int idiomasAudio El id del idiomaAudio.
     * int puntuacionMinima La puntuación mínima deseada para los audios.
     * int categorias Los ids de las categorías deseadas.
     * error(ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO)
     */
    public function modificarConfiguracion() {
        $user=comprobarLogin();
        if($user) {
            $idiomasAudio=post('idiomasAudio');
            $puntuacionMinima=post('puntuacionMinima');$puntuacionMinima=0;
            $categorias=post('categorias');
            $idiomaAplicacion=post('idioma');

            if ($idiomasAudio && $puntuacionMinima !== FALSE && $categorias && $idiomaAplicacion) {
                $user = unserialize($_SESSION['user']);
                $preferencia = Preferencia::cargar($user->getIdUser());
                if ($preferencia['error'] == ERROR_NO_ERROR) {
                    //Cambiamos el idioma de la aplicación
                    $idioma=unserialize($_SESSION['claseIdioma']);
                    $idioma->establecerIdioma($idiomaAplicacion);
                    //Las categorías
                    foreach ($categorias as $categoriaId)
                        $categoriasArray[] = new Categoria($categoriaId, '');
                    $preferencia['preferencia']->setCategoria($categoriasArray);
                    //El usuario
                    $preferencia['preferencia']->setIdUser($user->getIdUser());
                    //Los idiomas del audio
                    foreach($idiomasAudio as $idiomaAudioId)
                        $idiomasAudioArray[] = new IdiomaAudio($idiomaAudioId, '', '');
                    $preferencia['preferencia']->setIdiomaAudio($idiomasAudioArray);
                    //La puntuación mínima
                    $preferencia['preferencia']->setPuntuacionMinima($puntuacionMinima);
                    //Guardamos los cambios
                    if($preferencia['preferencia']->actualizar()) $resultado['error'] = ERROR_NO_ERROR;
                    else $resultado['error'] = ERROR_GENERICO;
                }
                else $resultado['error'] = $preferencia['error'];
            }
            else
                $resultado['error'] = ERROR_FALTA_DATO;

            if($resultado['error'] == ERROR_NO_ERROR) mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['textos']['guardada']), BASE_URL.'index.php/mapa'), 'textoMensaje' => array($GLOBALS['textos']['guardada'])));
            else mostrar('vconfig', array('mensaje' => mostrarMensajes(errores($resultado['error']), BASE_URL.'index.php/config'), 'textoMensaje' => errores($resultado['error'])));
        }
        else
            mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['errores'][ERROR_NO_INGRESO]), BASE_URL.'index.php/acceso'), 'textoMensaje' => array($GLOBALS['errores'][ERROR_NO_INGRESO])));
    }
}
