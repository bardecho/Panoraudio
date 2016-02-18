<h2><?php echo $GLOBALS['textos']['mensajeTitulo']; ?></h2>
<?php
if(isset($datos['textoMensaje']))
    foreach($datos['textoMensaje'] as $mensaje)
        echo "<p>$mensaje</p>";
?>
<br/>
<p><?php echo $GLOBALS['textos']['mensajeTexto']; ?></p>