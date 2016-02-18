<?php
//Desactivamos si no está logueado
if(comprobarLogin()) {
    $idCategorias = 'botonCategorias';
    $idMarca = 'botonMarca';
    $idRutas = 'botonRutas';
}
else {
    $idCategorias = 'botonCategorias';//'catUnLog';
    $idMarca = 'botonMarcaDesactivado';
    $idRutas = 'botonRutasDesactivado';
}
?>

</div>
<div id="menuLateral">
    <img id="botonBuscar" class='botonCuadrado' src='<?php echo BASE_URL_IMG; ?>img/lupa.png' alt='<?php echo $GLOBALS['textos']['buscar']; ?>' title='<?php echo $GLOBALS['textos']['buscar']; ?>'/>
    <img id="<?php echo $idCategorias; ?>" class='botonCuadrado' src='<?php echo BASE_URL_IMG; ?>img/tellme.png' alt='<?php echo $GLOBALS['textos']['prefCat']; ?>' title='<?php echo $GLOBALS['textos']['prefCat']; ?>'/>
    <img id="<?php echo $idRutas; ?>" class='botonCuadrado' src='<?php echo BASE_URL_IMG; ?>img/rutas.png' alt='<?php echo $GLOBALS['textos']['rutas']; ?>' title='<?php echo $GLOBALS['textos']['rutas']; ?>'/>
    <img id="<?php echo $idMarca; ?>" class='botonCuadrado' src='<?php echo BASE_URL_IMG; ?>img/punterorojocuadrado.png' alt='<?php echo $GLOBALS['textos']['marcar']; ?>' title='<?php echo $GLOBALS['textos']['marcar']; ?>'/>
</div>