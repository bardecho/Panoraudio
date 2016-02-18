<div id="cabecera">
    <img id="botonIdioma" src="<?php echo BASE_URL_IMG; ?>img/banderas/<?php echo $_SESSION['idioma']; ?>.png" alt="<?php echo $_SESSION['idioma']; ?>" title="<?php echo $_SESSION['idioma']; ?>"/>
    <a href="http://panoraudio.net" target="_blank"><img src="<?php echo BASE_URL_IMG; ?>img/LOGO-panoraudio-32-contraste.png" alt="Panoraudio"/></a>
    <input class="busqueda" type="text" name="textoBusqueda" value=""/>
    <?php
    if(comprobarLogin()) {
        echo "<img id='botonPerfil' src='".BASE_URL_IMG."img/perfil-icon.png' alt=''/>";
    }
    else {
        echo "<span id='botonRegistroVentana' class='boton'>{$GLOBALS['textos']['registrar']}</span>";
        echo "<span id='botonAcceso'>{$GLOBALS['textos']['acceder']}</span>";
    }
    ?>
    <a href="https://twitter.com/panoraudio" target="_blank"><img src="<?php echo BASE_URL_IMG; ?>img/twitter-icon.png" alt="Twitter"/></a>
    <a href="https://www.facebook.com/pages/Panoraudiocom/457862144254026" target="_blank"><img src="<?php echo BASE_URL_IMG; ?>img/facebook-icon.png" alt="Facebook"/></a>
    <div id="contacto">
        <a href="mailto:contact@panoraudio.com">
            <img src="<?php echo BASE_URL_IMG; ?>img/mail-icon-hover-53.png" alt="<?php echo $GLOBALS['textos']['contacta']; ?>"/>
            <p><?php echo $GLOBALS['textos']['contacta']; ?></p>
        </a>
    </div>
</div>

<div id="contenedorMapa">
    <div id="mapa"></div>

    <div class="menuTira">
        <ul id="categorias">
            <?php
            $baseUrlImg = BASE_URL_IMG;
            foreach($datos['categorias'] as $categoria) {
                echo "
                    <li>
                        <img id='{$categoria->getIdCategoria()}_categoria' src='{$baseUrlImg}img/categorias/{$categoria->getIdCategoria()}.png' alt='".traducirLista($categoria->getIdCategoria())."'/>
                        <p>".traducirLista($categoria->getIdCategoria())."</p>
                    </li>";
            }
            
            //Las rutas
            echo "
                <li>
                    <img id='ruta_categoria' src='{$baseUrlImg}img/categorias/ruta.png' alt='{$GLOBALS['textos']['rutas']}'/>
                    <p>{$GLOBALS['textos']['rutas']}</p>
                </li>";
            ?>
        </ul>
    </div>

    <div id="botonesDer">
        <img id="botonIntercambiar" src="<?php echo BASE_URL_IMG; ?>img/icon-photo.png" alt="<?php echo $GLOBALS['textos']['intercambiar']; ?>" title="<?php echo $GLOBALS['textos']['intercambiar']; ?>"/>
        <img id="<?php echo ($datos['logueado'] ? 'botonMarca' : 'botonMarcaDeslogueado'); ?>" src="<?php echo BASE_URL_IMG; ?>img/marcar-icon-<?php echo ($datos['logueado'] ? 'activado' : 'desactivado'); ?>.png" alt="<?php echo $GLOBALS['textos']['marcar']; ?>" title="<?php echo $GLOBALS['textos']['marcar']; ?>"/>
        <img id="localizarse" src="<?php echo BASE_URL_IMG; ?>img/brujula-icon.png" alt="<?php echo $GLOBALS['textos']['localizarse']; ?>" title="<?php echo $GLOBALS['textos']['localizarse']; ?>"/>
        <img id="tipoMapa" src="<?php echo BASE_URL_IMG; ?>img/tierra-icon.png" alt="<?php echo $GLOBALS['textos']['botonTipoMapa']; ?>" title="<?php echo $GLOBALS['textos']['botonTipoMapa']; ?>"/>
    </div>
</div>