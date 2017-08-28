<?php
/**
 * Clase para extraer texto de la Wikipedia.
 *
 * @author bardecho
 */
class TextoWiki implements Texto {
    public function getTexto($url) {
        $texto = $this->cogerWeb($url);
        //Recortamos el resumen
        $ini = strpos($texto, '<p>');
        $recorte = substr($texto, $ini, strpos($texto, '<div id="toc"') - $ini);
        //Quitamos todas las etiquetas
        $recorte = strip_tags($recorte);
        //Nos cargamos los corchetes con cosas
        $recorte = preg_replace('|\\[.*?\\]|is', '', $recorte);
        //Traducir entidades html
        $recorte = html_entity_decode($recorte, ENT_COMPAT | ENT_HTML5, 'UTF-8');
        
        return $recorte;
    }
    
    /**
     * Captura el código html de una web.
     * @param string $url
     * @return string
     */
    private function cogerWeb($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $contenido = curl_exec($ch);
        curl_close($ch);

        return $contenido;
    }
}

?>
