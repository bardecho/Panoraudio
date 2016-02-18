<?php
include_once 'clases/ispeech.php';
/**
 * Description of LectorI
 *
 * @author bardecho
 */
class LectorI implements Lector {
    public function leer($texto, $idioma) {
        $idioma = $this->correspondenciaIdioma($idioma);
        
        $SpeechSynthesizer = new SpeechSynthesizer();
        $SpeechSynthesizer->setParameter('server', 'http://api.ispeech.org/api/rest');
        $SpeechSynthesizer->setParameter('apikey', '59e482ac28dd52db23a22aff4ac1d31e');
        $SpeechSynthesizer->setParameter('text', $texto);
        $SpeechSynthesizer->setParameter('format', 'mp3');
        $SpeechSynthesizer->setParameter('voice', $idioma);
        $SpeechSynthesizer->setParameter('output', 'rest');
        $respuesta = $SpeechSynthesizer->makeRequest();

        if (is_array($respuesta)) //error occurred 
            echo '<pre>'.htmlentities(print_r($respuesta, true), null, 'UTF-8').'</pre>';

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
               $resultado = 'eurspanishfemale';
               break;

           case 'en':
               $resultado = 'ukenglishfemale';
               break;

           case 'pt':
               $resultado = 'eurportuguesefemale';
               break;

           case 'fr':
               $resultado = 'eurfrenchfemale';
               break;

           case 'de':
               $resultado = 'eurdutchfemale';
               break;
       }

       return $resultado;
   }
}
