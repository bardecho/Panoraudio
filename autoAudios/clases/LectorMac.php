<?php
/**
 * Convierte un texto en un archivo de audio usando un mac.
 *
 * @author bardecho
 */
class LectorMac implements Lector {
    public function leer($texto, $idioma) {
        $idioma = $this->correspondenciaIdioma($idioma);
        if($idioma) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://sayit.no-ip.org/say/index.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('voice' => $idioma, 'text' => $texto));
            $respuesta = curl_exec($ch);
            curl_close($ch);
        }
        else
            $respuesta = FALSE;

        return $respuesta;
    }
    
   /**
    * Recibe las siglas de idioma y las cambia por las especiales de esta clase.
    * @param string $idioma
    * @return string
    */
   private function correspondenciaIdioma($idioma) {
       $resultado=FALSE;

       switch($idioma) {
           case 'es':
               $resultado = 'esf';
               break;

           case 'en':
               $resultado = 'enf';
               break;

           case 'pt':
               $resultado = 'ptf';
               break;

           case 'fr':
               $resultado = 'frf';
               break;

           case 'de':
               $resultado = 'gef';
               break;
       }

       return $resultado;
   }
}

?>
