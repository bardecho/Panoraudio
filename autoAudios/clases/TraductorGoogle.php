<?php
/**
 * Traduce texto usando google.
 *
 * @author bardecho
 */
class TraductorGoogle implements Traductor {
    public function traducirTexto($texto, $idioma) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/language/translate/v2');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('q' => $texto, 'userIp' => '31.170.165.220',
            'key' => 'AIzaSyBSCkQCqcDm7sdIOAcXwDIBJ1-Ws8SiYoY', 'source' => 'es', 'target' => $idioma));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
        $contenido = json_decode(curl_exec($ch), TRUE);
        if(!isset($contenido['error']))
            $contenido = $contenido['data']['translations'][0]['translatedText'];
        else
            $contenido = FALSE;
        curl_close($ch);
    
        return $contenido;
    }
}
