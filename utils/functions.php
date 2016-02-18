<?php
//Returns a $_POST value without tags or FALSE
function post($key) {
    $result=FALSE;

    if(isset($_POST[$key])) {
        if(is_array($_POST[$key])) {
            array_walk_recursive($_POST[$key], 'stripTags');
            $result=$_POST[$key];
        }
        else
            $result=strip_tags($_POST[$key]);
    }

    return $result;
}

//Returns a $_GET value without tags or FALSE
function get($key) {
    $result=FALSE;

    if(isset($_GET[$key])) {
        if(is_array($_GET[$key])) {
            array_walk_recursive($_GET[$key], 'stripTags');
            $result=$_GET[$key];
        }
        else
            $result=strip_tags($_GET[$key]);
    }

    return $result;
}

function stripTags(&$var) {
    $var=strip_tags($var);
}

//Try to obtain the client real ip
function getRealIP() {
   if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
      $client_ip =
         (!empty($_SERVER['REMOTE_ADDR'])) ?
            $_SERVER['REMOTE_ADDR']
            :
            ((!empty($_ENV['REMOTE_ADDR'])) ?
               $_ENV['REMOTE_ADDR']
               :
               FALSE);

      $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);

      reset($entries);
      while (list(, $entry) = each($entries)) {
         $entry = trim($entry);
         if(preg_match("/^([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)/", $entry, $ip_list)) {
            $private_ip = array(
                  '/^0\\./',
                  '/^127\\.0\\.0\\.1/',
                  '/^192\\.168\\..*/',
                  '/^172\\.((1[6-9])|(2[0-9])|(3[0-1]))\\..*/',
                  '/^10\\..*/');

            $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

            if ($client_ip != $found_ip) {
               $client_ip = $found_ip;
               break;
            }
         }
      }
   }
   else {
      $client_ip =
         (!empty($_SERVER['REMOTE_ADDR'])) ?
            $_SERVER['REMOTE_ADDR']
            :
            ((!empty($_ENV['REMOTE_ADDR'])) ?
               $_ENV['REMOTE_ADDR']
               :
               FALSE);
   }

   return $client_ip;
}

function sendMail($toEmail, $fromName, $fromEmail, $subject, $message, $messageText) { 
    require_once 'clases/mailer/class.phpmailer.php';

    $mail = new PHPMailer();

    $mail->isSMTP(); // Set mailer to use SMTP
    $mail->Host = 'localhost'; // Specify main and backup server
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'contact@panoraudio.com'; // SMTP username
    $mail->Password = 'indianajones7'; // SMTP password
    $mail->SMTPSecure = 'tls'; // Enable encryption, 'ssl' also accepted
    
    //$mail->IsMail();

    $mail->From = $fromEmail;
    $mail->FromName = $fromName;
    $mail->AddAddress($toEmail); 
    $mail->AddReplyTo($fromEmail, $fromName);

    $mail->WordWrap = 50;                                 // set word wrap to 50 characters
    //$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
    $mail->IsHTML(true);                                  // set email format to HTML

    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->AltBody = $messageText;
    
    $mail->CharSet = 'UTF-8';

    //se envia el mensaje, si no ha habido problemas 
    //la variable $exito tendra el valor true
    $exito = $mail->Send();

    //Si el mensaje no ha podido ser enviado se realizaran 4 intentos mas como mucho 
    //para intentar enviar el mensaje, cada intento se hara 5 segundos despues 
    //del anterior, para ello se usa la funcion sleep	
    $intentos=1; 
    while ((!$exito) && ($intentos < 2)) {
        sleep(2);
        //echo $mail->ErrorInfo;
        $exito = $mail->Send();
        $intentos=$intentos+1;	
    }
}

/**
 * Generate 12 caracters ramdom password.
 * @return String 12 caracter lenght pass.
 */
function passGenerator() {
    mt_srand(time());
    //Generate new pass
    $pass='';
    //Uppercase letters
    for($i=0;$i < 4;$i++) $pass.=chr(mt_rand(65, 90));
    //Lowercase letters
    for($i=0;$i < 4;$i++) $pass.=chr(mt_rand(97, 122));
    //Numbers
    for($i=0;$i < 4;$i++) $pass.=chr(mt_rand(48, 57));
    //Ramdomizing positions
    for($i=0;$i < 12;$i++) {
        $auxPosition1=mt_rand(0, 11);
        $auxPosition2=mt_rand(0, 11);

        $aux=$pass[$auxPosition1];
        $pass[$auxPosition1]=$pass[$auxPosition2];
        $pass[$auxPosition2]=$aux;
    }

    return $pass;
}

//Muestra una vista
function mostrar($cuerpo, $datos=NULL) {
    //Selección de interfaz
    if(isset($_GET['movil'])) {
        $carpeta = 'movil';
    }
    elseif(isset($_GET['escritorio'])) {
        $carpeta = 'escritorio';
    }
    else {
        if($_SESSION['dispositivoMovil'] == 2) {
            $carpeta = 'movil';
        }
        else {
            $carpeta = 'escritorio';
        }
    }
    
    //Primero ponemos la cabecera
    require 'views/vcabecera.php';
    //Ahora el cuerpo solicitado
    if(is_file("views/$carpeta/$cuerpo.php"))
        require "views/$carpeta/$cuerpo.php";
    //Ahora el pie
    require 'views/vpie.php';
}

//Hay que llamar a esta función en cada formulario para pasar la comprovación de clave
function ponerClave() {
    return "<input type='hidden' name='clave' value='{$_SESSION['clave']}'/>";
}

//Redirecciona la página
function redireccionar($url) {
    @header("Location: $url");
    echo "<script type='text/javascript'>location='$url';</script>";
    exit;
}

/**
 * Devuelve un array con todos los mensajes de error basándose en un código numérico.
 * @param int $codigo El código de error.
 * @return array Un array de strings o FALSE en caso de error.
 */
function errores($codigo) {
    //Los códigos deben estar ordenados de mayor a menor
    $resultado=FALSE;
    foreach($GLOBALS['errores'] as $indice => $mensaje) {
        if($codigo >= $indice) {
            $resultado[]=$mensaje;
            $codigo -= $indice;
            if($codigo == 0) break;
        }
    }

    return $resultado;
}

//Muestra un alert y después redirecciona para que no vuelva a aparecer
function mostrarMensajes($mensajes, $url) {
    $resultado='<script type="text/javascript">alert("';
    foreach($mensajes as $mensaje)
        $resultado .= $mensaje.'\n';
    $resultado=substr($resultado, 0, -2).'");window.location="'.$url.'";</script>';

    return $resultado;
}

function cargarFrases($vista) {
    //Cargamos el archivo de idioma correspondiente
    if(is_file("texto/{$_SESSION['idioma']}/servidor/$vista.php"))
        require_once "texto/{$_SESSION['idioma']}/servidor/$vista.php";
}

/**
 * Devuelve el usuario logueado o FALSE.
 * @return User
 */
function comprobarLogin() {
    $resultado=FALSE;
    
    if(isset($_SESSION['user']) && is_a(unserialize($_SESSION['user']), 'User')) 
        $resultado=unserialize($_SESSION['user']);

    return $resultado;
}

function registrarDepuracion($texto) {
    $hoy=new DateTime();
    file_put_contents('log/mensajes', $hoy->format('Y-m-d H:i:s')."\n$texto\n\n", FILE_APPEND);
}

function traducirLista($id) {
    require_once "texto/{$_SESSION['idioma']}/servidor/listas.php";
    
    if($id == 0) $resultado=$GLOBALS['idiomaAudio'][0];
    else $resultado=$GLOBALS['categorias'][$id];
    
    return $resultado;
}

function convertirURL($texto) { 
    $re='((?:http|https)(?::\/{2}[\w]+)(?:[\/.]?)(?:[^\s"]*))';

    return preg_replace("/".$re."/is", '<a href="$0" target="_blank">$0</a>', $texto);
}

function grabarIdiomaAudio($idiomaAudio) {
    if($idiomaAudio instanceof IdiomaAudio)        
        grabarGalletitaRetardada(array('idiomaAudio', $idiomaAudio->getIdIdiomaAudio(), time()+60*60*24*30, '/', DOMINIO_COOKIE, FALSE, FALSE)); //Falta ssl
}

function cargarIdiomaAudio() {
    $resultado = false;
    
    if(isset($_COOKIE['idiomaAudio']))
        $resultado = IdiomaAudio::cargar($_COOKIE['idiomaAudio']);
    else {
        $idioma=unserialize($_SESSION['claseIdioma']);
        $resultado = IdiomaAudio::cargarPorSiglas($idioma->idiomaActual());
        $resultado = $resultado['idiomaAudio'];
    }
    
    return $resultado;
}

/**
 * Carga las categorías de una cookie.
 * @return array Un array de categorías.
 */
function cargarCategoriasAudio() {
    if(isset($_SESSION['categoriasAudio'])) {
        $categorias = json_decode($_SESSION['categoriasAudio']);
    }
    elseif(isset($_COOKIE['categoriasAudio'])) {
        $categorias = json_decode($_COOKIE['categoriasAudio']);
    }

    if(isset($categorias)) {
        foreach($categorias as $idCategoria) {
            $resultadoTmp = Categoria::cargar(FALSE, FALSE, $idCategoria);
            if($resultadoTmp)
                $resultado[] = $resultadoTmp;
        }
    }
    else {
        $resultado = array(new Categoria(0, ''));
    }

    return $resultado;
}

/**
 * Graba las categorías del usuario en una cookie.
 * @param array $categoriasAudio Un array de categorías
 */
function grabarCategoriasAudio($categoriasAudio) {
    $ids = array();
    
    foreach($categoriasAudio as $categoriaAudio)
        $ids[] = $categoriaAudio->getIdCategoria();

    $ids = json_encode(array_unique($ids));

    //También lo metemos en sesión para corregir el retardo
    $_SESSION['categoriasAudio'] = $ids;
    eliminarGalletitaRetardada('categoriasAudio');
    grabarGalletitaRetardada(array('categoriasAudio', $ids, time()+60*60*24*30, '/', DOMINIO_COOKIE, FALSE, FALSE)); //Falta ssl
}

/**
 * Graba en la sesión un array de arrays para mandar las galletitas en la siguiente solicitud
 * @param array $nuevaGalletita Mismos parámetros que setcookie.
 */
function grabarGalletitaRetardada($nuevaGalletita) {
    if(!empty($_SESSION['galletitas']))
        $galletitas = unserialize($_SESSION['galletitas']);
    
    $galletitas[] = $nuevaGalletita;
    
    $_SESSION['galletitas'] = serialize($galletitas);
}

/**
 * Borra todas las entradas de galletitaRetardada por el nombre.
 * @param string $nombre
 */
function eliminarGalletitaRetardada($nombre) {
    if(!empty($_SESSION['galletitas'])) {
        $galletitas = unserialize($_SESSION['galletitas']);
        foreach($galletitas as $indice => $galletita) {
            if($galletita[0] == $nombre) {
                unset($galletitas[$indice]);
            }
        }
        $_SESSION['galletitas'] = serialize($galletitas);
    }
}

/**
 * Devuelve la plantilla seleccionada en el idioma actual o en el idioma por defecto de no existir.
 * @param string $nombrePlantilla El nombre de la versión html de la plantilla sin extensión.
 * @return array ['mensaje','mensajeTexto'] o FALSE si no se encuentra la plantilla.
 */
function cargarPlantillaEmail($nombrePlantilla) {
    $resultado = FALSE;
    $idioma = unserialize($_SESSION['claseIdioma']);
    $ruta = "templates/{$idioma->idiomaActual()}/$nombrePlantilla";
    if(!is_file($ruta.'.php')) {
        $idiomaPredeterminado = Idioma::IDIOMA_PREDETERMINADO;
        $ruta = "templates/$idiomaPredeterminado/$nombrePlantilla";
    }
    
    if(is_file($ruta.'.php')) {
        $resultado['mensaje'] = file_get_contents($ruta.'.php');
        $resultado['mensajeTexto'] = file_get_contents($ruta.'Text.php');
    }
    
    return $resultado;
}

/**
 * Devuelve si es un ordenador (3), una tablet (1) o un movil (2).
 * @return int
 */
function tipoDispositivo() {
    $tablet_browser = 0;
    $mobile_browser = 0;
    $body_class = 'desktop';

    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
        $tablet_browser++;
        $body_class = "tablet";
    }

    if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
        $mobile_browser++;
        $body_class = "mobile";
    }

    if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
        $mobile_browser++;
        $body_class = "mobile";
    }

    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
    $mobile_agents = array(
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
        'newt','noki','palm','pana','pant','phil','play','port','prox',
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
        'wapr','webc','winw','winw','xda ','xda-');

    if (in_array($mobile_ua,$mobile_agents)) {
        $mobile_browser++;
    }

    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'opera mini') > 0) {
        $mobile_browser++;
        //Check for tablets on opera mini alternative headers
        $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
          $tablet_browser++;
        }
    }
    if ($tablet_browser > 0) {
        $resultado = 1;
    }
    else if ($mobile_browser > 0) {
        $resultado = 2;
    }
    else {
        $resultado = 3;
    }
    
    return $resultado;
}

/**
 * Se descarga un archivo al disco local.
 * @param string $urlOrigen
 * @param string $rutaDestino
 * @return boolean
 */
function descargarArchivo($urlOrigen, $rutaDestino) {
    $resultado = false;
    $intentos = 1;
    $file = fopen($rutaDestino, 'w');
    
    $ch = curl_init($urlOrigen);
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; es-ES; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_FILE, $file);

    curl_exec($ch);
    $info = curl_getinfo($ch);
    while($intentos < 10 && $info['http_code'] >= 300 && $info['http_code'] <= 399) {
        curl_setopt($ch, CURLOPT_URL, $info['redirect_url']);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        
        $intentos++;
    }
    
    curl_close($ch);
    fclose($file);
    
    if($info['http_code'] >= 200 && $info['http_code'] <= 299) {
        $resultado = true;
    }
    
    return $resultado;
}