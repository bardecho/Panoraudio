<?php
/**
 * Description of TextoTurGalicia
 *
 * @author bardecho
 */
class TextoTurGalicia implements Texto {
    public function getTexto($url) {
        $resultado = '';
        $texto = $this->cogerWeb($url);
        //Accedemos como xml
        if($texto) {
            $tidy = new tidy;
            $tidy->parseString($texto, array(), 'UTF8');
            $tidy->cleanRepair();
            
            $body = $tidy->body();
            $this->buscaTituloRecursivo($body, 'Descripción', $resultado);
            if(!$resultado)
                $this->buscaTituloRecursivo($body, 'Características', $resultado);
        }

        return $resultado;
    }
    
    /**
     * Busca un h2 que contenga el texto de título.
     * @param TidyNode $node
     * @param string $titulo
     * @param string $resultado
     */
    private function buscaTituloRecursivo($node, $titulo, &$resultado) {
        foreach($node->child as $indice => $tag) {
            if($tag->hasChildren()) 
                $this->buscaTituloRecursivo($tag, $titulo, $resultado);
            
            if($resultado)
                break;
            
            $text=html_entity_decode(trim(strip_tags($tag->value)), ENT_COMPAT | ENT_HTML5, 'UTF-8');
            if($tag->name == 'h2' && $text == $titulo) {
                $resultado = strip_tags($node->child[$indice + 1]->child[0]->value);
                break;
            }
        }
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