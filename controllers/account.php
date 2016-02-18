<?php
/**
 * Account related actions.
 *
 * @author marcbardecho
 */
class Account {
    
    public function register($email, $pass, $usuario) {
        
    }

    /**
     * Da de baja a un usuario.
     * @return Array error(ERROR_NO_ERROR, ERROR_GENERICO)
     */
    public function unregister() {
        $user=unserialize($_SESSION['user']);
        $result=$user->remove();
        if($result['error'] == ERROR_NO_ERROR) {
            unset($_SESSION['user']);
            session_destroy();
        }

        return $result;
    }

    
    public function login($pass, $email) {
        
    }

    

    /**
     * Envía una solicitud por email para que alguien se una a la aplicación.
     * @param string $email El email del destinatario.
     * @param string $extraText Un mensaje para el destinatario.
     * @return Array error (ERROR_NO_ERROR, ERROR_FALTA_DATO) 
     */
    public function request($email, $extraText) {
        if($email) {
            $user=unserialize($_SESSION['user']);
            $message=file_get_contents('templates/request.php');
            $message=str_replace('{extraText}', $extraText, $message);
            $messageText=file_get_contents('templates/requestText.php');
            $messageText=str_replace('{extraText}', $extraText, $messageText);
            
            sendMail($email, '', $user->getEmail(), 'Audio invitación', $message, $messageText);

            $returnData['error']=ERROR_NO_ERROR;
        }
        else $returnData['error']=ERROR_FALTA_DATO;

        return $returnData;
    }
    
    /**
     * Verifica si se está logueado.
     * @param string $clave La clave única recibida desde el cliente.
     * @return boolean TRUE si está logueado y coincide la clave, FALSE en caso contrario.
     */
    public static function comprobarLogin($clave) {
        $resultado=FALSE;
        
        if(isset($_SESSION['user']) && is_a(unserialize($_SESSION['user']), 'User') && 
                isset($_SESSION['clave']) && $clave && $clave == $_SESSION['clave'])
            $resultado=TRUE;
        
        return $resultado;
    }
    
    /**
     * Cierra sesión.
     * @return int error -> ERROR_NO_ERROR
     */
    public function salir() {

    }
}
