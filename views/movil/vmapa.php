<div id="cabecera">
    <div id="cabeceraSuperior">
        <img id="botonMenuLateral" src="<?php echo BASE_URL_IMG; ?>img/hamburger-icon.png" alt=""/>
        <input class="busqueda" type="text" name="textoBusqueda" value=""/>
        <img id="<?php echo ($datos['logueado'] ? 'botonPerfil' : 'botonPerfilDeslogueado'); ?>" src="<?php echo BASE_URL_IMG; ?>img/perfil-icon.png" alt=""/>
        <div style="clear: both;float:none"></div>
    </div>
    <div>
        <div id="botonVistaMapa" class="botonVista botonVistaSeleccionado"><?php echo $GLOBALS['textos']['vistaMapa']; ?></div>
        <div id="botonVistaLista" class="botonVista"><?php echo $GLOBALS['textos']['vistaLista']; ?></div>
        <div style="clear: both;float:none"></div>
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

    <!-- <div id="botonesIzq">
        <a href="http://web.panoraudio.com" target="_blank"><img src="<?php echo BASE_URL_IMG; ?>img/icono-IR-WEB-panoraudio-51.png" alt="Panoraudio Info"/></a>
    </div> -->

    <div id="botonesDer">
        <img id="botonIntercambiar" src="<?php echo BASE_URL_IMG; ?>img/icon-photo.png" alt="<?php echo $GLOBALS['textos']['intercambiar']; ?>" title="<?php echo $GLOBALS['textos']['intercambiar']; ?>"/>
        <img id="<?php echo ($datos['logueado'] ? 'botonMarca' : 'botonMarcaDeslogueado'); ?>" src="<?php echo BASE_URL_IMG; ?>img/marcar-icon-<?php echo ($datos['logueado'] ? 'activado' : 'desactivado'); ?>.png" alt="<?php echo $GLOBALS['textos']['marcar']; ?>"/>
        <img id="localizarse" src="<?php echo BASE_URL_IMG; ?>img/brujula-icon.png" alt="<?php echo $GLOBALS['textos']['localizarse']; ?>"/>
        <img id="tipoMapa" src="<?php echo BASE_URL_IMG; ?>img/tierra-icon.png" alt="<?php echo $GLOBALS['textos']['botonTipoMapa']; ?>"/>
    </div>
</div>