<?php
/**
 * Traduce texto.
 * @author bardecho
 */
interface Traductor {
    /**
     * Recibe un texto y lo traduce.
     * @param string $texto El texto a traducir.
     * @param string $idioma Las siglas del idioma objetivo.
     * @return string El texto traducido.
     */
    public function traducirTexto($texto, $idioma);
}
