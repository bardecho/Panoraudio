<!DOCTYPE html>
<html>
    <head>
        <title>Panoraudio - Sound Scapes of The World</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="application-name" content="Panoraudio"/>
        <meta name="author" content="Daniel Vázquez Cañedo y Fernando Javier Julián Freire"/>
        <meta name="description" content="Create and share sound scapes all over the world."/>
        <meta name="keywords" content="smart pictures,interactive map,mapa sonoro,sound,map,audio photo,travel,sound,fotografía,viajes" />
<?php 
        if(isset($_GET['id']) && is_file("img/fondos/{$_GET['id']}_mini.jpg")) {
            echo '<meta property="og:image" content="'.BASE_URL_IMG.'img/fondos/'.$_GET['id'].'_mini.jpg">';
        }
        else {
            echo '<meta property="og:image" content="'.BASE_URL_IMG.'img/LOGO-panoraudio-32-contraste.png">';
        }
        ?>
        <link rel="shortcut icon" href="<?php echo BASE_URL_IMG; ?>img/logoPanoraudio.ico" type="image/x-icon"/>
        <link rel="icon" href="<?php echo BASE_URL_IMG; ?>img/logoPanoraudioMini.png" sizes="32x32"/>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>estilo/estilo.css" type="text/css"/>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>estilo/least.min.css" type="text/css"/>
        <?php 
        if($_SESSION['dispositivoMovil'] == 2 || isset($_GET['movil'])) {
            echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />';
            echo '<script type="text/javascript">var movil = true;</script>';
            echo '<script type="text/javascript">var tablet = false;</script>';
        }
        else {
            echo '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'estilo/estiloEscritorio.css"/>';
            echo '<script type="text/javascript">var movil = false;</script>';
            if($_SESSION['dispositivoMovil'] == 1) {
                echo '<script type="text/javascript">var tablet = true;</script>';
            }
            else {
                echo '<script type="text/javascript">var tablet = false;</script>';
            }
        }
        ?>
        <script type="text/javascript">var logueado = <?php echo ($datos['logueado'] ? 'true' : 'false'); ?>;</script>
	<link href='http://fonts.googleapis.com/css?family=Lobster' rel='stylesheet' type='text/css'>
        <?php include_once("analyticstracking.php") ?>
        <script type="text/javascript" src="<?php echo BASE_URL; ?>js/jquery.js"></script>
        <script type="text/javascript" src="<?php echo BASE_URL; ?>js/blockui.js"></script>
        <script type="text/javascript" src="<?php echo BASE_URL; ?>js/reproductor.js"></script>
        <script type="text/javascript"><?php if (empty($datos['mapa'])) echo 'var desviacion=0;';else echo 'var desviacion=40, clave="' . $_SESSION['clave'] . '";'; ?></script>
        <script type="text/javascript">
            var categoriasPlataforma = {};
            <?php
            foreach($datos['categorias'] as $categoria) {
                echo "categoriasPlataforma[{$categoria->getIdCategoria()}] = '".traducirLista($categoria->getIdCategoria())."';";
            }
            ?>
        </script>
        <script type="text/javascript" src="<?php echo BASE_URL; ?>js/funciones.js"></script>
        <?php if (isset($datos['js'])) echo $datos['js']; ?>
        <?php if (isset($datos['mensaje'])) echo $datos['mensaje']; ?>
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-P6SKH8');</script>
		<meta name="google-site-verification" content="cYoWOIhXwSYAUn72RxboiNQLGORXQleDx-_BDMjScsw" />
    </head>

    <body>
        <!-- Google Tag Manager -->
        <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-P6SKH8"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager -->
        
        <div id="contenedor">
