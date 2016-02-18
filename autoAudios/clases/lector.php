<?php
/**
 * Convierte texto en audio.
 * @author bardecho
 */
interface Lector {
    /**
     * Convierte texto en audio.
     * @param string $texto El texto a leer.
     * @param string $idioma Las siglas del idioma.
     * @return string El contenido del archivo de audio.
     */
    public function leer($texto, $idioma);
}
