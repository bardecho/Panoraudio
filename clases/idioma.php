<?php
/**
 * Controla el idioma de la aplicación.
 *
 * @author bardecho
 */
class Idioma {
    private $idiomasExistentes;
    const IDIOMA_PREDETERMINADO='es';
    
    public function __construct() {
        //Cargamos los idioma existentes
        $d = dir('texto');
        while (false !== ($entry = $d->read())) {
            if($entry != '.' && $entry != '..') 
                $this->idiomasExistentes[]=$entry;
        }
        $d->close();
    }
    
    public function idiomaActual() {
        if(isset($_SESSION['idioma']) && in_array($_SESSION['idioma'], $this->idiomasExistentes))
            $idioma=$_SESSION['idioma'];
        else
            $idioma=FALSE;
        
        return $idioma;
    }
    
    public function establecerIdioma($idioma=FALSE, $inmediato=FALSE) {
        //Si es false lo establece automáticamente
        if($idioma === FALSE) {
            //¿Está en la sesión?
            if(isset($_SESSION['idioma']) && $_SESSION['idioma']) $idioma=$_SESSION['idioma'];
            else {
                //¿Está en la cookie?
                if(isset($_COOKIE['idioma']) && $_COOKIE['idioma']) {
                    $idioma=$_COOKIE['idioma'];
                }
                else {
                    //Detectamos el idioma
                    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $_SERVER['HTTP_ACCEPT_LANGUAGE'])
                        $idioma=strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
                }
            }

            //Nos aseguramos de que existe
            if(!in_array($idioma, $this->idiomasExistentes)) $idioma=self::IDIOMA_PREDETERMINADO;
        }
        else {
            //Nos aseguramos de que existe
            if(!in_array($idioma, $this->idiomasExistentes))
                $idioma=self::IDIOMA_PREDETERMINADO;
        }

        $_SESSION['idioma']=$idioma;
        if($inmediato)
            setcookie('idioma', $idioma, time()+60*60*24*30, '/', DOMINIO_COOKIE, FALSE, TRUE); //FALTA SSL
        else
            grabarGalletitaRetardada(array('idioma', $idioma, time()+60*60*24*30, '/', DOMINIO_COOKIE, FALSE, TRUE)); //FALTA SSL
    }
}