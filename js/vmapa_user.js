//Constantes
var tiempoSolicitud = 60000, zoomLocalizado = 14, zoomNoLocalizado = 2, alcanceGrupos = 18;
//Globales
var tellmee_Servidor = new Tellmee_Servidor(cadenaConexion, tiempoSolicitud, almacenarAudios), tellmee_Google,
        nombreUsuario, idLocalizado, direccion, listaReproduccion = new ColaReproduccion(), reproductor, rutaInterfaz,
        marcadores, aleatorio = false, vistaLista = null, fotoPuntos = true;

function enviar() {
    $('form[name="formu"]').submit();
}

function puntuar(id, puntos) {
    tellmee_Servidor.puntuar(id, puntos, function (data) {
        if (data.ok) {
            //Actualizamos la puntuación
            var marcador = tellmee_Google.capas['marcadores'].obtenerMarcador(id);
            marcador.puntosPositivos = data.puntosPositivos;
            marcador.puntosNegativos = data.puntosNegativos;

            $('#puntuarPositivo').next().html(marcador.puntosPositivos);
            $('#puntuarNegativo').next().html(marcador.puntosNegativos);
        }
    });
}

function contarDescarga(id) {
    tellmee_Servidor.contarDescarga(id);
}

function borrarElemento(id) {
    if (confirm(textos['confirmaBorra'])) {
        tellmee_Servidor.borrarElemento(id, function (data) {
            if (data.ok) {
                tellmee_Servidor.solicitarAudios();
                tellmee_Google.globo.cerrarGlobo();
                $('#infoAudio').remove();

                alert(textos['marcaBorrada']);
            }
            else
                alert(textos['marcaNoBorrada']);
        });
    }
}

function marcarInapropiado(id, tipoDenuncia) {
    if (confirm(textos['marcarInapropiado'])) {
        tellmee_Servidor.marcarInapropiado(id, tipoDenuncia, function (data) {
            if (data.ok) {
                alert(textos['marcadoInapropiado']);
            }
            else
                alert(textos['noMarcadoInapropiado']);
        });
    }
}

function compartirFacebook(id) {
    //http://www.facebook.com/sharer.php?u=http://www.example.com/&t=Texto
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + base_url + 'index.php/mapa?id=' + id,
            'facebook-share-dialog', 'width=626,height=436');
}

function compartirTwitter(id) {
    //http://twitter.com/share?url=http://www.example.com/&text=Texto
    window.open('http://twitter.com/share?url=' + base_url + 'index.php/mapa?id=' + id,
            'twitter-share-dialog', 'width=626,height=436');
}

function clickImagen(fondoGrande) {
    var ancho = $(document).width() - $(document).width() * 4 / 100 - 6,
            alto = $(document).height() - $(document).height() * 4 / 100 - 6;

    $.blockUI({
        message: '<img onload="reposicionarImagenGrande()" style="display: block;max-width: ' + ancho + 'px;max-height: ' + alto + 'px" src="' + fondoGrande + '.jpg" alt=""/>',
        css: {
            width: 'auto',
            top: '2%',
            left: '2%',
            cursor: 'default',
            borderRadius: '0'
        },
        blockMsgClass: 'bloqueImagen'
    });
    //Evento para cerrar el menú
    $('.blockOverlay').click(function () {
        $.unblockUI();
    });
}

function reposicionarImagenGrande() {
    var izquierda = ($(document).width() - $('.bloqueImagen img').width()) / 2,
            arriba = ($(document).height() - $('.bloqueImagen img').height()) / 2;
    $('.bloqueImagen').css('top', arriba + 'px');
    $('.bloqueImagen').css('left', izquierda + 'px');
}

function crearInfoAudio(esto) {
    superposicionNegra(true, cerrarInfoAudio);
    var ventana = $('<div class="padreVentanaMarca"></div>');
    var ventanaInterna = $(
            '<div class="ventanaMarca">' +
            '<p id="nombreUsuarioMarca">' + textos['subidoPor'] + ' ' + esto.usuario + '</p>' +
            '<div id="imagen"><img height="185" src="" alt=""/></div>' +
            '<div id="desplazamiento">' +
            '<div id="lineaTiempo"></div>' +
            '<p id="reloj">00:00</p>' +
            '<div style="float:none;clear:both"></div>' +
            '</div>' +
            '<div><strong>' + textos['descripcion'] + ':</strong> ' + decodeURI(esto.descripcion) + '</div>' +
            '<ul>' +
            '<li>' +
            '<span id="play"></span>' +
            '</li>' +
            (nombreUsuario != undefined ?
                    '<li>' +
                    '<img id="puntuarPositivo" src="' + base_url_img + 'img/bien-icon.png" alt="' + textos['gusta'] + '" title="' + textos['gusta'] + '""/>' +
                    '<p>' + esto.puntosPositivos + '</p>' +
                    '</li>' +
                    '<li>' +
                    '<img id="puntuarNegativo" src="' + base_url_img + 'img/mal-icon.png" alt="' + textos['noGusta'] + '" title="' + textos['noGusta'] + '"/>' +
                    '<p>' + esto.puntosNegativos + '</p>' +
                    '</li>' : '') +
            '<li>' +
            '<img id="compartirTwitter" src="' + base_url_img + 'img/twitter-icon.png" alt="Twitter"/>' +
            '</li>' +
            '<li>' +
            '<img id="compartirFacebook" src="' + base_url_img + 'img/facebook-icon.png" alt="Facebook"/>' +
            '</li>' +
            (movil || tablet ? 
                    '<li>' +
                    '<a href="whatsapp://send?text=' + base_url + '?id=' + esto.id + '" data-action="share/whatsapp/share"><img id="compartirWhatsapp" src="' + base_url_img + 'img/whatsapp.png" alt="WhatsApp"/></a>' +
                    '</li>' : '') + 
            
            (nombreUsuario != undefined ?
                    '<li>' +
                    '<img id="marcarInapropiado" src="' + base_url_img + 'img/denuncia-icon.png" alt="' + textos['botonInapropiado'] + '" title="' + textos['botonInapropiado'] + '"/>' +
                    '<p>' + textos['botonInapropiado'] + '</p>' +
                    '</li>' : '') +
            '</ul>' +
            '<div style="float: none;clear: both"></div>' +
            '<img src="' + base_url_img + 'img/cerrar.png" alt="' + textos['cerrar'] + '" class="cerrar"/>' +
            (nombreUsuario != undefined && esto.usuario == nombreUsuario ?
                '<img src="' + base_url_img + 'img/borrar.png" onclick="borrarElemento(' + esto.id + ')" alt="' + textos['botonBorrar'] + '" class="borrar"/>' : '') +
            '</div>' +
            '<img src="' + base_url_img + 'img/flechaInferior.png" alt="" class="flechaInferior"/>' +
            '<img src="' + base_url_img + 'img/categorias/mapa/' + esto.categoria + '.png" alt="' + categoriasPlataforma[esto.categoria] + '" title="' + categoriasPlataforma[esto.categoria] + '" class="categoriaActual"/>');
    centrarVentana(ventanaInterna, ventana);
    ventana.append(ventanaInterna);
    $('#contenedorMapa').append(ventana);

    //Ahora le añadimos la imagen de fondo y el meta para el whatsapp
    if (esto.fondo) {
        $('#imagen img').attr('src', esto.fondo);
        $('#imagen img').css('cursor', 'pointer');

        $('#imagen img').click(function () {
            var vistaImagen = new VistaImagen(esto, tellmee_Servidor, nombreUsuario);
            vistaImagen.dibujar();
        });
    }
    else {
        obtenerImagenStreet(250, 185, esto.position.lat(), esto.position.lng(), function (ruta) {
            $('#imagen img').attr('src', ruta);
        });
    }

    //Evento de cierre
    $('.padreVentanaMarca .cerrar').click(function () {
        cerrarInfoAudio();
    });

    if (nombreUsuario != undefined) {
        //Eventos de puntuar
        $('#puntuarPositivo').click(function () {
            puntuar(esto.id, 1);
        });
        $('#puntuarNegativo').click(function () {
            puntuar(esto.id, 0);
        });

        //Inapropiado
        $('#marcarInapropiado').click(function () {
            abrirVentanaDenunciar(esto);
        });
    }

    //Compartir
    $('#compartirTwitter').click(function () {
        compartirTwitter(esto.id);
    });
    $('#compartirFacebook').click(function () {
        compartirFacebook(esto.id);
    });

    //Eliminamos el reproductor de estar presente
    if (reproductor !== undefined) {
        reproductor.eliminar();
        reproductor = undefined;
    }
    //Quitamos la parte de la extensión (dejando el punto)
    var rutaDividida = esto.ruta.split('.');
    var ruta = esto.ruta.substring(0, esto.ruta.length - rutaDividida[rutaDividida.length - 1].length);
    reproductor = new Reproductor(ruta + 'mp3', ruta + 'ogg', 'play', base_url_img + 'img/play.png', base_url_img +
            'img/pause.png', base_url_img + 'img/cargando.gif', base_url_img + 'img/error.png', 'lineaTiempo', undefined, 'reloj');
    reproductor.controles.addEventListener('click', function () {
        contarDescarga(esto.id);
    });
    reproductor.addAccionReproducirHandler(function () {
        detenerOtrosAudios(reproductor.controles);
    });

    $('#nombreUsuarioMarca').click(function () {
        var vistaPerfil = new VistaPerfil(esto.idUsuario, esto.usuario, tellmee_Servidor, nombreUsuario);
        vistaPerfil.dibujar();
    });
}

function cerrarInfoAudio() {
    //Eliminamos el reproductor de estar presente
    if (reproductor !== undefined) {
        reproductor.eliminar();
        reproductor = undefined;
    }

    cerrarImagen();
    cerrarVentanaDenunciar();
    $('.padreVentanaMarca').remove();
    quitarSuperposiciones();
}

function abrirVentanaDenunciar(esto) {
    $('.padreVentanaMarca').css('z-index', 0);

    var ventana = $('<div class="padreVentanaDenunciar"></div>');
    var ventanaInterna = $(
            '<div class="ventanaDenunciar">' +
            '<div class="cabecera">' +
            '<h1>' + textos['denunciarContenido'] + '</h1>' +
            '<p>' + textos['porqueDenuncias'] + '</p>' +
            '</div>' +
            '<div class="cuerpo">' +
            '<p><label><input type="radio" name="inapropiado" value="1"/> ' + textos['contenidoFraudulento'] + '</label></p>' +
            '<div class="barraHorizontal"></div>' +
            '<p><label><input checked="checked" type="radio" name="inapropiado" value="2"/> ' + textos['contenidoInapropiado'] + '</label></p>' +
            '<div class="barraHorizontal"></div>' +
            '<p><label><input type="radio" name="inapropiado" value="3"/> ' + textos['contenidoAriesgado'] + '</label></p>' +
            '<div class="barraHorizontal"></div>' +
            '</div>' +
            '<div class="pie">' +
            '<div id="cancelarDenuncia">' + textos['cancelar'] + '</div>' +
            '<div id="enviarDenuncia">' + textos['enviarDenuncia'] + '</div>' +
            '<div style="float: none;clear: both"></div>' +
            '</div>' +
            '</div>' +
            '<img src="' + base_url_img + 'img/flechaInferiorNegra.png" alt="" class="flechaInferior"/>' +
            '<img src="' + base_url_img + 'img/categorias/mapa/' + esto.categoria + '.png" alt="' + categoriasPlataforma[esto.categoria] + '" class="categoriaActual"/>');
    centrarVentana(ventanaInterna, ventana);
    ventana.append(ventanaInterna);
    $('#contenedorMapa').append(ventana);

    $('#enviarDenuncia').click(function () {
        marcarInapropiado(esto.id, $('input[name="inapropiado"]:checked').val());
    });

    //Evento de cierre
    $('.padreVentanaDenunciar .cerrar, #cancelarDenuncia').click(function () {
        cerrarVentanaDenunciar();
    });
}

function cerrarVentanaDenunciar() {
    $('.padreVentanaMarca').css('z-index', 1001);
    $('.padreVentanaDenunciar').remove();
}

function crearInfoMarca(esto) {
    if (reproductor != undefined) {
        reproductor.eliminar();
        reproductor = undefined;
    }
    //Contenido de la ventana de información
    var contentString = '<div id="' + esto.id + '_tooltip" class="tooltip">' +
            '<div class="contenidoInfo">' +
            '<form action="' + base_url + 'index.php/mapa/subir" method="post" name="formu" enctype="multipart/form-data">' +
            '<input type="hidden" value="' + clave + '" name="clave"/>' +
            '<input type="hidden" value="' + esto.id + '" name="id"/>' +
            '<p><input class="file" type="file" name="audio"/></p>' +
            '</form>' +
            '<div style="float: left">' +
            '<p><strong>' + textos['usuario'] + ':</strong> ' + esto.usuario + '</p>' +
            '<p><strong>' + textos['categoria'] + ':</strong> ' + categorias[esto.categoria] + '</p>' +
            '<p><strong>' + textos['descripcion'] + ':</strong> ' + decodeURI(esto.descripcion) + '</p></div>';
    if (esto.usuario == nombreUsuario)
        contentString += '<img title="' + textos['botonBorrar'] + '" onclick="borrarElemento(' + esto.id + ')" src="' + base_url_img + 'img/borrar.png" alt="' + textos['botonBorrar'] + '" class="descargaImg"/>';
    contentString += '</div>' +
            '<div style="clear:both"></div>' +
            '</div>';

    return contentString;
}

function crearInfoGrupo(esto) {
    var contentString, icono, contentArray = {};
    //Agrupamos por ciudad y creamos la lista
    for (var x in esto.agrupados) {
        var elemento = esto.agrupados[x];

        if (elemento.idArea == undefined || elemento.idArea == '' || elemento.nombreArea == undefined || elemento.nombreArea == '') {
            //La ciudad por defecto
            elemento.idArea = 'varios';
            elemento.nombreArea = textos['varios'];
        }

        if (contentArray[elemento.idArea] == undefined) {
            //Creamos una nueva ciudad en la lista
            var clase = (elemento.idArea == 'varios' ? 'default' : '');
            var centrar = (elemento.idArea != 'varios' ? 'tellmee_Google.centrarVista(' + elemento.limitesArea[0] + ',' + elemento.limitesArea[1] + ',' + elemento.limitesArea[2] + ',' + elemento.limitesArea[3] + ')' : '');

            contentString = '<li onclick="' + centrar + '" class="area ' + clase + '">';
            if (elemento.nombreArea.length > 30)
                contentString += elemento.nombreArea.replace(/(<([^>]+)>)/ig, "").substr(0, 30) + '...';
            else
                contentString += elemento.nombreArea.replace(/(<([^>]+)>)/ig, "");
            contentString += '</li>';

            contentArray[elemento.idArea] = [contentString, '<li class="linea"><hr/></li>'];
        }

        contentString = '<li onclick="pulsadoElementoGrupo(' + esto.id + ', ' + x + ')">';
        //El icono por defecto
        if (elemento.getIcon() == undefined)
            icono = base_url_img + 'img/punterorojo.png';
        else
            icono = elemento.getIcon();

        contentString += '<img src="' + icono + '" alt="marca"/>';
        if (elemento.descripcion.length > 30)
            contentString += elemento.descripcion.replace(/(<([^>]+)>)/ig, "").substr(0, 30) + '...';
        else
            contentString += elemento.descripcion.replace(/(<([^>]+)>)/ig, "");
        contentString += '</li>';

        contentArray[elemento.idArea].push(contentString);
    }

    //Convertimos en un string
    contentString = '';
    for (var x in contentArray) {
        //Guardamos "varios" para el final
        if (x != 'varios') {
            for (var y in contentArray[x]) {
                contentString += contentArray[x][y];
            }
        }
    }
    if (contentArray['varios'] != undefined) {
        for (var x in contentArray['varios']) {
            contentString += contentArray['varios'][x];
        }
    }

    superposicionNegra(true, cerrarInfoGrupo);
    var ventana = $(
            '<div class="padreVentanaGrupo"></div>');
    var ventanaInterna = $(
            '<div class="ventanaMarca">' +
            '<ul class="tablita">' +
            contentString +
            '</ul>' +
            '<div style="float: none;clear: both"></div>' +
            '<img src="' + base_url_img + 'img/cerrar.png" alt="' + textos['cerrar'] + '" class="cerrar"/>' +
            '</div>' +
            '<img src="' + base_url_img + 'img/flechaInferior.png" alt="" class="flechaInferior"/>' +
            '<img src="' + base_url_img + 'img/categorias/mapa/grupo.png" alt="' + textos['grupo'] + '" class="categoriaActual"/>');
    ventana.append(ventanaInterna);
    centrarVentana(ventanaInterna, ventana);
    $('#contenedorMapa').append(ventana);
    $('.padreVentanaGrupo .cerrar').click(function () {
        cerrarInfoGrupo();
    });
}

function cerrarInfoGrupo() {
    $('.padreVentanaGrupo').remove();
    quitarSuperposiciones();
}

//Crea lo necesario para que funcione la lista de reproducción
function crearListaAudio() {
    var audio = document.getElementById('tagLista');
    if (audio != null && audio.canPlayType) {
        //Añadimos un evento reproducción finalizada para pasar a la canción siguiente
        audio.addEventListener('ended', function () {
            ponerASonar(listaReproduccion.siguienteAudio());
        }, false);
    }
}

function deslistar(pos) {
    //Si se borra el actual se pasa al siguiente
    if (pos == listaReproduccion.obtenerActual())
        ponerASonar(listaReproduccion.siguienteAudio());

    listaReproduccion.eliminarAudio(pos);
    generarLista();
}

function listar(ruta, descripcion, idMarca) {
    var desc, pos;

    //Lo añadimos también a la lista visual
    if (descripcion.length < 10)
        desc = descripcion;
    else
        desc = descripcion.substring(0, 8) + '...';

    //Añadimos un audio a la lista
    pos = listaReproduccion.anhadirAudio(ruta, desc, idMarca);
    generarLista();
    //Si era el primero lo ponemos a sonar
    if (pos == 0) {
        ponerASonar(listaReproduccion.obtenerAudio(pos));
    }
}

function suena(pos) {
    ponerASonar(listaReproduccion.obtenerAudio(pos));
}

function marcarSonando() {
    //Destacamos el que suena en la lista
    $("*[id|=lista_li]").removeClass('sonando');
    $("#lista_li-" + listaReproduccion.obtenerActual()).addClass("sonando");
}

function ponerASonar(audio) {
    var audioTag = document.getElementById('tagLista');

    if (audio != undefined) {
        audioTag.children[0].src = audio[0] + 'ogg';
        audioTag.children[1].src = audio[0] + 'mp3';
        audioTag.load();
        audioTag.play();
    }
    else {
        audioTag.pause();
    }

    marcarSonando();
}

function abrirId(id) {
    tellmee_Servidor.localizarAudio(id, function (data) {
        if (data.ok) {
            //Obtenemos el id corregido si fuese necesario
            idLocalizado = data.id;

            centrarMapa();
        }
    }, function () {
    });
}

function generarLista() {
    var lista = '';

    for (var i = 0, audios = listaReproduccion.obtenerLista(), len = audios.length; i < len; i++)
        lista += '<li id="lista_li-' + i + '"><span onclick="abrirId(' + audios[i][2] + ')">' + audios[i][1] +
                '</span> <img alt="' + textos['sonar'] + '" onclick="suena(' + i + ')" title="' + textos['sonar'] + '" src="' + base_url_img +
                'img/sonar.png"/> <img alt="' + textos['deslistar'] + '" onclick="deslistar(' + i +
                ')" title="' + textos['deslistar'] + '" src="' + base_url_img + 'img/menos.png"/></li>';

    $('#listaAudio ul').html(lista);

    marcarSonando();
}

function crearInfoUsuario() {
    return '<div id="content">' +
            '<p>' + nombreUsuario + '</p>' +
            '</div>';
}

function pulsadoElementoGrupo(grupo, indiceMarca) {
    cerrarInfoGrupo();

    var esto = tellmee_Google.capas['marcadores'].grupos[grupo].agrupados[indiceMarca];

    if (esto.marca) {
        //Es una marca
        crearInfoMarca(esto);

        $('input[type="file"]').change(function () {
            $.blockUI({
                message: "<p>" + textos['desbloquear'] + "</p><input type='button' name='enviar' value='" + textos['enviar'] + "' onclick='enviar()'/>"
            });
            $('.blockOverlay').click($.unblockUI);
        });
    }
    else {
        //Es un audio
        crearInfoAudio(esto);
    }
}

function estrellitas(valoracion) {
    var resultado = "";

    for (var i = 0; i < 5; i++)
        if (valoracion > i)
            resultado += "<img src='" + base_url_img + "img/estrella.png' alt='\u2605' width='16'/>";
        else
            resultado += "<img src='" + base_url_img + "img/estrella_gris.png' alt='\u2606' width='16'/>";

    return resultado;
}

function abrirGrupo() {
    crearInfoGrupo(this);
}

function centrarMapa() {
    //Abrimos el globo de la marca buscada
    if (idLocalizado != undefined) {
        var marcador;

        marcador = tellmee_Google.capas['marcadores'].obtenerMarcador(idLocalizado);

        if (marcador.marca)
            crearInfoMarca(marcador);
        else
            crearInfoAudio(marcador);

        idLocalizado = undefined;
    }
    else {
        if (direccion != undefined) {
            tellmee_Google.localizarDireccion(direccion, base_url_img +
                    'img/circulo/circulo.1.png', textos['nadaEncontrado'], zoomLocalizado);

            direccion = undefined;
        }
    }
}

function pulsadoElementoGrupoRuta(grupo, indiceMarca) {
    var esto = tellmee_Google.capas['marcadoresParaCrearRutas'].grupos[grupo].agrupados[indiceMarca];

    if (esto.marca == 0) {
        cerrarInfoGrupo();
        //Es un audio
        tellmee_Google.globoRuta.modificarGlobo(esto, crearMiniInfo(esto));
        tellmee_Google.globoRuta.abrirGlobo();
    }
}

function crearInfoGrupoRuta(esto) {
    var contentString = '', icono;
    //Colocamos los iconos de los otros punteros con un número
    for (var x in esto.agrupados) {
        contentString += '<li onclick="pulsadoElementoGrupoRuta(' + esto.id + ', ' + x + ')">';
        //El icono por defecto
        if (esto.agrupados[x].getIcon() == undefined)
            icono = base_url_img + 'img/punterorojo.png';
        else
            icono = esto.agrupados[x].getIcon();

        contentString += '<img src="' + icono + '" alt="marca"/>';
        if (esto.agrupados[x].descripcion.length > 30)
            contentString += esto.agrupados[x].descripcion.replace(/(<([^>]+)>)/ig, "").substr(0, 30) + '...';
        else
            contentString += esto.agrupados[x].descripcion.replace(/(<([^>]+)>)/ig, "");
        contentString += '</li>';
    }

    superposicionNegra(true, cerrarInfoGrupo);
    var ventana = $('<div class="padreVentanaGrupo"></div>');
    var ventanaInterna = $(
            '<div class="ventanaMarca">' +
            '<ul class="tablita">' +
            contentString +
            '</ul>' +
            '<div style="float: none;clear: both"></div>' +
            '<img src="' + base_url_img + 'img/cerrar.png" alt="' + textos['cerrar'] + '" class="cerrar"/>' +
            '</div>' +
            '<img src="' + base_url_img + 'img/flechaInferior.png" alt="" class="flechaInferior"/>' +
            '<img src="' + base_url_img + 'img/categorias/mapa/grupo.png" alt="' + textos['grupo'] + '" class="categoriaActual"/>');
    centrarVentana(ventanaInterna, ventana);
    ventana.append(ventanaInterna);
    $('#contenedorMapa').append(ventana);

    $('.padreVentanaGrupo .cerrar').click(function () {
        cerrarInfoGrupo();
    });
}

function abrirGrupoRuta() {
    crearInfoGrupoRuta(this);
}

function ampliarRuta(idMarca) {
    var marcador = tellmee_Google.capas['marcadoresParaCrearRutas'].obtenerMarcador(idMarca);
    rutaInterfaz.anhadirMarca(marcador, espera, finEspera);
    $('#cantidadPuntos').html(rutaInterfaz.cantidad());
}

function borrarRutas(idsRuta) {
    if (confirm(textos['rutaBorrar'])) {
        espera();
        var ids = new String(idsRuta).split(',');
        tellmee_Servidor.borrarRuta(ids, function (datos) {
            if (datos.ok) {
                tellmee_Google.capas['rutas'].ocultar();
                for (var x = 0, len = ids.length; x < len; x++)
                    tellmee_Google.capas['rutas'].eliminarRuta(ids[x]);
                tellmee_Google.capas['rutas'].mostrar(undefined, tellmee_Google.mapa);
            }
            else
                alert(textos['error_desconocido']);

            finEspera();
        });

    }
}

function crearMiniInfo(marca) {
    var enRutaActual = enArray(rutaInterfaz.idsRuta(), marca.id);

    var resultado =
            '<div class="ventanaAddMarca">' +
            '<p>' + marca.descripcion + '</p>' +
            (!enRutaActual ? '<img id="botonAddRuta" onclick="ampliarRuta(' + marca.id + ');$(\'#botonAddRuta\').remove();" src="' + base_url_img + 'img/anadir-punto-ruta-icon-56.png" alt="' + textos['anhadirARuta'] + '"/>' : '') +
            '</div>';

    return resultado;
}

function iniciarCrearRuta() {
    tellmee_Google.ocultarCapa('marcadores');
    tellmee_Servidor.pararAutoSolicitarAudios();

    var listaCategorias = '';
    for (var x in categoriasPlataforma) {
        listaCategorias += '<li><img src="' + base_url_img + 'img/categorias/' + x + '.png" alt="' + categoriasPlataforma[x] + '"/></li>';
    }

    //Ventana de información
    var ventana = $(
            '<div class="ventanaAyudaRuta">' +
            '<h1>' + textos['paraCrearRuta'] + '</h1>' +
            '<p>' + textos['explicacionRuta1'] + '</p>' +
            '<ul>' +
            listaCategorias +
            '</ul>' +
            '<p>' + textos['explicacionRuta2'] + ' <img src="' + base_url_img + 'img/anadir-punto-ruta-icon-56.png" alt="Añadir"/> ' + textos['explicacionRuta3'] + '</p>' +
            '<p id="botonCerrarAyuda">' + textos['deAcuerdo'] + '</p>' +
            '</div>');
    centrarVentana(ventana, ventana);
    $('#contenedorMapa').append(ventana);

    $('#botonCerrarAyuda').click(function () {
        $('.ventanaAyudaRuta').remove();
    });

    //Mezcla las categorías
    var mezcla = [];
    for (var x = 0, len = listaCat.length; x < len; x++) {
        if (marcadores[listaCat[x]] !== undefined) {
            mezcla = mezcla.concat(marcadores[listaCat[x]]);
        }
    }

    var capa = new Tellmee_Capa(base_url + 'imagenGrupo.php', alcanceGrupos), iter, icono;

    for (var x = 0; x < mezcla.length; x++) {
        iter = mezcla[x];

        if (iter.marca == 0) {
            if (fotoPuntos && iter.fondo) {
                icono = new Tellmee_Icono(iter.fondo, 40, 40);
            }
            else {
                icono = base_url_img + 'img/categorias/mapa/' + iter.categoria + '.png';
            }

            capa.anhadirMarcador(iter.id, iter.la, iter.lo, icono,
                    function () {
                        //Ponemos el nombre y el botón añadir
                        var contentString = crearMiniInfo(this);

                        if (tellmee_Google.globoRuta == undefined || tellmee_Google.globoRuta.marcador == undefined ||
                                !(tellmee_Google.globoRuta.marcador.id == this.id && tellmee_Google.globoRuta.marcador.tipo == this.tipo)) {
                            if (tellmee_Google.globoRuta == undefined) {
                                tellmee_Google.globoRuta = new Tellmee_GloboDecorado(tellmee_Google.mapa,
                                        {background: "url('" + base_url_img + "img/tipbox.gif') no-repeat 78px 0", opacity: 0.9});

                                tellmee_Google.globoRuta.anhadirEvento('closeclick', function () {
                                    tellmee_Google.globoRuta.cerrarGlobo();
                                });
                            }
                            tellmee_Google.globoRuta.modificarGlobo(this, contentString);
                            tellmee_Google.globoRuta.abrirGlobo();
                        }
                    },
                    {
                        usuario: iter.user,
                        rutas: iter.rutas,
                        categoria: iter.categoria,
                        descargas: iter.descargas,
                        descripcion: iter.descripcion,
                        puntosPositivos: iter.puntosPositivos,
                        puntosNegativos: iter.puntosNegativos,
                        marca: iter.marca,
                        fondo: iter.fondo,
                        idUsuario: iter.idUser,
                        idArea: iter.idArea,
                        nombreArea: iter.nombreArea,
                        limitesArea: iter.limitesArea
                    }
            );
        }
    }

    //Generamos los grupos
    capa.generarGruposRadial(tellmee_Google.obtenerZoom(), abrirGrupoRuta);

    //Creamos la nueva capa
    tellmee_Google.anhadirCapa('marcadoresParaCrearRutas', capa);
    tellmee_Google.mostrarCapa('marcadoresParaCrearRutas');

    //Instanciamos el objeto accesorio para crear rutas
    rutaInterfaz = new RutaInterfaz(tellmee_Google.mapa);
}

function almacenarAudios(datos) {
    if (datos.ok) {
        marcadores = datos.marcadores;

        mezclarCategorias();
    }
}

function mezclarCategorias() {
    var mezcla = new Array();

    for (var x = 0, len = listaCat.length; x < len; x++) {
        if (marcadores[listaCat[x]] !== undefined) {
            mezcla = mezcla.concat(marcadores[listaCat[x]]);
        }
    }

    solicitarAudios({ok: true, marcadores: mezcla});
}

function solicitarAudios(datos) {
    if (datos.ok) {
        var capa = new Tellmee_Capa(base_url + 'imagenGrupo.php', alcanceGrupos), iter, icono;

        for (var x = 0; x < datos.marcadores.length; x++) {
            iter = datos.marcadores[x];

            if (iter.marca == 0) {
                if (fotoPuntos && iter.fondo) {
                    icono = new Tellmee_Icono(iter.fondo, 40, 40);
                }
                else {
                    icono = base_url_img + 'img/categorias/mapa/' + iter.categoria + '.png';
                }
            }
            else {
                icono = base_url_img + 'img/punteroamarillo.png';
            }

            capa.anhadirMarcador(iter.id, iter.la, iter.lo, icono,
                    function () {
                        if (this.marca == 0)
                            crearInfoAudio(this);
                        else
                            crearInfoMarca(this);

                        $('input[type="file"]').change(function () {
                            $.blockUI({
                                message: "<p>" + textos['desbloquear'] + "</p><input type='button' name='enviar' value='" + textos['enviar'] + "' onclick='enviar()'/>"
                            });
                            $('.blockOverlay').click($.unblockUI);
                        });
                    },
                    {
                        usuario: iter.user,
                        ruta: iter.ruta,
                        categoria: iter.categoria,
                        descargas: iter.descargas,
                        descripcion: iter.descripcion,
                        puntosPositivos: iter.puntosPositivos,
                        puntosNegativos: iter.puntosNegativos,
                        marca: iter.marca,
                        fondo: iter.fondo,
                        idUsuario: iter.idUser,
                        idArea: iter.idArea,
                        nombreArea: iter.nombreArea,
                        limitesArea: iter.limitesArea
                    }
            );
        }

        //Volvemos a crear las capas
        capa.generarGruposRadial(tellmee_Google.obtenerZoom(), abrirGrupo);
        tellmee_Google.ocultarCapa('marcadores');
        tellmee_Google.anhadirCapa('marcadores', capa);
        tellmee_Google.mostrarCapa('marcadores');

        centrarMapa();

        //solicitamos también las rutas
        if (nombreUsuario != undefined || true) {//TODO: por el momento todas las rutas serán públicas
            tellmee_Servidor.solicitarRutas(function (datos) {
                if (datos.ok) {
                    var capaRutas = new Tellmee_CapaRutas();

                    tellmee_Google.ocultarCapa('rutas');
                    tellmee_Google.anhadirCapa('rutas', capaRutas);
                    for (var x in datos.datos) {
                        if(datos.datos[x]) {
                            var marcadores = new Array();
                            for (var y = 0, len = datos.datos[x].length; y < len; y++) {
                                var marcador = tellmee_Google.capas['marcadores'].obtenerMarcador(datos.datos[x][y]);
                                if (marcador)
                                    marcadores.push(marcador);
                            }

                            capaRutas.anhadirRuta(x, marcadores, function () {
                                if (getCookie('rutasActivas') == 1) {
                                    tellmee_Google.mostrarCapa('rutas');
                                }
                            });
                        }
                    }
                }
            });
        }
    }
    else {
        tellmee_Google.ocultarCapa('marcadores');
        tellmee_Google.eliminarCapa('marcadores');
    }

    //Sistema de audios aleatorios
    if (!aleatorio && !movil) {
        aleatorio = true;

        var marcas = tellmee_Google.capas['marcadores'].marcadores;
        var aleatoria = desordenarLista(marcas, 50);

        superposicionNegra(false);

        var ventana = $(
                '<div id="ventanaInicio" class="ventanaFormulario">' +
                '<p><img src="' + base_url_img + 'img/LOGO-panoraudio-32.png" alt="Panoraudio"/></p>' +
                '<h1>' + textos['comienza'] + '</h1>' +
                '<p><span id="botonDejateGuiar" class="boton">' + textos['dejateGuiar'] + '</span></p>' +
                '<p><span id="botonLibremente" class="boton fondoAzul">' + textos['libremente'] + '</span></p>' +
                '</div>');

        centrarVentana(ventana, ventana);
        $('#contenedorMapa').append(ventana);

        $('#botonDejateGuiar').click(function () {
            cerrarInfoAudioAleatorio(false);
        });
        $('#botonLibremente').click(function () {
            cerrarInfoAudioAleatorio(true);
        });

        tellmee_Google.centrarMapa(aleatoria[0].position.lat(), aleatoria[0].position.lng());
        tellmee_Google.cambiarZoom(zoomLocalizado);

        $('#contenedorMapa').append('<div id="slider"></div>');
        var slider = new Slider('#slider', 2, function (marca) {
            tellmee_Google.centrarMapa(marca.position.lat(), marca.position.lng());
            tellmee_Google.cambiarZoom(zoomLocalizado);
        });

        for (var x = 0, len = aleatoria.length; x < len; x++) {
            slider.addMarca(aleatoria[x]);
        }

        slider.dibujar();

        //Para que solamente suene uno cada vez
        $('.play').click(function (ev) {
            if (ev.which) {
                detenerOtrosAudios(this);
            }
        });
    }
}

function mostrarAyuda() {
    if($(".superposicionNegra").length == 0) {
        superposicionNegra(true);
        $(".superposicionNegra").css("z-index", 1020);
        var div_imagf1 = $('<div id="flecha-ayuda-2"><img src="' + base_url_img + 'img/flecha-ayuda-2.png"/><p class="text_ayuda">' + textos['ico_reg_ayuda'] + '</p></div>');//contengo la imagen	
        $('.superposicionNegra').append(div_imagf1);
        var posf1 = $('#botonRegistroVentana').offset();	//averiguo su posicion
        div_imagf1.css({
            position: 'absolute',
            'top': posf1.top - 40,
            'left': posf1.left + 84
        });
         
        var div_imagf2 = $('<div id="flecha-ayuda-1"><img src="' + base_url_img + 'img/flecha-ayuda-1.png"/><p class="text_ayuda">' + textos['ico_cat_ayuda'] + '</p></div>');//contengo la imagen	
        $('.superposicionNegra').append(div_imagf2);
        var posf2 = $('#1_categoria').offset();	//averiguo su posicion
        div_imagf2.css({
            position: 'absolute',
            'top': posf2.top - 25,
            'left': posf2.left - 60
        });
         
        var div_imagf3 = $('<div id="flecha-ayuda-3"><p class="text_ayuda">' + textos['ico_vist_ayuda'] + '</p><img src="' + base_url_img + 'img/flecha-ayuda-4.png"/></div>');//contengo la imagen	
        $('.superposicionNegra').append(div_imagf3);
        var posf3 = $('#localizarse').offset();	//averiguo su posicion
        div_imagf3.css({
            position: 'absolute',
            'top': posf3.top - 260,
            'left': posf3.left - 300
        });
         
        var div_imagf4 = $('<div id="flecha-ayuda-4"><p class="text_ayuda" id="text_ayuda2">' + textos['ico_der_ayuda'] + '</p><img src="' + base_url_img + 'img/flecha-ayuda-4.png"/></div>');//contengo la imagen	
        $('.superposicionNegra').append(div_imagf4);
        var posf4 = $('#localizarse').offset();	//averiguo su posicion
        div_imagf4.css({
            position: 'absolute',
            'top': posf4.top - 150,
            'left': posf4.left - 382
        });
         
        var div_ok = $('<div id="entendido"><p id="boton_ok" class="boton">' + textos['entendido_ayuda'] + '</p></div>');
        $('.superposicionNegra').append(div_ok);
        var posf5 = $('#1_categoria').offset();	//averiguo su posicion
        div_ok.css({
            position: 'absolute',
            'top': (posf5.top),
            'left': (posf5.left) + 36
        });
    }
    else {
        quitarSuperposiciones();
    }
}

function continuar(posicionMapa, posicionUsuario) {
    var distancia;

    //Elegimos el zoom y la posición
    if (posicionMapa == null) {
        if (posicionUsuario != null) {
            //Intentamos centrar en el usuario
            obtenerImagenStreet(1, 1, posicionUsuario.coords.latitude, posicionUsuario.coords.longitude, function (ruta, comprobada) {
                //Nos aseguramos de que hay imagen
                if (comprobada)
                    localStorage.localizacionUsuario = '{"coords": {"latitude":' + posicionUsuario.coords.latitude + ',"longitude":' + posicionUsuario.coords.longitude + '}}';
                else
                    localStorage.localizacionUsuario = undefined;
            });
            posicionMapa = posicionUsuario;
            distancia = zoomLocalizado;
        }
        else {
            //Centramos en un punto cualquiera
            posicionMapa = {
                coords: {
                    latitude: 1,
                    longitude: 1
                }
            };
            distancia = zoomNoLocalizado;
        }
    }
    else {
        distancia = zoomLocalizado;
    }

    //Ponemos el mapa
    if (tellmee_Google == undefined) {
        tellmee_Google = new Tellmee_Mapa(document.getElementById("mapa"), posicionMapa.coords.latitude,
                posicionMapa.coords.longitude, distancia, true);

        //Solicitamos las categorías
        tellmee_Servidor.solicitarCategorias(function (data) {
            if (data.ok)
                categorias = data.items;
        });

        //Solicitamos los audios
        tellmee_Servidor.solicitarAudios();
        tellmee_Servidor.arrancarAutoSolicitarAudios();

        //Evento para rehacer los grupos según el zoom
        tellmee_Google.anhadirEvento('zoom_changed', function () {
            //Si está la capa de crear rutas agrupamos esa
            if (tellmee_Google.capas['marcadoresParaCrearRutas']) {
                tellmee_Google.ocultarCapa('marcadoresParaCrearRutas');
                tellmee_Google.capas['marcadoresParaCrearRutas'].generarGruposRadial(tellmee_Google.obtenerZoom(), abrirGrupoRuta);
                tellmee_Google.mostrarCapa('marcadoresParaCrearRutas');
            }
            else {
                tellmee_Google.ocultarCapa('marcadores');
                tellmee_Google.capas['marcadores'].generarGruposRadial(tellmee_Google.obtenerZoom(), abrirGrupo);
                tellmee_Google.mostrarCapa('marcadores');
            }
        });

        //Evento para cerrar del todo los globos
        tellmee_Google.globo.anhadirEvento('closeclick', function () {
            if (reproductor != undefined) {
                reproductor.eliminar();
                reproductor = undefined;
            }
            tellmee_Google.globo.cerrarGlobo();
            $('#infoAudio').remove();
        });

        //Evento para cambiar el tipo de mapa
        $('#tipoMapa').click(function () {
            tellmee_Google.cambiarTipo();

            if ($('#tipoMapa').attr('src').indexOf('tierra') !== -1) {
                $('#tipoMapa').attr('src', base_url_img + 'img/mapa-icon.png');
            }
            else {
                $('#tipoMapa').attr('src', base_url_img + 'img/tierra-icon.png');
            }
        });

    }
    else {
        if (posicionMapa) {
            tellmee_Google.centrarMapa(posicionMapa.coords.latitude,
                    posicionMapa.coords.longitude);

            if (vistaLista) {
                vistaLista.actualizarVistaLista();
            }
        }
    }

    //Colocamos al usuario
    if (posicionUsuario != null) {
        var capaUsuario = new Tellmee_Capa();
        capaUsuario.anhadirMarcador(0, posicionUsuario.coords.latitude, posicionUsuario.coords.longitude,
                base_url_img + 'img/punteroverde.png', undefined, {
                    title: "Yo",
                    tipo: "Yo"
                });
        capaUsuario.mostrar(false, tellmee_Google.mapa);
        tellmee_Google.anhadirCapa('usuario', capaUsuario);
    }

    //Solicitamos el nombre de usuario
    tellmee_Servidor.obtenerUsuario(function (datos) {
        if (datos.ok) {
            nombreUsuario = datos.usuario;
            if (posicionUsuario != null) {
                //Le colocamos una ventana de información si se tercia
                tellmee_Google.capas['usuario'].anhadirEvento(0, 'click',
                        function () {
                            var contentString = crearInfoUsuario();

                            if (tellmee_Google.globo.marcador == undefined || tellmee_Google.globo.marcador.id != 0) {
                                tellmee_Google.globo.modificarGlobo(tellmee_Google.capas['usuario'].marcadores[0], contentString);
                                tellmee_Google.globo.abrirGlobo();
                            }
                        }
                );
            }
        }
    });
}

function geoLocalizar(centerPosition) {
    if (navigator.geolocation) {
        //Geolocalización
        navigator.geolocation.getCurrentPosition(
                function (geoPosition) {
                    //Localizado
                    continuar(centerPosition, geoPosition);
                },
                function () {
                    continuar(centerPosition, null);
                },
                {
                    maximumAge: 1000,
                    timeout: 5500,
                    enableHighAccuracy: true
                }
        );
    }
    else {
        continuar(centerPosition, null);
    }
}

function terminarMarcado() {
    //Quitamos los eventos de poner marca
    tellmee_Google.desactivarClickSinArrastrar();
    //El botón cancelar y el mensaje
    $('#cancelar').remove();
    $('#mensajeMarca').remove();
    //La ventana de marcado
    $('.ventanaPonerMarca').remove();
    //Recuperamos las marcas
    tellmee_Google.mostrarCapa('marcadores');
    tellmee_Servidor.arrancarAutoSolicitarAudios();
    //Recuperamos los controles
    $('#botonesIzq,#botonesDer,.menuTira').show();
}

function terminarMarcadoRuta() {
    //Quitamos los eventos de poner marca
    tellmee_Google.desactivarClickSinArrastrar();
    //El menú de ruta y el globo
    $('#menuRuta').remove();
    if (tellmee_Google.globoRuta != undefined) {
        tellmee_Google.globoRuta.cerrarGlobo();
    }
    //Quitamos las marcas de ruta
    tellmee_Google.ocultarCapa('marcadoresParaCrearRutas');
    tellmee_Google.eliminarCapa('marcadoresParaCrearRutas');
    //Recuperamos las marcas
    tellmee_Google.mostrarCapa('marcadores');
    tellmee_Servidor.arrancarAutoSolicitarAudios();
    //Recuperamos los controles
    $('#botonesIzq,#botonesDer,.menuTira').show();

    rutaInterfaz = undefined;
}

function abrirPanelMarca(eventoRaton) {
    superposicionNegra(true, terminarMarcado);

    var optionsIdioma = '';
    for (var x in idiomasPlataforma) {
        optionsIdioma += '<option value="' + x + '">' + idiomasPlataforma[x] + '</option>';
    }

    var optionsCategorias = '';
    for (var x in categoriasPlataforma) {
        optionsCategorias += '<option value="' + x + '">' + categoriasPlataforma[x] + '</option>';
    }

    var ventana = $(
            '<div class="ventanaPonerMarca">' +
            '<img src="' + base_url_img + 'img/LOGO-panoraudio-32.png" alt="Panoraudio"/>' +
            '<form enctype="multipart/form-data" name="formu" method="post" action="' + base_url + 'index.php/mapa/ponerMarca">' +
            '<input type="hidden" name="latitud" value=""/>' +
            '<input type="hidden" name="longitud" value=""/>' +
            '<input type="hidden" name="idArea" value=""/>' +
            '<input type="hidden" name="nombreArea" value=""/>' +
            '<input type="hidden" name="latitudIzquierdaInferior" value=""/>' +
            '<input type="hidden" name="longitudIzquierdaInferior" value=""/>' +
            '<input type="hidden" name="latitudDerechaSuperior" value=""/>' +
            '<input type="hidden" name="longitudDerechaSuperior" value=""/>' +
            '<p>' + textos['idiomaAudio'] + '<span class="azul">*</span></p>' +
            '<select class="select" name="idiomaAudio">' +
            optionsIdioma +
            '</select>' +
            '<p>' + textos['seleccionarCategoria'] + '<span class="azul">*</span></p>' +
            '<select class="select" name="categoria">' +
            optionsCategorias +
            '</select>' +
            '<p>' + textos['descripcionMarca'] + '</p>' +
            '<textarea name="descripcion" class="textarea descripcion">' + textos['descripcion'] + '</textarea>' +
            '<p class="azul peque">* ' + textos['obigatorio'] + '</p>' +
            '<ul>' +
            '<li>' +
            '<img id="botonSeleccionarAudio" src="' + base_url_img + 'img/audio-desactivado-icon.png" alt="' + textos['subirAudio'] + '" title="' + textos['subirAudio'] + '"/>' +
            '<p>' + textos['subirAudio'] + '</p>' +
            '<p class="azul peque">' + textos['audioMax'] + '</p>' +
            '<input type="file" accept="audio/*" name="audio" value=""/>' +
            '</li>' +
            '<li>' +
            '<img id="botonSeleccionarFoto" src="' + base_url_img + 'img/foto-desactivado-icon.png" alt="' + textos['subirFoto'] + '" title="' + textos['subirFoto'] + '"/>' +
            '<p>' + textos['subirFoto'] + '</p>' +
            '<p class="azul peque">' + textos['fotoMax'] + '</p>' +
            '<input type="file" accept="image/jpeg,image/png,image/gif" name="fondo" value=""/>' +
            '</li>' +
            '</ul>' +
            '</form>' +
            '<div style="float: none;clear: both"></div>' +
            '<span id="botonPonerMarca" class="boton">' + textos['colocaMarca'] + '</span>' +
            '<img src="' + base_url_img + 'img/cerrar.png" alt="' + textos['cerrar'] + '" class="cerrar"/>' +
            '</div>');

    centrarVentana(ventana, ventana);
    $('#contenedorMapa').append(ventana);

    var formuEnviable = false, areaLista = false;

    //Solicitamos el area
    tellmee_Google.identificarArea(eventoRaton.latLng.lat(), eventoRaton.latLng.lng(), function (area) {
        if (area) {
            $('input[name="idArea"]').val(area[0]);
            $('input[name="nombreArea"]').val(area[1]);
            $('input[name="latitudIzquierdaInferior"]').val(area[2]);
            $('input[name="longitudIzquierdaInferior"]').val(area[3]);
            $('input[name="latitudDerechaSuperior"]').val(area[4]);
            $('input[name="longitudDerechaSuperior"]').val(area[5]);
        }

        areaLista = true;
        if (formuEnviable) {
            $('form[name="formu"]').submit();
        }
    });

    //Introducimos las coordenadas en el formulario
    $('input[name="latitud"]').val(eventoRaton.latLng.lat());
    $('input[name="longitud"]').val(eventoRaton.latLng.lng());
    //Texto de ayuda
    $('textarea[name="descripcion"]').focusin(entraInput);
    $('textarea[name="descripcion"]').focusout(saleInput);

    //Evento de creación
    $('#botonPonerMarca').click(function () {
        if ($('input[name="audio"]').val()) {
            formuEnviable = true;
            if (areaLista) {
                $('form[name="formu"]').submit();
            }
        }
        else {
            abrirEmergente(textos['tituloFaltaAudio'], textos['textoFaltaAudio'], true);
        }
    });

    //Evento de cierre
    $('.ventanaPonerMarca .cerrar').click(function () {
        quitarSuperposiciones();
        terminarMarcado();
    });

    //Eventos de selección de audio
    $('#botonSeleccionarAudio').click(function () {
        $('input[name="audio"]').click();
    });
    $('#botonSeleccionarAudio').mouseenter(function () {
        var imagen = $(this);
        imagen.attr('oldSrc', imagen.attr('src'));
        imagen.attr('src', base_url_img + 'img/audio-hover-icon.png');
    });
    $('#botonSeleccionarAudio').mouseleave(function () {
        var imagen = $(this);
        imagen.attr('src', imagen.attr('oldSrc'));
        imagen.attr('oldSrc', '');
    });
    $('input[name="audio"]').change(function () {
        $('#botonSeleccionarAudio').attr('oldSrc', base_url_img + 'img/audio-ok-icon.png');
    });

    //Eventos de selección de imagen
    $('#botonSeleccionarFoto').click(function () {
        $('input[name="fondo"]').click();
    });
    $('#botonSeleccionarFoto').mouseenter(function () {
        var imagen = $(this);
        imagen.attr('oldSrc', imagen.attr('src'));
        imagen.attr('src', base_url_img + 'img/foto-hover-icon.png');
    });
    $('#botonSeleccionarFoto').mouseleave(function () {
        var imagen = $(this);
        imagen.attr('src', imagen.attr('oldSrc'));
        imagen.attr('oldSrc', '');
    });
    $('input[name="fondo"]').change(function () {
        $('#botonSeleccionarFoto').attr('oldSrc', base_url_img + 'img/foto-ok-icon.png');
    });
}

function abrirMenuMarcaRuta() {
    var ventana = $(
            '<div class="ventanaTipoCreacion">' +
            '<img src="' + base_url_img + 'img/LOGO-panoraudio-32.png" alt="Panoraudio"/>' +
            '<p>' + textos['rutaOMarca'] + '</p>' +
            '<table>' +
            '<tr>' +
            '<td><img src="' + base_url_img + 'img/marca-unitaria-icon.png" alt="' + textos['marca'] + '"/></td>' +
            '<td><span id="botonCrearMarca" class="boton">' + textos['ponerMarca'] + '</span></td>' +
            '</tr>' +
            '<tr>' +
            '<td><img src="' + base_url_img + 'img/ruta-icon.png" alt="' + textos['ruta'] + '"/></td>' +
            '<td><span id="botonCrearRuta" class="boton fondoAzul">' + textos['crearRuta'] + '</span></td>' +
            '</tr>' +
            '</table>' +
            '<img src="' + base_url_img + 'img/cerrar.png" alt="' + textos['cerrar'] + '" class="cerrar"/>' +
            '</div>');
    centrarVentana(ventana, ventana);
    $('#contenedorMapa').append(ventana);

    $('.ventanaTipoCreacion .cerrar').click(function () {
        cerrarMenuMarcaRuta();
        quitarSuperposiciones();
    });
    $('#botonCrearMarca').click(function () {
        if ($('#cancelar').length == 0) {
            cerrarMenuMarcaRuta();
            quitarSuperposiciones();

            //Ocultamos las marcas
            tellmee_Google.ocultarCapa('marcadores');
            //Dejamos de sincronizar
            tellmee_Servidor.pararAutoSolicitarAudios();

            //Eventos para poner la marca con un toque
            tellmee_Google.activarClickSinArrastrar(function (eventoRaton) {
                abrirPanelMarca(eventoRaton);
            });

            //Ocultar controles
            $('#botonesIzq,#botonesDer,.menuTira').hide();

            //Nuevo botón
            $('#contenedorMapa').append('<div id="cancelar" class="botonesFlotante"><input type="button" name="cancelar" value="' + textos['cancelarMarcado'] + '" class="boton"/></div><div id="mensajeMarca" class="mensajeFlotante">' + textos['colocaMarca'] + '</div>');
            $('input[name="cancelar"]').click(terminarMarcado);
        }
    });
    $('#botonCrearRuta').click(function () {
        if ($('#controlesRuta').length == 0) {
            iniciarCrearRuta();

            cerrarMenuMarcaRuta();
            quitarSuperposiciones();

            //Ocultar controles
            $('#botonesIzq,#botonesDer,.menuTira').hide();

            var ventana = $(
                    '<ul id="menuRuta" class="menuTira">' +
                    '<li>' +
                    '<p><span id="cantidadPuntos">' + rutaInterfaz.cantidad() + '</span>/10</p>' +
                    '<p>' + textos['puntosSeleccionados'] + '</p>' +
                    '</li>' +
                    '<li>' +
                    '<span class="barraVertical"></span>' +
                    '</li>' +
                    '<li>' +
                    '<img id="botonRetrocederRuta" src="' + base_url_img + 'img/retroceder-icon.png" alt="' + textos['retrocederRuta'] + '"/>' +
                    '<p>' + textos['retrocederRuta'] + '</p>' +
                    '</li>' +
                    '<li>' +
                    '<img id="botonFinalizarRuta" src="' + base_url_img + 'img/finalizar-icon.png" alt="' + textos['aceptarRuta'] + '"/>' +
                    '<p>' + textos['aceptarRuta'] + '</p>' +
                    '</li>' +
                    '<li>' +
                    '<img id="botonCancelarRuta" src="' + base_url_img + 'img/cancelar-icon.png" alt="' + textos['cancelarRuta'] + '"/>' +
                    '<p>' + textos['cancelarRuta'] + '</p>' +
                    '</li>' +
                    '</ul>');
            $('#contenedorMapa').append(ventana);

            $('#botonCancelarRuta').click(terminarMarcadoRuta);
            $('#botonRetrocederRuta').click(function () {
                rutaInterfaz.eliminarMarca(espera, finEspera);
                $('#cantidadPuntos').html(rutaInterfaz.cantidad());
            });
            $('#botonFinalizarRuta').click(function () {
                tellmee_Servidor.guardarRuta(rutaInterfaz.idsRuta(), function (datos) {
                    if (datos.ok) {
                        alert(textos['rutaGuardada']);
                    }
                    else {
                        alert(textos['error_desconocido']);
                    }

                    terminarMarcadoRuta();
                });
            });
        }
    });
}

function cerrarMenuMarcaRuta() {
    $('.ventanaTipoCreacion').remove();
}

function cerrarInfoAudioAleatorio(previa) {
    $('#ventanaInicio').remove();

    quitarSuperposiciones();

    if (!movil && previa) {
        var previa = new VistaPrevia(tellmee_Servidor, tellmee_Google, 9, nombreUsuario);
        previa.actualizarVistaPrevia();
        tellmee_Google.anhadirEvento('tilesloaded', function () {
            previa.actualizarVistaPrevia();
        });

        $('#slider').remove();
        $('audio').remove();
    }
    
    //Añado dinamicamente botón ayuda lo indexo y genero su comportamiento
    if($('#help').length == 0) {
        var ayuda = $('<img id="help" src="' + base_url_img + 'img/icono-ayuda.png" alt="' + textos['ayuda'] + '"/>');
        $('#cabecera').append(ayuda);
    
        $('#help').click(mostrarAyuda);
    }
}

function actualizarVisualizacionCategorias() {
    $('#categorias img').not('#ruta_categoria').each(function () {
        var id = parseInt($(this).attr('id'));

        if (enArray(listaCat, id)) {
            $(this).attr('src', base_url_img + "img/categorias/" + id + ".png");
        }
        else {
            $(this).attr('src', base_url_img + "img/categorias/" + id + "b.png");
        }
    });
}

function ajustarMapa() {
    //Alto del mapa
    $('#contenedorMapa').css('height', $('body').outerHeight() - $('#cabecera').outerHeight() + 'px');
}

//Solicita y agrega la zona de las marcas sin ella
function agregarZonas(marcadores) {
    var valores = [];

    var valores = $.map(marcadores, function (value, index) {
        return value;
    });

    var valores2 = $.map(valores, function (value, index) {
        return value;
    });

    agregarZonaInterna(valores2, 0);
}

function agregarZonaInterna(marcas, indice) {
    if (marcas[indice] == undefined) {
        return;
    }

    if (marcas[indice].idArea == '' || marcas[indice].nombreArea == '') {
        //Solicitamos el area
        tellmee_Google.identificarArea(marcas[indice].la, marcas[indice].lo, function (area) {
            if (area) {
                tellmee_Servidor.enlazarArea(marcas[indice].id, area[0], area[1], area[2], area[3], area[4], area[5], function (resultado) {
                    console.log(resultado);

                    agregarZonaInterna(marcas, indice + 1);
                });
            }
            else {
                console.log('No obtuvo área');

                agregarZonaInterna(marcas, indice + 1);
            }
        });
    }
    else {
        console.log('Ya hecho');

        agregarZonaInterna(marcas, indice + 1);
    }
}

/**
 * Muestra la vista de lista.
 */
function abrirVistaLista() {
    $('#botonMarca').hide();
    $('#botonMarcaDeslogueado').hide();
    $('#tipoMapa').hide();

    vistaLista = new VistaLista(tellmee_Servidor, tellmee_Google, function () {
        //Abrir un audio
        var marcador = tellmee_Google.capas['marcadores'].obtenerMarcador(parseInt($(this).attr('id')));

        if (marcador.marca)
            crearInfoMarca(marcador);
        else
            crearInfoAudio(marcador);
    }, base_url_img + 'img/cargando.gif');
    vistaLista.actualizarVistaLista();
}

/**
 * Cierra la vista de lista.
 */
function cerrarVistaLista() {
    $('#vistaLista').remove();
    $('.superposicionBlanca').remove();
    vistaLista = null;

    $('#botonMarca').show();
    $('#botonMarcaDeslogueado').show();
    $('#tipoMapa').show();
}

//Activa una categoría y actualiza la visualización y la configuración
function activarCategoria(idCategoria, noRemezclar) {
    if (!enArray(listaCat, idCategoria)) {
        listaCat.push(idCategoria);
    }

    //Actualizar
    if (noRemezclar == undefined) {
        mezclarCategorias();
    }
    actualizarVisualizacionCategorias();
    tellmee_Servidor.modificarCategorias();
}

//Desactiva una categoría y actualiza la visualización y la configuración
function desactivarCategoria(idCategoria, noRemezclar) {
    var indice = indiceDe(listaCat, idCategoria);
    if (indice >= 0) {
        listaCat.splice(indice, 1);

        //Actualizar
        if (noRemezclar == undefined) {
            mezclarCategorias();
        }
        actualizarVisualizacionCategorias();
        tellmee_Servidor.modificarCategorias();
    }
}

//Pausa todos los audios excepto el reción pulsado
function detenerOtrosAudios(nuevo) {
    $('.play').not(nuevo).filter(function () {
        return $(this).attr('src').indexOf('pause') !== -1;
    }).click();
}

function iniciar() {
    ajustarMapa();
    $(window).resize(ajustarMapa);

    //Categorías iniciales
    var cats = decodeURIComponent(getCookie('categoriasAudio'));
    if (cats !== undefined && cats !== '') {
        try {
            listaCat = arrayUnique($.parseJSON(cats));
        }
        catch (e) {
            listaCat = [1, 2, 3, 5, 6, 7];
        }
    }
    else {
        listaCat = [1, 2, 3, 5, 6, 7];
    }
    actualizarVisualizacionCategorias();

    //Cambios en las categorías
    $('#categorias img').click(function () {
        //Modificar categorías
        var id = parseInt($(this).attr('id'));
        var ruta = $(this).attr('src');
        if (ruta.indexOf('b') === -1) {
            //Está activado
            desactivarCategoria(id);
        }
        else {
            if (id) {
                activarCategoria(id);
            }
        }

        if (vistaLista) {
            vistaLista.actualizarVistaLista();
        }
    });

    //Ocultar/mostrar las rutas
    $('#ruta_categoria').click(function () {
        if (getCookie('rutasActivas') == 1) {
            $(this).attr('src', base_url_img + "img/categorias/rutab.png");
            setCookie('rutasActivas', 0, 300);
            tellmee_Google.ocultarCapa('rutas');
        }
        else {
            $(this).attr('src', base_url_img + "img/categorias/ruta.png");
            setCookie('rutasActivas', 1, 300);
            tellmee_Google.mostrarCapa('rutas');
        }
    });
    if (getCookie('rutasActivas') == 1) {
        $('#ruta_categoria').attr('src', base_url_img + "img/categorias/ruta.png");
    }
    else {
        $('#ruta_categoria').attr('src', base_url_img + "img/categorias/rutab.png");
    }

    //Abrimos el menú buscar
    $('.busqueda').focusin(function () {
        abrirSelectorTipoBusqueda(function () {
            var limites = tellmee_Google.getLimitesVisibles();
            //Busca una marca
            tellmee_Servidor.buscarMarca($('.busqueda').val(), function (datos) {
                if (datos.ok) {
                    idLocalizado = datos.id;

                    centrarMapa();

                    if (vistaLista) {
                        vistaLista.actualizarVistaLista();
                    }
                }
                else {
                    alert(textos['nadaEncontrado']);
                }
            }, limites.latSupDer, limites.lonSupDer, limites.latInfIzq, limites.lonInfIzq);
            cerrarSelectorTipoBusqueda();
            cerrarInfoAudioAleatorio(true);
        }, function () {
            //Busca una dirección
            tellmee_Google.localizarDireccion($('.busqueda').val(), base_url_img +
                    'img/circulo/circulo.1.png', textos['nadaEncontrado'], zoomLocalizado, function () {
                if (vistaLista) {
                    vistaLista.actualizarVistaLista();
                }
            });
            cerrarSelectorTipoBusqueda();
            cerrarInfoAudioAleatorio(true);
        });
    });
    $('.busqueda').focusout(cerrarSelectorTipoBusqueda);
    //Texto de ayuda de la búsqueda
    $('.busqueda').val(textos['busqueda']);
    $('.busqueda').focusin(entraInput);
    $('.busqueda').focusout(saleInput);

    //Quiere poner una marca o una ruta sin estar logueado
    $('#botonMarcaDeslogueado').click(function () {
        abrirEmergente(textos['mensaje'], '<p>' + textos['marcaSinLogueo'] + '</p><br/><p><a href="' + base_url + 'index.php/acceso">' +
                textos['enlaceRegistro'] + '</a></p>', '');
    });

    //Para poner una marca o una ruta
    $('#botonMarca').click(function () {
        if (!$('.ventanaTipoCreacion').html()) {
            superposicionNegra(true, cerrarMenuMarcaRuta);
            abrirMenuMarcaRuta();
        }
    });

    //Para intercambiar entre fotos y tipo categoría
    $('#botonIntercambiar').click(function () {
        fotoPuntos = !fotoPuntos;
        
        if(fotoPuntos) {
            $(this).attr('src', base_url_img + 'img/photo-icon.png');
        }
        else {
            $(this).attr('src', base_url_img + 'img/icon-photo.png');
        }
        
        mezclarCategorias();
        
        if(vistaLista) {
            vistaLista.actualizarVistaLista();
        }
    });

    //Repetir geolocalización
    $('#localizarse').click(function () {
        geoLocalizar(null, null);
    });

    //Centramos el mapa en una id
    var id = getQuerystring('id', NaN);
    if (!isNaN(id)) {
        //Impedimos que se habra la búsqueda aleatoria
        aleatorio = true;

        tellmee_Servidor.localizarAudio(id, function (data) {
            if (data.ok) {
                //Cambiamos de idioma de ser necesario
                if (data.idioma) {
                    modificarIdiomas(parseInt(data.idioma), function () {
                        //Hay que esperar un poco o no llega a cambiarse la cookie
                        setTimeout(function () {
                            location.reload();
                        }, 500);
                    });
                }

                //También activamos la categoría si no lo está
                activarCategoria(parseInt(data.categoria), true);

                //Obtenemos el id corregido si fuese necesario
                idLocalizado = data.id;

                continuar(data);
            }
            else {
                continuar(null);
            }
        }, function () {
            continuar(null);
        });
    }
    else {
        //Centramos el mapa con coordenadas en la url
        var lat = getQuerystring('lat', NaN);
        var lon = getQuerystring('lon', NaN);

        if (!isNaN(lat) && !isNaN(lon)) {
            //Impedimos que se habra la búsqueda aleatoria
            aleatorio = true;

            continuar({
                coords: {
                    latitude: lat,
                    longitude: lon
                }
            });
        }
        else {
            //Centramos en una dirección buscada si se encuentra
            direccion = getQuerystring('dire', undefined);
            if (direccion !== undefined) {
                //Impedimos que se habra la búsqueda aleatoria
                aleatorio = true;
            }

            continuar(null);
        }
    }
}
