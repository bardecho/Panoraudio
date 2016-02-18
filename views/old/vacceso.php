<h2 class="desplegable registro_titulo"><?php echo $GLOBALS['textos']['registro_titulo']; ?></h2>
<div id="registro" class="contenidoDesplegable">
    <div class="formCompatible">
        <form action="<?php echo BASE_URL; ?>index.php/acceso/registro" method="post" name="compatibleRegistro">
            <?php echo ponerClave(); ?>
            <label for="usuarioRegistro"><?php echo $GLOBALS['textos']['usuario']; ?></label> <input id="usuarioRegistro" style="color: #000000" type="text" name="usuario" class="input" value=""/>
            <label for="emailRegistro"><?php echo $GLOBALS['textos']['email']; ?></label> <input id="emailRegistro" style="color: #000000" type="text" name="email" class="input" value=""/>
            <label for="passRegistro"><?php echo $GLOBALS['textos']['pass']; ?></label> <input id="passRegistro" style="color: #000000" type="password" name="pass" class="input" value=""/>
            <label for="pass2Registro"><?php echo $GLOBALS['textos']['pass2']; ?></label> <input id="pass2Registro" style="color: #000000" type="password" name="pass2" class="input" value=""/>
            <input type="submit" name="registrarse" class="boton" value="<?php echo $GLOBALS['textos']['registrar']; ?>"/>
        </form>
    </div>
    
    <div class="formNormal">
        <form action="<?php echo BASE_URL; ?>index.php/acceso/registro" method="post" name="normalRegistro">
            <?php echo ponerClave(); ?>
            <input type="text" name="usuario" class="input usuario" value="<?php echo $GLOBALS['textos']['usuario']; ?>"/>
            <input type="text" name="email" class="input email" value="<?php echo $GLOBALS['textos']['email']; ?>"/>
            <input type="text" name="pass" class="input pass" value="<?php echo $GLOBALS['textos']['pass']; ?>"/>
            <input type="text" name="pass2" class="input pass2" value="<?php echo $GLOBALS['textos']['pass2']; ?>"/>
            <input type="submit" name="registrarse" class="boton" value="<?php echo $GLOBALS['textos']['registrar']; ?>"/>
        </form>
    </div>
</div>
<h2 class="desplegable login_titulo"><?php echo $GLOBALS['textos']['login_titulo']; ?></h2>
<div id="login" class="contenidoDesplegable">
    <div class="formCompatible">
        <form action="<?php echo BASE_URL; ?>index.php/acceso/login" method="post" name="compatibleLogin">
            <?php echo ponerClave(); ?>
            <label for="emailAcceso"><?php echo $GLOBALS['textos']['emailUsuario']; ?></label> <input id="emailAcceso" style="color: #000000" type="text" name="entrar_email" class="input emailUsuario" value=""/>
            <label for="passAcceso"><?php echo $GLOBALS['textos']['pass']; ?></label> <input id="passAcceso" style="color: #000000" type="password" name="entrar_pass" class="input pass" value=""/>
            <input type="submit" name="entrar" class="boton" value="<?php echo $GLOBALS['textos']['entrar']; ?>"/>
        </form>
    </div>
    
    <div class="formNormal">
        <form action="<?php echo BASE_URL; ?>index.php/acceso/login" method="post" name="normalLogin">
            <?php echo ponerClave(); ?>
            <input type="text" name="entrar_email" class="input emailUsuario" value="<?php echo $GLOBALS['textos']['emailUsuario']; ?>"/>
            <input type="text" name="entrar_pass" class="input pass" value="<?php echo $GLOBALS['textos']['pass']; ?>"/>
            <input type="submit" name="entrar" class="boton" value="<?php echo $GLOBALS['textos']['entrar']; ?>"/>
        </form>
    </div>
</div><br/>

<footer id="pie">
        <nav>
            <ul>
                <li><a target="_blank" href="https://www.facebook.com/pages/Panoraudiocom/457862144254026" id="facebook" title="Facebook"><img src="../img/facebook_r.png" alt=""/></a></li>
                <li><a target="_blank" href="https://twitter.com/panoraudio" id="twitter" title="Twitter"><img src="../img/twitter.png" alt=""/></a></li>
                <li><a target="_blank" href="http://www.youtube.com/watch?v=mF_L9cBL19c" id="youtube" title="YouTube"><img src="../img/youtube.png" alt=""/></a></li>
            </ul>
<div style="clear: both;"></div>
        </nav>
        
					<p><a href="mailto:contact@panoraudio.com">contact@panoraudio.com</a></p>
					<p>Copyright &copy; 2013 Panoraudio.com</p>

    </footer>
