<?php
//Configuración
ini_set('display_errors', 0);
//ini_set('log_errors', 'log/errores');
//Constantes
define('FROM_NAME', 'Panoraudio');
define('FROM_EMAIL', 'contact@panoraudio.com');
define('BASE_URL', 'http://localhost/tellme/'); //FALTA SSL
define('BASE_URL_IMG', 'http://localhost/tellme/'); //FALTA SSL
define('BASE_URL_AUDIO', 'http://localhost/tellme/'); //FALTA SSL
define('CONTROLADOR_INICIAL', 'mapa');
define('AUDIO_MAX', 8000000);
define('DOMINIO_COOKIE', 'app.panoraudio.com');
//Los errores
define('ERROR_NO_ERROR', 0);
define('ERROR_GENERICO', 1);
define('ERROR_FALTA_DATO', 2);
define('ERROR_EMAIL_EXISTENTE', 4);
define('ERROR_NO_COINCIDE', 8);
define('ERROR_NO_INGRESO', 16);
define('ERROR_USUARIO_EXISTENTE', 32);

//Cargamos archivos comunes
require_once 'utils/functions.php';
require_once 'models/db.php';
require_once 'models/bdpreparada.php';
require_once 'clases/user.php';
require_once 'clases/idioma.php';

//La sesión
if(post('logueo')) {
    //Intenta loguearse
    if(post('recordar')) {
        //Quiere mantener la sesión
        $duracion = 365*24*60*60;
    }
    else {
        //No quiere mantener la sesión
        $duracion = 0;
    }

    session_id(uniqid('', FALSE));
}
else {
    //Sesión estándar
    $duracion = 0;
}

session_set_cookie_params($duracion, '/', DOMINIO_COOKIE, FALSE, TRUE); //FALTA SSL

session_start();

//Tipo de dispositivo
$_SESSION['dispositivoMovil'] = tipoDispositivo();

//Ejecutamos las cookies en modo retardado
if(!empty($_SESSION['galletitas'])) {
    $galletitas = unserialize($_SESSION['galletitas']);
    unset($_SESSION['galletitas']);
    
    foreach($galletitas as $galleta)
        setcookie($galleta[0], $galleta[1], $galleta[2], $galleta[3], $galleta[4], $galleta[5], $galleta[6]);
}

//La ip debe coincidir
if(!isset($_SESSION['ip'])) $_SESSION['ip']=getRealIP();
elseif($_SESSION['ip'] != getRealIP()) exit;

//Comprobamos que la clave de seguridad para datos post es correcta
//if(empty($_POST['clave']) || empty($_SESSION['clave']) || $_POST['clave'] != $_SESSION['clave']) unset($_POST);
//Generamos una clave nueva si no es una solicitud ajax
if(isset($_SERVER['PATH_INFO']))
    $path=explode('/', $_SERVER['PATH_INFO']);
if(empty($path[1]) || $path[1] != 'ajax') $_SESSION['clave']=passGenerator();

//Siempre por conexión segura
/*if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
    if(isset($_SERVER['PATH_INFO'])) 
        redireccionar(BASE_URL.'index.php'.$_SERVER['PATH_INFO']);
    else
        redireccionar(BASE_URL);
}*/

//Nos aseguramos de que tenemos la clase idioma en sesión
if(empty($_SESSION['claseIdioma'])) {
    $_SESSION['claseIdioma']=serialize(new Idioma());
}
//Comprobamos el idioma
$idiomaActual=unserialize($_SESSION['claseIdioma']);
if($idiomaActual->idiomaActual() === FALSE)
    $idiomaActual->establecerIdioma();

//Cargamos las frases de error
cargarFrases('errores');
cargarFrases('vmapa');

$parametros=array();

//Cargamos e instanciamos el controlador
if(isset($_SERVER['PATH_INFO'])) {
    //index.php/controlador/función/parametro1
    $path=explode('/', $_SERVER['PATH_INFO']);
    if(empty($path[1])) {
        //Controlador por defecto
        require_once 'controllers/'.CONTROLADOR_INICIAL.'.php';
        $nombre=CONTROLADOR_INICIAL;
        $controlador=new $nombre;
    }
    else {
        //Controlador indicado a no ser que no exista
        if(is_file('controllers/'.$path[1].'.php')) require_once 'controllers/'.$path[1].'.php';
        else exit('Error al cargar el controlador.');
        $controlador=new $path[1];
        //Parámetros url limpia
        foreach($path as $id => $parametro) {
            if($id > 2) $parametros[]=strip_tags($parametro);
        }
    }
}
else {
    //Controlador por defecto
    require_once 'controllers/'.CONTROLADOR_INICIAL.'.php';
    $nombre=CONTROLADOR_INICIAL;
    $controlador=new $nombre;
}

//Cargamos la función solicitada o index en su defecto
if(empty($path[2])) call_user_func_array(array($controlador, 'index'), (array)$parametros);
else call_user_func_array(array($controlador, $path[2]), (array)$parametros);
