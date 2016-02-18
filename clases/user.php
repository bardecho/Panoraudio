<?php
/**
 * Represents a User.
 *
 * @author marcbardecho
 */
class User {
    private $idUser, $email, $pass, $usuario, $idFacebook, $usoFacebook;

    public function __construct($idUser, $email, $pass, $usuario, $idFacebook, $usoFacebook = FALSE) {
        $this->setIdUser($idUser);
        $this->setEmail($email);
        $this->setPass($pass);
        $this->setUsuario($usuario);
        $this->setIdFacebook($idFacebook);
        $this->setUsoFacebook($usoFacebook);
    }

    /**
     * Register a new user.
     * @param String $email The user's unique email.
     * @param String $pass The user's pass.
     * @param String $usuario The user's unique name.
     * @return array error (ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_EMAIL_EXISTENTE, ERROR_USUARIO_EXISTENTE),
     * activationCode -> the code to activate the user.
     */
    public static function register($email, $pass, $usuario) {
        $result['error']=ERROR_GENERICO;
        $email=trim($email);
        $pass=trim($pass);
        $usuario=trim($usuario);

        if($email && $pass && $usuario) {
            try {
                $db=new DB();
                $data=$db->escapeData(array('email' => $email, 'usuario' => $usuario));
                $pass=reHash($pass);
                $activationCode=uniqid();

                $databaseResult=$db->alterData("insert into at_user (usuario, email, pass, activated, activationCode) ".
                        "values ('{$data['usuario']}', '{$data['email']}', '$pass', 0, '$activationCode')");
        
                if($databaseResult['rows'] > 0) {
                    $result['error']=ERROR_NO_ERROR;
                    $result['activationCode']=$activationCode;
                }
                else $result['error']=ERROR_GENERICO;
            }
            catch(Exception $e) {
                if($e->getCode() == '1062') {
                    $result['error']=0;
                    //Duplicated key
                    $info=$db->obtainData("select 1 from at_user where email = '{$data['email']}'");
                    if($info['rows'] > 0)
                        $result['error']+=ERROR_EMAIL_EXISTENTE;
                    $info=$db->obtainData("select 1 from at_user where usuario = '{$data['usuario']}'");
                    if($info['rows'] > 0)
                        $result['error']+=ERROR_USUARIO_EXISTENTE;
                    
                    if($result['error'] == 0) $result['error']=ERROR_GENERICO;
                }
                else $result['error']=ERROR_GENERICO;
            }
        }
        else $result['error']=ERROR_FALTA_DATO;

        return $result;
    }

    /**
     * Login action.
     * @param String $email The user's unique email or unique usuario.
     * @param String $pass The user's pass.
     * @return Array 'error' => (ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_COINCIDE). 'user' =>
     * a User or FALSE.
     */
    public static function login($email, $pass) {
        $result['error']=ERROR_GENERICO;
        $result['user']=FALSE;
        $email=trim($email);
        $pass=trim($pass);

        if($email && $pass) {
            try {
                $db=new DB();
                $data=$db->escapeData(array('email' => $email));
                $dbData=$db->obtainData("select idUser, pass, email, usuario, idFacebook from at_user where (email='{$data['email']}' or usuario='{$data['email']}') and activated=1");
                if($dbData['rows'] > 0) {
                    $passH=reHash($pass, substr($dbData['data'][0]['pass'], 0, 64));
                    if($passH == $dbData['data'][0]['pass']) {
                        $result['error']=ERROR_NO_ERROR;
                        $result['user']=new User($dbData['data'][0]['idUser'], $dbData['data'][0]['email'], $pass, $dbData['data'][0]['usuario'], $dbData['data'][0]['idFacebook'], FALSE);
                    }
                    else $result['error']=ERROR_NO_COINCIDE;
                }
                else $result['error']=ERROR_NO_COINCIDE;
            }
            catch(Exception $e) {
                $result['error']=ERROR_GENERICO;
            }
        }
        else $result['error']=ERROR_FALTA_DATO;

        return $result;
    }
    
    /**
     * Login action with Facebook.
     * @param int $idFacebook El id del usuario en facebook.
     * @return Array 'error' => (ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_NO_COINCIDE). 'user' =>
     * a User or FALSE.
     */
    public static function loginFacebook($idFacebook) {
        $result['error']=ERROR_GENERICO;
        $result['user']=FALSE;
        $idFacebook=intval($idFacebook);

        if($idFacebook) {
            try {
                $db=new DB();
                $dbData=$db->obtainData("select idUser, pass, email, usuario, idFacebook from at_user where idFacebook = $idFacebook and activated=1");
                if($dbData['rows'] > 0) {
                    $result['error']=ERROR_NO_ERROR;
                    $result['user']=new User($dbData['data'][0]['idUser'], $dbData['data'][0]['email'], $dbData['data'][0]['pass'], $dbData['data'][0]['usuario'], $dbData['data'][0]['idFacebook'], TRUE);
                }
                else $result['error']=ERROR_NO_COINCIDE;
            }
            catch(Exception $e) {
                $result['error']=ERROR_GENERICO;
            }
        }
        else $result['error']=ERROR_FALTA_DATO;

        return $result;
    }
    
    /**
     * Registra un usuario con un id de Facebook.
     * @param int $idFacebook El id del usuario en facebook.
     * @return Array 'error' => (ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO).
     */
    public static function registroFacebook($idFacebook) {
        $result['error']=ERROR_GENERICO;
        $result['user']=FALSE;
        $idFacebook=intval($idFacebook);

        if($idFacebook) {
            try {
                $db=new DB();
                $dbData=$db->alterData("insert into at_user (idFacebook, activated) values ($idFacebook, 1)");
                if($dbData['rows'] > 0) {
                    $result['error']=ERROR_NO_ERROR;
                }
            }
            catch(Exception $e) {
                $result['error']=ERROR_GENERICO;
            }
        }
        else $result['error']=ERROR_FALTA_DATO;

        return $result;
    }

    /**
     * Activates a user.
     * @param String $activationCode 13 caracters string.
     * @return boolean TRUE on success, FALSE in error.
     */
    public static function activate($activationCode) {
        $activationCode=trim($activationCode);
        $result=FALSE;

        if($activationCode) {
            try {
                $db=new DB();
                $data=$db->escapeData(array('activationCode' => $activationCode));
                $result=$db->alterData("update at_user set activated=1, activationCode=NULL where activationCode='{$data['activationCode']}'");
                if($result['rows'] > 0) $result=TRUE;
                else $result=FALSE;
            }
            catch(Exception $e) {
                $result=FALSE;
            }
        }

        return $result;
    }

    /**
     * Deactivated an active user.
     * @param String $email User's unique email.
     * @return String New activation code on success, FALSE in error.
     */
    public static function deactivate($email) {
        $email=trim($email);
        $result=FALSE;

        if($email) {
            try {
                $db=new DB();
                $data=$db->escapeData(array('email' => $email));
                $activationCode=uniqid();
                $result=$db->alterData("update at_user set activated=0, activationCode='$activationCode' where email='{$data['email']}'");
                if($result['rows'] > 0) $result=$activationCode;
                else $result=FALSE;
            }
            catch(Exception $e) {
                $result=FALSE;
            }
        }

        return $result;
    }

    /**
     * Update some user's data.
     * @param String $newEmail The user's unique new email.
     * @param String $newPass The user's new pass.
     * @param String $newUsuario The user's new unique alias.
     * @return array error (ERROR_NO_ERROR, ERROR_GENERICO, ERROR_FALTA_DATO, ERROR_EMAIL_EXISTENTE, ERROR_USUARIO_EXISTENTE),
     * activationCode if needed.
     */
    public function update($newEmail=FALSE, $newPass=FALSE, $newUsuario=FALSE) {
        $result['error']=ERROR_GENERICO;
        $newPass=trim($newPass);
        $newEmail=trim($newEmail);
        $newUsuario=trim($newUsuario);

        if($newPass || $newEmail || $newUsuario) {
            try {
                $db=new DB();
                $data=$db->escapeData(array('email' => $newEmail, 'usuario' => $newUsuario));
                $sql = 'update at_user set ';
                
                if($newEmail) 
                    $sql .= "email = '{$data['email']}',";
                if($newPass) {
                    $newPass=reHash($newPass);
                    $sql .= "pass = '$newPass',";
                }
                if($newUsuario) 
                    $sql .= "usuario = '{$data['usuario']}',";
                
                $resultdb=$db->alterData(substr($sql, 0, -1)." where idUser = $this->idUser");
                if($resultdb['rows'] > 0) {
                    $result['error']=ERROR_NO_ERROR;
                    //Refreshing data
                    if($this->email != $newEmail) {
                        $this->email=$newEmail;
                        $result['activationCode']=User::deactivate($this->email);
                    }
                    $this->pass=$newPass;
                    $this->usuario=$newUsuario;
                }
                else $result['error']=ERROR_GENERICO;
            }
            catch(Exception $e) {
                if($e->getCode() == '1062') {
                    $result['error']=0;
                    //Duplicated key
                    $info=$db->obtainData("select 1 from at_user where email = '{$data['email']}'");
                    if($info['rows'] > 0)
                        $result['error']+=ERROR_EMAIL_EXISTENTE;
                    $info=$db->obtainData("select 1 from at_user where usuario = '{$data['usuario']}'");
                    if($info['rows'] > 0)
                        $result['error']+=ERROR_USUARIO_EXISTENTE;
                    
                    if($result['error'] == 0) $result['error']=ERROR_GENERICO;
                }
                else $result['error']=ERROR_GENERICO;
            }
        }
        else $result['error']=ERROR_FALTA_DATO;

        return $result;
    }

    /**
     * Crea una nueva contraseña para un usuario.
     * @param string $email El email del usuario que ha perdido la contraseña.
     * @return Array error(ERROR_NO_ERROR, ERROR_GENERICO), pass -> la contraseña nueva.
     */
    public static function passRecover($email) {
        $return['error']=ERROR_NO_ERROR;

        $pass=passGenerator();

        //Modifying database pass
        try {
            $db=new DB();
            $escapedData=$db->escapeData(array('email' => $email, 'pass' => $pass));
            $pass=reHash($escapedData['pass']);
            $query="update at_user set pass='$pass' where email='{$escapedData['email']}'";
            $db->alterData($query);
            $return['pass']=$escapedData['pass'];        
        }
        catch(Exception $e) {
            $return['error']=ERROR_GENERICO;
        }

        return $return;
    }

    /**
     * Deletes the user from database.
     * @return Array error(ERROR_NO_ERROR, ERROR_GENERICO)
     */
    public function remove() {
        $result['error']=ERROR_GENERICO;

        try {
            $db=new DB();
            $data=$db->escapeData(array('email' => $this->email));
            $resultDB=$db->alterData("delete from at_user where email='{$data['email']}'");
            if($resultDB['rows'] > 0) $result['error']=ERROR_NO_ERROR;
            else $result['error']=ERROR_GENERICO;
        }
        catch(Exception $e) {
            $result['error']=ERROR_GENERICO;
        }

        return $result;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $result=FALSE;

        if(trim($email)) {
            $this->email=trim($email);
            $result=TRUE;
        }

        return $result;
    }

    public function getPass() {
        return $this->pass;
    }

    public function setPass($pass) {
        $result=FALSE;

        if(trim($pass)) {
            $this->pass=trim($pass);
            $result=TRUE;
        }

        return $result;
    }

    public function getIdUser() {
        return $this->idUser;
    }

    private function setIdUser($idUser) {
        $this->idUser=intval($idUser);

        return TRUE;
    }
    
    public function getUsuario() {
        return $this->usuario;
    }
    
    public function setUsuario($usuario) {
        $result=FALSE;

        if(trim($usuario)) {
            $this->usuario=trim($usuario);
            $result=TRUE;
        }

        return $result;
    }
    
    public function getIdFacebook() {
        return $this->idFacebook;
    }

    public function setIdFacebook($idFacebook) {
        $this->idFacebook = intval($idFacebook);
        
        return TRUE;
    }
    
    public function getUsoFacebook() {
        return $this->usoFacebook;
    }
    
    public function setUsoFacebook($usoFacebook) {
        $this->usoFacebook = $usoFacebook ? TRUE : FALSE;
    }

    /**
     * Crea una consulta preparada para cargar un usuario.
     * @return BDPreparada La consulta para cargar un usuario.
     */
    public static function cargarPreparada() {
        try {
            $resultado=new BDPreparada('select * from at_user where idUser = ?');
            $resultado->meterParametros(array('idUser' => BDPreparada::INTEGER));
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
    
    /**
     * Ejecuta la consulta preparada con cargarPreparada().
     * @param BDPreparada $BDPreparada La consulta que devuelve cargarPreparada().
     * @param int $idUser El id del usuario que se quiere cargar.
     * @return User El usuario solicitado.
     */
    public static function ejecutarPreparada($BDPreparada, $idUser) {
        $resultado=FALSE;
        
        try {
            $BDPreparada->rellenarParametros(array('idUser' => $idUser));
            $datos=$BDPreparada->obtenerDatos();
            if($datos['filas'] > 0) 
                $resultado=new User($datos['datos'][0]['idUser'], $datos['datos'][0]['email'], $datos['datos'][0]['pass'], $datos['datos'][0]['usuario'], $datos['datos'][0]['idFacebook']);
        }
        catch(Exception $ex) {
            $resultado=FALSE;
        }
        
        return $resultado;
    }
}
