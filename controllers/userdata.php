<?php

/**
 * Description of userdata
 *
 * @author marcbardecho
 */
class UserData {

    /**
     * Actualiza los datos del usuario.
     * @param string $newEmail El nuevo email del usuario.
     * @param string $newPass La nueva contraseña del usuario.
     * @return array error=(ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_EMAIL_EXISTENTE, 
     * ERROR_NO_INGRESO)
     */
    public function update($newEmail = FALSE, $newPass = FALSE, $newUsuario = FALSE) {
        $user = unserialize($_SESSION['user']);
        $returnData = $user->update($newEmail, $newPass, $newUsuario);
        $_SESSION['user'] = serialize($user);

        if ($returnData['error'] == ERROR_NO_ERROR && $newEmail && isset($returnData['activationCode'])) {
            $message = file_get_contents('templates/registration.php');
            $message = str_replace('{activationCode}', $returnData['activationCode'], $message);
            sendMail($newEmail, FROM_NAME, FROM_EMAIL, 'Audio registro', $message);
            unset($returnData['activationCode']);
        }

        return $returnData;
    }

    /**
     * Encuentra los audios al alcance.
     * @param float $latitud La latitud del sujeto.
     * @param float $longitud La longitud del sujeto.
     * @return array audios[] = (la => latitud del audio, lo => longitud del audio, ruta => la ruta del archivo, 
     * id => id del audio, puntos => la puntuación del audio, categoria => la categoría del audio), 
     * error=(ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO)
     */
    public function synch($latitud, $longitud) {
        $resultado['error'] = ERROR_GENERICO;
        $user = unserialize($_SESSION['user']);

        if ($longitud !== NULL && $longitud !== NULL) {
            $preferencia = Preferencia::cargar($user->getIdUser());
            $audios = Audio::localizarAudios($latitud, $longitud, $preferencia['preferencia']->getCategoria(), 
                    $preferencia['preferencia']->getIdiomaAudio(), $preferencia['preferencia']->getPuntuacionMinima());
            if ($audios['error'] == ERROR_NO_ERROR && isset($audios['audios'])) {
                $resultado['error']=ERROR_NO_ERROR;
                //Vamos a cargar los nombres de usuario de los dueños de los audios
                $BDPreparada=User::cargarPreparada();
                foreach ($audios['audios'] as $audio) {
                    $nombreUser=User::ejecutarPreparada($BDPreparada, $audio->getIdUser());
                    if($nombreUser) $nombreUser=$nombreUser->getUsuario();
                    //Para distribuir los audios en distintos servidores se pueden ir alternando aquí
                    $ruta='http://localhost/audioTwytter/servidor/sonido/'.rawurlencode($audio->getArchivo());
                    $resultado['audios'][] = array('la' => $audio->getLatitud(), 'lo' => $audio->getLongitud(),
                        'ruta' => $ruta, 'id' => $audio->getIdAudio(), 'puntos' => $audio->getPuntos(),
                        'categoria' => $audio->getCategoria()->getIdCategoria(), 'user' => $nombreUser);
                }
            }
            else $resultado['error']=$audios['error'];
        }
        else
            $resultado['error'] = ERROR_FALTA_DATO;

        return $resultado;
    }

    /**
     * Para recuperar la contraseña.
     * @param string $email El email del usuario.
     * @return array error=(ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO)
     */
    public function lostPass($email) {
        if ($email) {
            $return = User::passRecover($email);
            if ($return['error'] == ERROR_NO_ERROR) {
                $message = file_get_contents('templates/lostpass.php');
                $message = str_replace('{pass}', $return['pass'], $message);
                $messageText = file_get_contents('templates/lostpassText.php');
                $messageText = str_replace('{pass}', $return['pass'], $messageText);
                sendMail($email, FROM_NAME, FROM_EMAIL, 'Recuperar contraseña audio', $message, $messageText);

                unset($return['pass']);
            }
        }
        else
            $return['error'] = ERROR_FALTA_DATO;

        return $return;
    }

    

    

    /**
     * Sirve para valorar un audio.
     * @param int $idAudio El id del audio a valorar.
     * @param int $puntos Los puntos que se le otorgan.
     * @return array error (ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO)
     */
    public function valorar($idAudio, $puntos) {
        if($idAudio && $puntos !== FALSE) {
            $audio = Audio::cargar($idAudio);
            $user = unserialize($_SESSION['user']);

            if ($audio && $audio->getIdUser() != $user->getIdUser()) {
                $puntuacion = new Puntuacion($user->getIdUser(), $idAudio, $puntos);
                if($puntuacion->actualizar()) $resultado['error']=ERROR_NO_ERROR;
                else $resultado['error']=ERROR_GENERICO;
            }
            else $resultado['error']=ERROR_GENERICO;
        }
        else $resultado['error']=ERROR_FALTA_DATO;
        
        return $resultado;
    }

}

