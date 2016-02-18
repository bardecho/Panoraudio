<?php
include_once 'clases/audio.php';
require_once 'clases/idiomaaudio.php';
require_once 'clases/categoria.php';
require_once 'clases/preferencia.php';
require_once 'clases/area.php';
require_once 'utils/limitimagesize_helper.php';
require_once 'utils/rehash_helper.php';
cargarFrases('vmapa');
cargarFrases('vacceso');

class Mapa {
    private $js;
    
    public function __construct() {
        $this->js= '
                <script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>
                <script type="text/javascript" src="'.BASE_URL.'texto/'.$_SESSION['idioma'].'/cliente/vacceso.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'texto/'.$_SESSION['idioma'].'/cliente/vmapa.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/vclases.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/infobox_packed.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/least.min.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/VistaPerfil.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/VistaPrevia.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/VistaImagen.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/VistaLista.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/Slider.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/vmapa_google.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/vmapa_server.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/vmapa_user.js"></script>
                <script type="text/javascript" src="'.BASE_URL.'js/vmapa_rutas.js"></script>';
    }
    
    public function index() {
        //La lista de idiomasAudio
        $idiomasAudio=IdiomaAudio::listar();
        if($idiomasAudio['error'] != ERROR_NO_ERROR) $idiomasAudio['idiomasAudio']=FALSE;
        //La lista de categorías
        $categorias=Categoria::listar();
        if($categorias['error'] != ERROR_NO_ERROR) $categorias['categorias']=FALSE;
        
        $user=comprobarLogin();
        if($user) {
            //Las preferencias del usuario
            $configuracion = Preferencia::cargar($user->getIdUser());
            if($configuracion['error'] != ERROR_NO_ERROR) $configuracion['preferencia']=FALSE;
            if($configuracion['preferencia'] && is_array($configuracion['preferencia']->getCategoria())) {
                //Mandamos un array javascript con las preferencias activas
                $this->js.='<script type="text/javascript">listaCat=[';
                foreach($configuracion['preferencia']->getCategoria() as $categoria)
                    $this->js.=$categoria->getIdCategoria().',';
                $this->js=substr($this->js, 0, -1).'];</script>';
            }
        }
        else
            $configuracion['preferencia']=FALSE;
        
        mostrar('vmapa', array('js' => $this->js, 'mapa' => TRUE, 'idiomasAudio' => $idiomasAudio['idiomasAudio'], 
            'categorias' => $categorias['categorias'], 'configuracion' => $configuracion['preferencia'], 'logueado' => ($user !== FALSE)));
    }
    
    /**
     * Inserta un nuevo audio en la base de datos.
     * @param int $idCategoria El id de la categoría del nuevo audio.
     * @param int $idIdiomaAudio El id del idioma del nuevo audio.
     * @param float $latitud La latitud del audio.
     * @param float $longitud La longitud del audio.
     * @param $sonido El archivo de sonido.
     * @return string error -> (ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_ERROR)
     */
    public function subir() {
        $error='correcto';
        
        $user=comprobarLogin();
        if($user) {
            $idAudio=post('id');
            
            $ext=explode('.', $_FILES['audio']['name']);
            $ext=strtolower(trim($ext[count($ext)-1]));
            $archivo=uniqid().'.'.$ext;
            //Comprobamos que es de audio
            if(strtolower(substr(trim($_FILES['audio']['type']), 0, 5)) == 'audio' || $ext == 'amr' || $ext == 'ogg') {
                //Comprobamos el tamaño
                if($_FILES['audio']['size'] <= AUDIO_MAX) {
                    //Lo movemos
                    if(move_uploaded_file($_FILES['audio']['tmp_name'], 'sonido/'.$archivo)) {
                        if(!Audio::enlazar($idAudio, $archivo))
                            $error='errorSubida';
                    }
                    else $error='errorSubida';
                }
                else {
                    $error='audioMax';
                    $GLOBALS['textos']['audioMax']=str_replace('{size}', $_FILES['audio']['size'], $GLOBALS['textos']['audioMax']);
                }
            }
            else {
                $error='noAudio';
            }
            
            mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['textos'][$error]), BASE_URL.'index.php/mapa'), 'textoMensaje' => array($GLOBALS['textos'][$error])));
        }
        else 
            mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['errores'][ERROR_NO_INGRESO]), BASE_URL.'index.php/acceso'), 'textoMensaje' => array($GLOBALS['errores'][ERROR_NO_INGRESO])));
    }
    
    /**
     * Graba una nueva marca con su audio y una imagen[opcional].
     * @param int $idCategoria El id de la categoría del nuevo audio.
     * @param int $idIdiomaAudio El id del idioma del nuevo audio.
     * @param float $latitud La latitud del audio.
     * @param float $longitud La longitud del audio.
     * @param string $descripcion La descripción de la marca.
     * @param $sonido El archivo de sonido.
     */
    public function ponerMarca() {
        $error='correcto';
        
        $user=comprobarLogin();
        if(!$user) {
            if(post('entrar_email') && post('entrar_pass')) {
                $user = User::login(post('entrar_email'), post('entrar_pass'));
                $user = $user['user'];
            }
            elseif(post('facebookID')) {
                $user=User::loginFacebook(post('facebookID'));
                $user = $user['user'];
                if($user && $user->getUsuario() && $user->getUsuario() != post('facebookName')) {
                    $user = false;
                }
            }
        }

        if($user) {
            $idArea = '';
            if(post('idArea') && post('nombreArea')) {
                $areaObj = Area::cargar(post('idArea'));
                if(!$areaObj) {
                    //Creamos el area porque no existe
                    $areaObj = new Area(post('idArea'), post('nombreArea'), post('latitudIzquierdaInferior'), post('longitudIzquierdaInferior'), post('latitudDerechaSuperior'), post('longitudDerechaSuperior'));
                    if($areaObj->grabar() != ERROR_NO_ERROR) {
                        $areaObj = FALSE;
                    }
                }
                
                if($areaObj) {
                    $idArea = $areaObj->getIdArea();
                }
            }
            
            $audio = new Audio(0, new Categoria(post('categoria'), ''), $user->getIdUser(), '', 
                    new IdiomaAudio(post('idiomaAudio'), '', ''), post('latitud'), post('longitud'), 
                    0, FALSE, 1, post('descripcion'), 0, $idArea);
            $errorNumerico = $audio->grabar();
            if($errorNumerico == ERROR_NO_ERROR) {
                $idAudio=$audio->getIdAudio();

                $ext=explode('.', $_FILES['audio']['name']);
                $ext=strtolower(trim($ext[count($ext)-1]));
                $archivo=uniqid().'.'.$ext;
                //Comprobamos que es de audio
                if(strtolower(substr(trim($_FILES['audio']['type']), 0, 5)) == 'audio' || $ext == 'amr' || $ext == 'ogg') {
                    //Comprobamos el tamaño
                    if($_FILES['audio']['size'] <= AUDIO_MAX) {
                        //Lo movemos
                        if(move_uploaded_file($_FILES['audio']['tmp_name'], 'sonido/'.$archivo)) {
                            if(!Audio::enlazar($idAudio, $archivo))
                                $error='errorSubida';
                        }
                        else $error='errorSubida';
                    }
                    else {
                        $error='audioMax';
                        $GLOBALS['textos']['audioMax']=str_replace('{size}', $_FILES['audio']['size'], $GLOBALS['textos']['audioMax']);
                    }
                }
                else {
                    $error='noAudio';
                }
                
                //Grabamos la imagen en caso de exitir
                if(isset($_FILES['fondo']) && is_uploaded_file($_FILES['fondo']['tmp_name']) && filesize($_FILES['fondo']['tmp_name']) > 0) {
                    //Creamos la imagen grande
                    limitImageSize($_FILES['fondo']['tmp_name'], $_FILES['fondo']['name'], 1024, 768, 80, FALSE, FALSE, "img/fondos/$idAudio.jpg");
                    //Ahora la pequeña
                    limitImageSize($_FILES['fondo']['tmp_name'], $_FILES['fondo']['name'], 500, 200, 80, FALSE, FALSE, "img/fondos/{$idAudio}_mini.jpg");
                }

                if($error != 'correcto')
                    Audio::borrar($audio->getIdAudio());

                $this->enviarCodigoApp(($error == 'correcto' ? $audio->getIdAudio() : $error));
                mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['textos'][$error]), BASE_URL.'index.php/mapa'), 'textoMensaje' => array($GLOBALS['textos'][$error])));
            }
            else {
                $this->enviarCodigoApp(-($errorNumerico));
                mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['errores'][$errorNumerico]), BASE_URL.'index.php/mapa'), 'textoMensaje' => array($GLOBALS['errores'][$errorNumerico])));
            }
        }
        else {
            $this->enviarCodigoApp(-(ERROR_NO_INGRESO));
            mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['errores'][ERROR_NO_INGRESO]), BASE_URL.'index.php/acceso'), 'textoMensaje' => array($GLOBALS['errores'][ERROR_NO_INGRESO])));  
        }
    }
    
    /**
     * Convierte un código de la aplicación a app.
     * @param mixed $codigoError
     */
    private function enviarCodigoApp($codigoError) {
        if(post('enviarMensaje')) {
            if(!is_int($codigoError)) {
                switch ($codigoError) {
                    case 'errorSubida':
                        $codigoError = -64;
                        break;
                    
                    case 'audioMax':
                        $codigoError = -128;
                        break;
                    
                    case 'noAudio':
                        $codigoError = -256;
                        break;
                }
            }
            
            echo $codigoError;
            exit;
        }
    }
}
