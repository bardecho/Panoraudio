<form action="<?php echo BASE_URL; ?>index.php/config/modificarConfiguracion" method="post">
    <?php echo ponerClave(); ?>
    <h2 class="desplegable"><?php echo $GLOBALS['textos']['idiomaAplicacion']; ?></h2>
    <div id="idiomaAplicacion" class="contenidoDesplegable">
        <select name="idioma" class="select">
            <option value="es" <?php if($_SESSION['idioma'] == 'es') echo 'selected="selected"'; ?>>Castellano</option>
            <option value="ca" <?php if($_SESSION['idioma'] == 'ca') echo 'selected="selected"'; ?>>Català</option>
            <option value="de" <?php if($_SESSION['idioma'] == 'de') echo 'selected="selected"'; ?>>Deutsch</option>
            <option value="en" <?php if($_SESSION['idioma'] == 'en') echo 'selected="selected"'; ?>>English</option>
			<option value="it" <?php if($_SESSION['idioma'] == 'it') echo 'selected="selected"'; ?>>Italiano</option>
            <option value="eu" <?php if($_SESSION['idioma'] == 'eu') echo 'selected="selected"'; ?>>Euskara</option>
            <option value="fr" <?php if($_SESSION['idioma'] == 'fr') echo 'selected="selected"'; ?>>Français</option>
            <option value="gl" <?php if($_SESSION['idioma'] == 'gl') echo 'selected="selected"'; ?>>Galego</option>
            <option value="pt" <?php if($_SESSION['idioma'] == 'pt') echo 'selected="selected"'; ?>>Português</option>
        </select>
    </div>

    <h2 class="desplegable"><?php echo $GLOBALS['textos']['idiomaAudios']; ?></h2>
    <div id="idiomasAudio" class="contenidoDesplegable">
        <?php
        if(is_array($datos['idiomasAudio'])) {
            foreach($datos['idiomasAudio'] as $idiomaAudio) {
                $seleccionado='';
                if($datos['configuracion'] && $datos['configuracion']->getIdiomaAudio())
                    foreach($datos['configuracion']->getIdiomaAudio() as $confIdiomaAudio)
                        if($confIdiomaAudio->getIdIdiomaAudio() == $idiomaAudio->getIdIdiomaAudio()) {
                            $seleccionado='checked="checked"';
                            break;
                        }

                if($idiomaAudio->getIdIdiomaAudio() == 0) $nombreIdioma=traducirLista(0);
                else $nombreIdioma=$idiomaAudio->getIdioma();
                
                echo '<label class="etiqueta"><input type="checkbox" name="idiomasAudio[]" value="' . $idiomaAudio->getIdIdiomaAudio() . '" '.$seleccionado.'/> ' . $nombreIdioma . '</label>';
            }
        }
        ?>
    </div>

    <h2 class="desplegable"><?php echo $GLOBALS['textos']['arrastrar']; ?></h2>
    <div id="categorias" class="contenidoDesplegable">
        <?php
        if(is_array($datos['categorias'])) {
            foreach($datos['categorias'] as $categoria) {
                $seleccionado='';
                if($datos['configuracion'] && $datos['configuracion']->getCategoria())
                    foreach($datos['configuracion']->getCategoria() as $confCategoria)
                        if($confCategoria->getIdCategoria() == $categoria->getIdCategoria()) {
                            $seleccionado='checked="checked"';
                            break;
                        }

                echo '<label class="etiqueta"><input type="checkbox" name="categorias[]" value="' . $categoria->getIdCategoria() . '" '.$seleccionado.'/> ' . traducirLista($categoria->getIdCategoria()) . '</label>';
            }
        }
        ?>
    </div>
    <div style="clear: both"></div>

    <div id="botones">
        <input type="submit" name="aceptar" class="boton" value="<?php echo $GLOBALS['textos']['aceptar']; ?>"/> <input type="button" name="cancelar" class="boton" value="<?php echo $GLOBALS['textos']['cancelar']; ?>"/>
    </div>
</form>
