<?php
require_once 'utils/rehash_helper.php';
require_once 'utils/limitimagesize_helper.php';
require_once 'clases/preferencia.php';
require_once 'clases/idiomaaudio.php';
require_once 'clases/categoria.php';
require_once 'clases/audio.php';
require_once 'clases/puntuacion.php';
require_once 'clases/facebook/facebook.php';
cargarFrases('vacceso');

/**
 * Controla el acceso al sistema. 
 */
class Acceso {
    private $js;
    
    public function __construct() {
        $this->js='<script type="text/javascript" src="'.BASE_URL.'texto/'.$_SESSION['idioma'].'/cliente/vacceso.js"></script>'.
                '<script type="text/javascript" src="'.BASE_URL.'js/vacceso.js"></script>';
    }

    /**
     * Muestra la página para loguearse o registrarse. 
     */
    public function index() {
        mostrar('vacceso', array('js' => $this->js));
    }
    
    /**
     * Loguea al usuario.
     * string pass La contraseña con la que intenta loguearse.
     * string email El email o usuario con el que quiere loguearse.
     * 
     * (ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_COINCIDE).
     */
    public function login($usarFacebook = FALSE) {
        if($usarFacebook == 'conFacebook') {
            if(post('facebookID') && post('facebookName')) {
                $result=User::loginFacebook(post('facebookID'));
                if($result['error'] == ERROR_NO_COINCIDE) {
                    //Lo registramos e intentamos loguearle de nuevo
                    $registrado = User::registroFacebook(post('facebookID'));
                    if($registrado['error'] == ERROR_NO_ERROR) {
                        $result=User::loginFacebook(post('facebookID'));
                        if(is_a($result['user'], 'User')) {
                            //Le añadimos el nombre de usuario
                            if(!$result['user']->getUsuario()) {
                                $result['user']->update(FALSE, FALSE, post('facebookName'));
                            }
                            
                            if($result['user']->getUsuario() && $result['user']->getUsuario() != post('facebookName')) {
                                $result['error'] == ERROR_NO_COINCIDE;
                            }
                        }
                    }
                }
                
                if($result['error'] == ERROR_NO_ERROR && !is_file("img/fotosPerfil/{$result['user']->getIdUser()}.jpg")) {
                    $this->obtenerFotoPerfilFacebook(post('facebookID'), $result['user']->getIdUser());
                }

		if($result['error'] == ERROR_NO_ERROR && !$result['user']->getUsuario()) {
                    $result['user']->update(FALSE, FALSE, post('facebookName'));
                }
            }
            else {
                $config = array(
                    'appId' => '1408832206028267',
                    'secret' => '207ceee66eb707c91eb32d81d8c2a608',
                    'fileUpload' => false, // optional
                    'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
                );

                $facebook = new Facebook($config);
                $result=User::loginFacebook($facebook->getUser());
                if($result['error'] == ERROR_NO_COINCIDE) {
                    //Lo registramos e intentamos loguearle de nuevo
                    $registrado = User::registroFacebook($facebook->getUser());
                    if($registrado['error'] == ERROR_NO_ERROR)
                        $result=User::loginFacebook($facebook->getUser());
                }

                if(is_a($result['user'], 'User')) {
                    //Le añadimos el nombre de usuario y el email
                    if(!$result['user']->getUsuario() || !$result['user']->getEmail()) {
                        $user_profile = $facebook->api('/me','GET');
                        $result['user']->update($user_profile['email'], FALSE, ($user_profile['name'] ? $user_profile['name'] : $user_profile['first_name']));
                    }
                }
                            
                if($result['error'] == ERROR_NO_ERROR && !is_file("img/fotosPerfil/{$result['user']->getIdUser()}.jpg")) {
                    $this->obtenerFotoPerfilFacebook($facebook->getUser(), $result['user']->getIdUser());
                }
            }
        }
        else {
            $result=User::login(post('entrar_email'), post('entrar_pass'));
        }

        if($result['error'] == ERROR_NO_ERROR) {
            //Guardamos firebase
            if(post('firebase') && post('firebase') != $result['user']->getFireBase()) {
                $result['user']->setFireBase(post('firebase'));
                $result['user']->actualizarFireBase();
            }
            
            //Metemos al usuario en la sesión
            $_SESSION['user']=serialize($result['user']);

            //Y redireccionamos
            /*$preferencia = Preferencia::cargar($result['user']->getIdUser());
            if(!$preferencia['preferencia']->getIdiomaAudio() || !$preferencia['preferencia']->getCategoria()) {
                //A la configuración si no tiene ni categorías ni idiomas
                redireccionar(BASE_URL.'index.php/config');
            }
            else {
                //En caso contrario al mapa
                redireccionar(BASE_URL.'index.php/mapa');
            }*/
            //Directos al mapa
            
            //Descargas filtradas
            $idiomasAudio = array(cargarIdiomaAudio()); //Este sistema tiene preferencia sobre el de la bd

            $preferencia = Preferencia::cargar($result['user']->getIdUser());
            $categorias = $preferencia['preferencia']->getCategoria();
            if (!$idiomasAudio[0]) {
                $idiomasAudio = $preferencia['preferencia']->getIdiomaAudio();
            }
            $puntuacionMinima = $preferencia['preferencia']->getPuntuacionMinima();

            //Devolvemos todos los audios de las categorías
            $categorias = Categoria::listar();
            //Sobreescribimos el idioma si llega por post
            if(post('idioma') !== FALSE) {
                if (!post('idioma')) {
                    $idiomasAudio = array(new IdiomaAudio(0, '', ''));
                }
                else {
                    $idiomasAudio = array(IdiomaAudio::cargar(post('idioma')));
                }
            }

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
            
            $descargasAudios = array();
            if ($audios) {
                foreach($audios as $audio) {
                    $descargasAudios[$audio->getIdAudio()] = $audio->getDescargas();
                }
            }

            $this->enviarCodigoApp($result['user']->getIdUser(), array('seguidos' => $result['user']->obtenerSeguidos(), 'resultado' => $result['user']->getIdUser(), 
                'audiosPositivos' => $result['user']->obtenerPuntuadosPositivos(), 'descargas' => $descargasAudios));
            redireccionar(BASE_URL.'index.php/mapa');
        }
        else {
            $this->enviarCodigoApp(array('seguidos' => array(), array('resultado' => -($result['error']))));
            mostrar('vmensaje', array('mensaje' => mostrarMensajes(errores($result['error']), BASE_URL.'index.php/mapa'), 'textoMensaje' => errores($result['error'])));
        }
    }
    
    /**
     * Se descarga la foto de perfil de Facebook.
     * @param int $idFacebook
     * @param int $idUsuario
     */
    private function obtenerFotoPerfilFacebook($idFacebook, $idUsuario) {
        if(descargarArchivo("http://graph.facebook.com/$idFacebook/picture?height=800&width=800", "img/temp/$idUsuario.jpg")) {
            //Lo movemos
            limitImageSize("img/temp/$idUsuario.jpg", "$idUsuario.jpg", 800, 800, 85, FALSE, FALSE, "img/fotosPerfil/$idUsuario.jpg");
            unlink("img/temp/$idUsuario.jpg");
        }
    }
    
    /**
     * Convierte un código de la aplicación a app.
     * @param mixed $codigoError
     * @param array $codigoExtendido
     */
    private function enviarCodigoApp($codigoError, $codigoExtendido = array()) {
        if(post('enviarMensaje')) {
            if($codigoExtendido) {
                echo json_encode($codigoExtendido);
            }
            else {
                echo $codigoError;
            }
            exit;
        }
    }
    
    /**
     * Registra un nuevo usuario.
     * string email El email a registrar.
     * string pass La contraseña correspondiente.
     * String usuario El nombre de usuario a registrar.
     * (ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_EMAIL_EXISTENTE, ERROR_USUARIO_EXISTENTE)
     */
    public function registro() {
        $return=User::register(post('email'), post('pass'), post('usuario'));
        $mensaje = cargarPlantillaEmail('registration');

        if($return['error'] == ERROR_NO_ERROR && $mensaje) {
            $mensaje['mensaje'] = str_replace(array('{activationCode}', '{baseUrl}'), array($return['activationCode'], BASE_URL), $mensaje['mensaje']);
            $mensaje['mensajeTexto'] = str_replace(array('{activationCode}', '{baseUrl}'), array($return['activationCode'], BASE_URL), $mensaje['mensajeTexto']);
            
            sendMail(post('email'), FROM_NAME, FROM_EMAIL, $GLOBALS['textos']['registro_titulo'], $mensaje['mensaje'], $mensaje['mensajeTexto']);
            unset($return['activationCode']);
            
            $this->enviarCodigoApp($return['error']);
            mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['textos']['mensaje_registrado']), BASE_URL.'index.php/mapa'), 'textoMensaje' => array($GLOBALS['textos']['mensaje_registrado'])));
        }
        else {
            $this->enviarCodigoApp(-($return['error']));
            mostrar('vmensaje', array('mensaje' => mostrarMensajes(errores($return['error']), BASE_URL.'index.php/mapa'), 'textoMensaje' => array($GLOBALS['textos']['mensaje_registrado'])));
        }
    }
    
    /**
     * Activa un usuario.
     * @param string $activationCode El código que lo identifica para la activación.
     */
    public function activar($activationCode) {
        if(User::activate($activationCode)) {
            mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['textos']['mensaje_activado']), BASE_URL.'index.php/mapa'), 'textoMensaje' => array($GLOBALS['textos']['mensaje_activado'])));
        }
        else {
            mostrar('vmensaje', array('mensaje' => mostrarMensajes(array($GLOBALS['errores'][ERROR_GENERICO]), BASE_URL.'index.php/mapa'), 'textoMensaje' => array($GLOBALS['errores'][ERROR_GENERICO])));
        }
    }
    
    public function salir() {
        if(isset($_SESSION['user'])) unset($_SESSION['user']);
        if(isset($_SESSION['clave'])) unset($_SESSION['clave']);
        if(isset($_SESSION['claseIdioma'])) unset($_SESSION['claseIdioma']);
        session_destroy();
        
        redireccionar(BASE_URL.'index.php/mapa');
    }
    
    public function limpiar() {
        $db=new DB();
        $db->alterData('DELETE FROM at_user WHERE email = "cityvillecuenta1@gmail.com"');
        $db->alterData('DELETE FROM at_user WHERE email = "cityvillecuenta2@gmail.com"');
        
        echo 'ok';
        exit;
    }
    
    public function eliminarCuenta() {
        $user = comprobarLogin();
        if ($user) {
            //Borramos los audios
            $audios = Audio::cargarPorUsuario($user->getIdUser());
            foreach($audios as $audio) {
                $audio->borradoCompleto();
            }
            
            //Borramos el usuario
            unlink('img/fotosPerfil/'.$user->getIdUser().'.jpg');
            $user->remove();
        }

        //Cerramos sesión
        $this->salir();
    }
}
