<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>Convertir texto en audio</title>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/funciones.js"></script>
    </head>
    <body>
        <h1>Convertir texto en audio</h1>
        <div style="float: left;border: 1px solid black;padding:10px">
            <h2>De uno en uno</h2>
            <p>URL: <input type="text" name="url" value=""/></p>
            <p>NOMBRE: <input type="text" name="nombre" value=""/></p>
            <p><input type="button" name="enviar" value="Enviar"/> <input type="button" name="limpiar" value="Limpiar"/></p>
        </div>
        <div style="float: left;margin: 0 0 10px 10px;border: 1px solid black;padding:10px">
            <h2>Muchos a la vez</h2>
            <p>URLs: <textarea name="urls" cols="80" rows="10"></textarea></p>
            <p>Nº inicial archivo: <input type="text" name="numero" value=""/></p>
            <p><input type="button" name="enviarVarios" value="Enviar varios"/> <input type="button" name="limpiar" value="Limpiar"/></p>
        </div>
        <div style="clear: both">
            <hr/>
            <p>Obteniendo texto <img src="" id="obteniendo" width="15"/></p>
            <hr/>
            <div id="idiomas"></div>
            <hr/>
            <div id="procesadas"></div>
        </div>
    </body>
</html>
