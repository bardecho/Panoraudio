<?php
/**
 * Captura texto de una página.
 * @author bardecho
 */
interface Texto {
    /**
     * Coge el texto útil de una determinada url.
     * @param string $url La dirección de la que coger el texto.
     * @return string El texto extraído.
     */
    public function getTexto($url);
}

?>
