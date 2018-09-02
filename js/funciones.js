var cadenaConexion='http://localhost/panoraudio/index.php/ajax/';
var base_url='http://localhost/panoraudio/';
var base_url_img='http://localhost/panoraudio/';
var base_url_audio='http://localhost/panoraudio/';
//Aquí se va a guardar temporalmente si el texto del input es del usuario
var ayuda=new Array();
//Y aquí las categorías que están activadas o desactivadas
var listaCat=new Array();
//Para saber si se ha pulsado el botón de FB
var fbPulsado = false;
//Idiomas de la plataforma
var idiomasPlataforma = {};
idiomasPlataforma[1] = 'Castellano';
//idiomasPlataforma[2] = 'Català';
//idiomasPlataforma[3] = 'Deutsch';
idiomasPlataforma[4] = 'English';
idiomasPlataforma[6] = 'Français';
idiomasPlataforma[7] = 'Galego';
//idiomasPlataforma[8] = 'Português';
//idiomasPlataforma[9] = 'Italiano';
//idiomasPlataforma[10] = '日本語';
//idiomasPlataforma[11] = '中國';

//Busca un elemento en un array
function enArray(array, valor) {
    var resultado = false;
    
    for(var x = 0;x < array.length;x++) {
        if(array[x] == valor) {
            resultado = true;
            break;
        }
    }
    
    return resultado;
}

//Devuelve un array formado por las marcas comunes (Los elementos deben ser únicos en cada array)
function interseccion(array1, array2) {
    var arrayTemp = array1.concat(array2), contador = new Object(), resultado = new Array();
    
    for(var x = 0; x < arrayTemp.length; x++) {
        if(isNaN(contador[arrayTemp[x].marca.id]))
            contador[arrayTemp[x].marca.id] = 1;
        else
            contador[arrayTemp[x].marca.id]++;
        if(contador[arrayTemp[x].marca.id] >= 2)
            resultado.push(arrayTemp[x].marca);
    }
    
    return resultado;
}

//Busca el primer índice para ese valor o el siguiente más próximo sin sobrepasar limite (Deben estar ordenados de menor a mayor)
function primerIndiceDe(array, valor, limite, propiedad) {
    var lon = array.length, resultado = -1;
    
    for(var x = 0; x < lon; x++) {
        if(array[x][propiedad] >= valor) {
            if(array[x][propiedad] <= limite)
                resultado = x;
            break;
        }
    }
    
    return resultado;
}

//Busca el último índice para ese valor o el anterior más próximo sin sobrepasar limite (Deben estar ordenados de menor a mayor)
function ultimoIndiceDe(array, valor, limite, propiedad) {
    var lon = array.length, resultado = -1;
    
    for(var x = lon - 1; x >= 0; x--) {
        if(array[x][propiedad] <= valor) {
            if(array[x][propiedad] >= limite)
                resultado = x;
            break;
        }
    }
    
    return resultado;
}

//Busca el primer índice de un valor concreto
function indiceDe(array, valor) {
    var lon = array.length, resultado = -1;
    
    for(var x = 0; x < lon; x++) {
        if(array[x] == valor) {
            resultado = x;
            break;
        }
    }
    
    return resultado;
}

//Busca el primer índice de un texto concreto
function indiceDeParcial(array, texto) {
    var lon = array.length, resultado = -1;
    
    for(var x = 0; x < lon; x++) {
        if(array[x].indexOf(texto) != -1) {
            resultado = x;
            break;
        }
    }
    
    return resultado;
}

//Realiza una copia de un array
function copiarArray(array) {
    var copia=new Array();
    
    for(var x = 0;x < array.length;x++)
        copia[x]=array[x];
    
    return copia;
}

//Elimina los valores repetidos de un array
function arrayUnique(array) {
    var temp = new Object();
    
    for (var x = 0, len = array.length; x < len; x++) {
        temp[array[x]] = 0;
    }
    
    return Object.keys(temp);
}

//Coge datos de la url
function getQuerystring(key, default_) {
    key = key.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regex = new RegExp("[\\?&]" + key + "=([^&#]*)");
    var qs = regex.exec(window.location.href);
    if (qs == null)
        return default_;
    else
        return qs[1];
}

/**
 * Desordena una lista y devuelve la cantidad solicitada.
 * @param {Array} array
 * @param {int} cantidad 0 para todos.
 * @returns {Array}
 */
function desordenarLista(array, cantidad) {
    var lote = [];
    
    if(cantidad <= 0 || cantidad > array.length) {
        cantidad = array.length;
    }
    
    for (var i = 0; i < cantidad; i++) {
        // Generar un nuevo elemento.
        var nuevo = array[Math.floor((Math.random() * array.length))];

        // Si el elemento no se encuentra en lote[] agregar (push), en caso
        // de que sea se encuentre (continue;), saltar al siguente.
        if (lote.indexOf(nuevo) != -1) {
            continue;
        }
        else {
            lote.push(nuevo);
        }
    }
    
    return lote;
}

//Indica si el navegador es el internet explorer
function esIE() {
    return (navigator.userAgent.toLowerCase().indexOf('trident') !== -1);
}

//Entra en un input
function entraInput(event) {
    var elemento = $(event.currentTarget);
    if(ayuda[elemento.attr('name')] == undefined || ayuda[elemento.attr('name')] !== elemento.val()) {
        //Es el texto explicativo, lo borramos
        elemento.val('');
        //Cambiamos el color a negro
        elemento.css('color', '#4C4C4C');
        //Lo convertimos en password si es necesario
        if(/pass|pass2|nuevoPass/.test(elemento.attr('class'))) {
            //Colocamos el nuevo
            var nuevo = $(elemento.clone().wrap('<p>').parent().html().replace('type="text"', 'type="password"').replace(/value="[^"]+"/, ''));
            elemento.after(nuevo);
            //Borramos el antiguo
            elemento.remove();
            //Le damos el foco al nuevo
            nuevo.focus();
            //Recuperamos los eventos
            nuevo.focusin(entraInput);
            nuevo.focusout(saleInput);
        }
    }
}

//Sale de un input
function saleInput(event) {
    var elemento = $(event.currentTarget);
    if(elemento.val() == '') {
        //Está vacío, volvemos el color a gris
        elemento.css('color', '#9D9D9D');
        //Ponemos el texto explicativo
        var clases=elemento.attr('class');
        clases=clases.split(' ');
        for(var x in clases) {
            if(textos[clases[x]]) {
                elemento.val(textos[clases[x]]);
                break;
            }
        }
        //Lo convertimos en text
        if(/pass|pass2|nuevoPass/.test(elemento.attr('class'))) {
            //Colocamos el nuevo
            var nuevo = $(elemento.clone().wrap('<p>').parent().html().replace('type="password"', 'type="text"').replace(/value="[^"]+"/, ''));
            elemento.after(nuevo);
            //Recuperamos el valor
            nuevo.val(elemento.val());
            //Borramos el antiguo
            elemento.remove();
            //Recuperamos los eventos
            nuevo.focusin(entraInput);
            nuevo.focusout(saleInput);
        }
        //Lo desanotamos
        ayuda[elemento.attr('name')] = undefined;
    }
    else {
        //Ha escrito algo, lo anotamos
        ayuda[elemento.attr('name')] = elemento.val();
    }
}

function espera() {
    $.blockUI({
        message: "<img width='100' border='0' src='" + base_url_img + "img/loading130.gif'/>",
        css: { 
            width: '100px', 
            top: ($(window).height()-100)/2 + 'px',
            left: ($(window).width()-100)/2 + 'px' 
        }
    });
}

function finEspera() {
    $.unblockUI();
}

//Le pregunta al servidor si estamos logueados con Facebook
function usoFacebook(callback) {
    $.getJSON(this.cadenaConexion + 'usoFacebook?callback=?', callback).
    error(function(data, texto, http) {});
}

//Modificamos los idiomas
function modificarIdiomas(idIdioma, callback) {
    if(idIdioma) {
        $.getJSON(this.cadenaConexion + 'modificarIdiomasPreferidos/' + idIdioma +'?callback=?', callback).
        error(function(data, texto, http) {});
    }
}

//Devuelve la url para conseguir una imagen del street view en la función callback y si la imagen es seguro que existe
function obtenerImagenStreet(width, height, lat, lon, callback) {
    //Puede que no esté cargado el api de Google maps en algunas ocasiones
    if(google != undefined) {
        var panorama = new google.maps.StreetViewService();
        panorama.getPanoramaByLocation(new google.maps.LatLng(lat, lon), 
            40, function(StreetViewPanoramaData, StreetViewStatus) {
                if(StreetViewStatus == google.maps.StreetViewStatus.OK)
                    callback("http://maps.googleapis.com/maps/api/streetview?size=" + width + "x" + height + "&location=" + lat + "," + lon + "&sensor=true", true);
                else
                    callback(base_url_img + "img/marco-foto-vacio-63.png", false);
            });
    }
    else
        callback(base_url_img + "img/marco-foto-vacio-63.png", false);
}

//Cambia el tamaño de las imágenes según el tamaño del documento
function ajustarImagenes(selectorJQuery) {//TODO: limitar el cambio
    var objetivo = $(selectorJQuery), 
    ratio = Math.min($(document).height()/587, $(document).width()/1024);
    
    objetivo.width(objetivo.width()*ratio);
}

//Abrir una ventana superpuesta
function abrirDialogo(contenido, idFondo) {
    //Abrimos el menú
    $.blockUI({
        message: contenido,
        css: {
            width: '60%',
            top: '2%',
            left: '20%',
            cursor: 'default'
        }
    });
    //Evento para cerrar el menú
    $('.blockOverlay').click(function() {
        $.unblockUI();
    }); 
    //Configuramos su imagen de fondo
    if(idFondo != '' && localStorage.localizacionUsuario != undefined) {
        var localizacionUsuario = $.parseJSON(localStorage.localizacionUsuario);
        obtenerImagenStreet(800, 800, 
            localizacionUsuario.coords.latitude, localizacionUsuario.coords.longitude, 
            function(ruta) {
                $(idFondo).attr('src', ruta);
                $(idFondo).show();
            });
    }
}

function validarEmail(email) {
    if(email.match(/[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/)) return true;
    else return false;
}

function textoAyudaRegistroLogin() {
    //Texto informativo
    $('input[type="text"],input[type="password"]').focusin(entraInput);
    $('input[type="text"],input[type="password"]').focusout(saleInput);
                
    //Forzamos a que salga del input al pulsar enter
    $('input[type="text"],input[type="password"]').keydown(function(event) {
        if(event.which == 13) {
            saleInput(event.currentTarget);
        }
    });
}

function setCookie(cname,cvalue,exdays) {
    var d = new Date();
    d.setTime(d.getTime()+(exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
} 

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++)
    {
        var c = ca[i].trim();
        if (c.indexOf(name)==0) return c.substring(name.length,c.length);
    }
    return "";
} 

/**
 * Crea y muestra el menú lateral.
 */
function crearMenuLateral() {
    var idiomaActual = getCookie('idiomaAudio');
    var options = '';
    for(var x in idiomasPlataforma) {
        options += '<option value="' + x + '">' + idiomasPlataforma[x] + '</option>';
    }
    
    $('#contenedorMapa').append(
            '<div class="menuLateral">' + 
                '<div>' + 
                    '<p><img src="' + base_url_img + 'img/LOGO-panoraudio-32.png" alt="Panoraudio"/></p>' + 
                    '<p>' + textos['idiomaAudio'] + '</p>' +
                    '<select class="select">' + 
                        options +
                    '</select>' + 
                '</div>' + 
            '<div>' + 
            '<p><a href="http://web.panoraudio.com" target="_blank"><img src="' + base_url_img + 'img/marca-unitaria-icon.png" alt="' + textos['acercaDe'] + '"/>' + textos['acercaDe'] + '</a></p>' + 
            '<p><a href="mailto:contact@panoraudio.com"><img src="' + base_url_img + 'img/mail-icon-hover-53.png" alt="' + textos['contacto'] +'"/>' + textos['contacto'] +'</a></p>' + 
        '</div>' + 
        '<div>' + 
            '<p>' + textos['sigue'] + '</p>' + 
            '<a href="https://twitter.com/panoraudio" target="_blank"><img src="' + base_url_img + 'img/twitter-icon.png" alt="Twitter"/></a><a href="https://www.facebook.com/pages/Panoraudiocom/457862144254026" target="_blank"><img src="' + base_url_img + 'img/facebook-icon.png" alt="Facebook"/></a>' + 
        '</div>' + 
    '</div>');
    $('.menuLateral select').val(idiomaActual);
    $('.menuLateral').animate({left: 0}, 500);
    $('.menuLateral select').change(function() {
        modificarIdiomas(parseInt($(this).val()), function() {
            location.reload();
        });
    });
    
}

/**
 * Oculta y elimina el menú lateral.
 */
function eliminarMenuLateral() {
    $('.menuLateral').animate({left: '-290px'}, 500, function() {
        $('.menuLateral').remove();
    });
}

/**
 * Muestra el selector de tipo de búsqueda.
 * @param {function} eventoBuscarMarca
 * @param {function} eventoBuscarDireccion
 */
function abrirSelectorTipoBusqueda(eventoBuscarMarca, eventoBuscarDireccion) {
    //Colocamos el selector
    var buscador = $('.busqueda');
    var posicion = buscador.position();
    var opciones = $('<div class="opcionesBusqueda"></div>');
    
    opciones.css('position', 'absolute');
    opciones.css('top', posicion.top + buscador.outerHeight(false) + 2);
    opciones.css('left', posicion.left);
    
    var opcion1 = $('<p>' + textos['buscaMarca'] + '</p>');
    opcion1.click(eventoBuscarMarca);
    opciones.append(opcion1);
    var opcion2 = $('<p>' + textos['buscaDireccion'] + '</p>');
    opcion2.click(eventoBuscarDireccion);
    opciones.append(opcion2);

    buscador.after(opciones);
}

/**
 * Quita el selector de tipo de búsqueda.
 */
function cerrarSelectorTipoBusqueda() {
    setTimeout(function() {
        $('.opcionesBusqueda').remove();
    }, 300);
}

/**
 * Muestra el menú de usuario.
 */
function abrirMenuPerfil() {
    if(esIE()) {
        var clase = '';
        var tipo = 'password';
    }
    else {
        var clase = 'nuevoPass';
        var tipo = 'text';
    }
    
    $('#contenedorMapa').append(
        '<div class="ventanaUsuario">' +
            '<div>' +
                '<p>' + textos['hola'] + ' <span class="azul">' + nombreUsuario +'</span> <img id="borrarCuenta" src="' + base_url_img + 'img/perfil-delete.png" alt="' + textos['borrarCuenta'] + '" title="' + textos['borrarCuenta'] + '"/></p>' +
            '</div>'+
            '<div>'+
                '<p>' + textos['cambiarPass'] + ':</p>'+
                '<p><input class="inputText bordeAzul ' + clase + '" type="' + tipo + '" name="nuevoPass" value="' + textos['nuevoPass'] + '"/></p>'+
                '<p><span id="cambiarPass" class="boton">' + textos['cambiarPass'] + '</span></p>'+
            '</div>'+
            '<div>'+
                '<p id="botonSalir">' + textos['cerrarSesion'] + '</p>'+
            '</div>'+
        '</div>');

    if(!esIE()) {
        $('.nuevoPass').focusin(entraInput);
        $('.nuevoPass').focusout(saleInput);
    }

    $('#cambiarPass').click(function() {
        //Validamos la recuperación
        //Todos los campos son obligatorios
        if(ayuda['nuevoPass'] == undefined || $.trim($('input[name="nuevoPass"]').val()) == '') {
            alert(textos['mensaje_faltan_campos']);
        }
        else { 
            tellmee_Servidor.cambiarPass(function(respuesta) {
                $('.ventanaUsuario').remove();
                abrirEmergente('Mensaje', respuesta.mensaje, true); 
            }, $.trim($('input[name="nuevoPass"]').val()));
        }
    });
    $('#botonSalir').click(cerrarSesion);
    $('#borrarCuenta').click(function() {
        if(confirm(textos['confirmaBorrarCuenta'])) {
            location.assign(base_url + "index.php/acceso/eliminarCuenta");
        }
    });
}

/**
 * Intenta cerra la sesión.
 * @returns {undefined}
 */
function cerrarSesion() {
    //Intenta desloguearse
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            FB.logout(function(response) {
                location.assign(base_url + "index.php/acceso/salir");
            });
        }
        else {
            location.assign(base_url + "index.php/acceso/salir");
        }
    });

    return false;
}

/**
 * Elimina el menú de usuario.
 */
function cerrarMenuPerfil() {
    $('.ventanaUsuario').remove();
}

/**
 * Realiza el proceso de login con facebook.
 * @param {string} selector
 */
function logueoFacebook(selector) {
    $(selector).click(function() {
        FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {                                       
                //Avisamos al servidor
                location.assign(base_url + "index.php/acceso/login/conFacebook");
            }
            else {
                fbPulsado = true;

                FB.login(function(response) {}, {
                    scope: 'basic_info,email'
                }); 
            }
        });

        return false;
    });
}

/**
 * Abre una ventana de login.
 */
function abrirVentanaAcceso() {
    if(esIE()) {
        var clase = '';
        var tipo = 'password';
    }
    else {
        var clase = 'pass';
        var tipo = 'text';
    }
    
    var ventana = $(
        '<div class="ventanaFormulario">' +
            '<img src="' + base_url_img + 'img/LOGO-panoraudio-32.png" alt="Panoraudio"/>' +
            '<h1>' + textos['iniciarSesion'] + '</h1>' +
            '<form name="normalLogin" method="post" action="' + base_url + 'index.php/acceso/login">' +
                '<input type="hidden" name="logueo" value="1"/>' +
                '<input class="inputText emailUsuario" type="text" name="entrar_email" value="' + textos['emailUsuario'] + '"/>' +
                '<input class="inputText bordeAzul ' + clase + '" type="' + tipo + '" name="entrar_pass" value="' + textos['pass'] + '"/>' +
                '<div id="controlesFormu">' +
                    '<div>' +
                        '<p id="botonCancelar">' + textos['cancelar'] + '</p>' +
                        '<p><label><input class="checkbox" type="checkbox" name="recordar" value="1"/>' + textos['recordar'] + '</label></p>' +
                    '</div>' +
                    '<div>' +
                        '<p id="botonAcceder">' + textos['acceder'] + '</p>' +
                        '<p id="recuperarEnlace">' + textos['olvidada'] + '</p>' +
                    '</div>' +
                    '<div style="float:none;clear:both"></div>' +
                '</div>' +
            '</form>' + 
            '<span id="botonVentanaRegistro" class="boton">' + textos['registrame'] + '</span>' +
            '<p id="fraseFacebook">' + textos['accedeFacebook'] + '</p>' +
            '<img id="loginFacebook" src="' + base_url_img + 'img/facebook-login-icon-55.png" alt="Facebook"/>' +
        '</div>');

    centrarVentana(ventana, ventana);
    $('#contenedorMapa').append(ventana);

    textoAyudaRegistroLogin();
    
    $('#botonCancelar').click(function() {
        cerrarVentanaAcceso();
        quitarSuperposiciones();
    });
    
    //Quiere registrarse
    $('#botonVentanaRegistro').click(function() {
        cerrarVentanaAcceso();
        abrirVentanaRegistro();
    });
    
    //Botón de Facebook
    logueoFacebook('#loginFacebook');

    //Intenta loguearse
    $('#botonAcceder').click(function() {
        //Validamos el login
        //Todos los campos son obligatorios
        if(ayuda['entrar_email'] == undefined || $.trim($('input[name="entrar_email"]').val()) == '' ||
            ayuda['entrar_pass'] == undefined || $.trim($('input[name="entrar_pass"]').val()) == '') {
            alert(textos['mensaje_faltan_campos']);
        }
        else { 
            //La contraseña debe contener al menos 5 caracteres
            if($('input[name="entrar_pass"]').val().length < 5) {
                alert(textos['mensaje_longitud']);
            }
            else {
                //Correcto
                $('form[name="normalLogin"]').submit();
            }
        }
    });

    //Quiere recuperar la contraseña
    $('#recuperarEnlace').click(function() {
        $('.ventanaFormulario').empty();
        $('.ventanaFormulario').append(
            '<img src="' + base_url_img + 'img/LOGO-panoraudio-32.png" alt="Panoraudio"/>' +
            '<h1>' + textos['recuperarPass'] + '</h1>' +
            '<form name="normalLogin" method="post" action="' + base_url + 'index.php/acceso/login">' +
                '<input class="inputText email" type="text" name="entrar_email" value="' + textos['email'] + '"/>' +
                '<span id="botonRecuperar" class="boton">' + textos['recuperarPass'] + '</span>' +
            '</form>');
    
        textoAyudaRegistroLogin();
        
        $('#botonRecuperar').click(function() {
            //Validamos la recuperación
            //Todos los campos son obligatorios
            if(ayuda['entrar_email'] == undefined || $.trim($('input[name="entrar_email"]').val()) == '') {
                alert(textos['mensaje_faltan_campos']);
            }
            else { 
                tellmee_Servidor.solicitarRecuperar(function(respuesta) {
                    if(respuesta.ok) {
                        cerrarVentanaAcceso();
                        abrirEmergente('Mensaje', respuesta.menu);
                    }
                }, $.trim($('input[name="entrar_email"]').val()));
            }
        });
    });
}

/**
 * Cierra la ventana de login.
 */
function cerrarVentanaAcceso() {
    $('.ventanaFormulario').remove();
}

/**
 * Abre una ventana de registro.
 */
function abrirVentanaRegistro() {
    if(esIE()) {
        var clase1 = '';
        var clase2 = '';
        var tipo = 'password';
    }
    else {
        var clase1 = 'pass';
        var clase2 = 'pass';
        var tipo = 'text';
    }
    
    var ventana = $(
        '<div class="ventanaFormulario">' +
            '<img src="' + base_url_img + 'img/LOGO-panoraudio-32.png" alt="Panoraudio"/>' +
            '<form name="normalRegistro" method="post" action="' + base_url + 'index.php/acceso/registro">' +
                '<input class="inputText usuario" type="text" name="usuario" value="' + textos['usuario'] + '"/>' +
                '<input class="inputText email" type="text" name="email" value="' + textos['email'] + '"/>' +
                '<input class="inputText bordeAzul ' + clase1 + '" type="' + tipo + '" name="pass" value="' + textos['pass'] + '"/>' +
                '<input class="inputText bordeAzul ' + clase2 + '" type="' + tipo + '" name="pass2" value="' + textos['pass2'] + '"/>' +
                '<div id="controlesFormu">' +
                    '<div>' +
                        '<p id="botonCancelarRegistro">' + textos['cancelar'] + '</p>' +
                    '</div>' +
                    '<div>' +
                        '<p id="botonRegistro">' + textos['registrarse'] + '</p>' +
                    '</div>' +
                    '<div style="float:none;clear:both"></div>' +
                '</div>' +
            '</form>' +
            '<p id="fraseFacebook">' + textos['accedeFacebook'] + '</p>' +
            '<img id="loginFacebook" src="' + base_url_img + 'img/facebook-login-icon-55.png" alt="Facebook"/>' +
        '</div>');
    
    centrarVentana(ventana, ventana);
    
    $('#contenedorMapa').append(ventana);

    textoAyudaRegistroLogin();
    
    //Cancelar
    $('#botonCancelarRegistro').click(function() {
        cerrarVentanaRegistro();
        quitarSuperposiciones();
    });
    
    //Registrarse
    $('#botonRegistro').click(function() {
        //Validamos el registro
        //Todos los campos son obligatorios
        if(ayuda['email'] == undefined || $.trim($('input[name="email"]').val()) == '' || 
                ayuda['pass'] == undefined || $.trim($('input[name="pass"]').val()) == '' || 
                ayuda['pass2'] == undefined || $.trim($('input[name="pass2"]').val()) == '' || 
                ayuda['usuario'] == undefined || $.trim($('input[name="usuario"]').val()) == '') {
            alert(textos['mensaje_faltan_campos']);
        }
        else {
            //El email debe ser correcto
            if(!validarEmail($('input[name="email"]').val())) {
                alert(textos['mensaje_revisa_email']);
            }
            else {
                //Las contraseñas deben ser iguales
                if($('input[name="pass"]').val() != $('input[name="pass2"]').val()) {
                    alert(textos['mensaje_pass']);
                }
                else {
                    //La contraseña debe contener al menos 5 caracteres
                    if($('input[name="pass"]').val().length < 5) {
                        alert(textos['mensaje_longitud']);
                    }
                    else {
                        //Correcto
                        $('form[name="normalRegistro"]').submit();
                    }
                }
            }
        }
    });
    
    //Botón de Facebook
    logueoFacebook('#loginFacebook');
}

function cerrarVentanaRegistro() {
    $('.ventanaFormulario').remove();
}

/**
 * Aber una ventana con un mensaje.
 * @param {string} url
 * @param {boolean} dejarSuperposicion
 */
function abrirImagen(url, dejarSuperposicion) {
    var ventana = $(
        '<div class="ventanaImagen">' +
            '<img src="' + base_url_img + 'img/cerrar.png" alt="' + textos['cerrar'] + '" class="cerrar"/>' +
            '<img class="imagenGrande" src="' + url + '" alt=""/>' +
        '</div>');
    $('body').append(ventana);
    $('.ventanaImagen .imagenGrande').load(function() {
        var imagen = $(this);
        var dimensiones = redimensionarImagen(parseFloat(imagen.width()), parseFloat(imagen.height()), 
                parseFloat($(window).width()) * 0.95, parseFloat($(window).height()) * 0.95);
        imagen.css(dimensiones);
        ventana.css(dimensiones);
        ventana.css(centrarImagen(parseFloat(imagen.width()), parseFloat(imagen.height()), 
                parseFloat($(window).width()), parseFloat($(window).height())));
        ventana.css('visibility', 'visible');
    });

    $('.ventanaImagen .cerrar').click(function() {
        cerrarImagen();
        if(dejarSuperposicion === undefined || !dejarSuperposicion) {
            quitarSuperposiciones();
        }
    });
}

/**
 * Devuelve las dimensiones de la imagen para que manteniendo la proporción, quepa en los límites.
 * @param {float} anchoImagen
 * @param {float} altoImagen
 * @param {float} anchoMaximo
 * @param {float} altoMaximo
 * @returns json {height, width}
 */
function redimensionarImagen(anchoImagen, altoImagen, anchoMaximo, altoMaximo) {
    var multiplierW = anchoMaximo / anchoImagen;
    var multiplierH = altoMaximo / altoImagen;
    if (multiplierH < 1 || multiplierW < 1) {
        //Usamos la diferencia menor
        var multiplier = (multiplierH < multiplierW ? multiplierH : multiplierW);

        var newWidth = anchoImagen * multiplier;
        var newHeight = altoImagen * multiplier;
    }
    else {
        //Size is ok
        var newWidth = anchoImagen;
        var newHeight = altoImagen;
    }

    return {height: newHeight, width: newWidth};
}

/**
 * Devuelve las dimensiones de la imagen para que manteniendo la proporción, rellene por completo los límites.
 * @param {float} anchoImagen
 * @param {float} altoImagen
 * @param {float} anchoContenedor
 * @param {float} altoContenedor
 * @returns json {height, width}
 */
function redimensionarImagenCompletar(anchoImagen, altoImagen, anchoContenedor, altoContenedor) {
    var multiplierW = anchoContenedor / anchoImagen;
    var multiplierH = altoContenedor / altoImagen;

    //Usamos la diferencia mayor
    var multiplier = (multiplierH > multiplierW ? multiplierH : multiplierW);

    var newWidth = anchoImagen * multiplier;
    var newHeight = altoImagen * multiplier;

    return {height: newHeight, width: newWidth};
}

/**
 * Devuelve las posiciones para centrar una imagen.
 * @param {float} anchoImagen
 * @param {float} altoImagen
 * @param {float} anchoContenedor
 * @param {float} altoContenedor
 * @returns json {top, left}
 */
function centrarImagen(anchoImagen, altoImagen, anchoContenedor, altoContenedor) {
    return { top: (altoContenedor - altoImagen)/2, left: (anchoContenedor - anchoImagen)/2 };
}

/**
 * Cierra la ventana de imagen.
 */
function cerrarImagen() {
    $('.ventanaImagen').remove();
}

/**
 * Abre una ventana con un mensaje.
 * @param {string} titulo
 * @param {string} mensaje
 * @param {boolean} dejarSuperposicion
 */
function abrirEmergente(titulo, mensaje, dejarSuperposicion) {
    var ventana = $(
        '<div class="ventanaMensaje">' +
            '<img src="' + base_url_img + 'img/cerrar.png" alt="' + textos['cerrar'] + '" class="cerrar"/>' +
            '<h1>' + titulo + '</h1>' +
            '<p>' + mensaje + '</p>' +
            '<p class="puntero">' + textos['deAcuerdo'] + '</p>' +
        '</div>');
    centrarVentana(ventana, ventana);
    $('#contenedorMapa').append(ventana);
    $('.ventanaMensaje .cerrar, .ventanaMensaje .puntero').click(function() {
        cerrarEmergente();
        if(dejarSuperposicion === undefined || !dejarSuperposicion) {
            quitarSuperposiciones();
        }
    });
}

/**
 * Cierra la ventana con el mensaje.
 */
function cerrarEmergente() {
    $('.ventanaMensaje').remove();
}

/**
 * Abre una ventana con banderas.
 */
function abrirVentanaIdiomas() {
    var ventana = $('<div class="padreVentanaIdioma"><img src="' + base_url_img + 'img/cerrar.png" alt="' + textos['cerrar'] + '" class="cerrar"/></div>');
    var ventanaInterna = $(
            '<img src="' + base_url_img + 'img/flechaSuperior.png" alt="" class="flechaSuperior"/>' +
            '<div class="ventanaIdioma">' +
                '<p>Elige tu idioma</p>' +
                '<img id="6_idioma" src="' + base_url_img + 'img/banderas/fr.png" alt="' + idiomasPlataforma[6] + '" title="' + idiomasPlataforma[6] + '"/>' +
                '<img id="1_idioma" src="' + base_url_img + 'img/banderas/es.png" alt="' + idiomasPlataforma[1] + '" title="' + idiomasPlataforma[1] + '"/><img id="4_idioma" src="' + base_url_img + 'img/banderas/en.png" alt="' + idiomasPlataforma[4] + '" title="' + idiomasPlataforma[4] + '"/><img id="7_idioma" src="' + base_url_img + 'img/banderas/gl.png" alt="' + idiomasPlataforma[7] + '" title="' + idiomasPlataforma[7] + '"/>' +
            '</div>');
    ventana.append(ventanaInterna);
    var izq = $('#botonIdioma').offset().left - 55;
    ventana.css('left', (izq > 0 ? izq : 0));
    $('#botonIdioma').after(ventana);
    $('.padreVentanaIdioma .cerrar').click(cerrarVentanaIdiomas);
    $('.ventanaIdioma img').click(function() {
        modificarIdiomas(parseInt($(this).attr('id')), function() {
            location.reload();
        });
    });
}

/**
 * Cierra la ventana con banderas.
 */
function cerrarVentanaIdiomas() {
    $('.padreVentanaIdioma').remove();
}

/**
 * Centra la ventana que se le pasa.
 * @param {JQuery} ventanaAncho La ventana que se usa para medir.
 * @param {JQuery} ventanaCentrar La ventana que se centra.
 */
function centrarVentana(ventanaAncho, ventanaCentrar) {
    var anchoVentana = (parseFloat(ventanaAncho.css('width')) + parseFloat(ventanaAncho.css('padding-left')) + parseFloat(ventanaAncho.css('padding-right')))/2;
    var anchoPantalla = parseFloat($('#contenedorMapa').innerWidth())/2;
    if(!anchoVentana) {
        anchoVentana = 300/2;
    }

    ventanaCentrar.css('left', (anchoPantalla - anchoVentana) + 'px');
}

/**
 * Coloca una superposición blanca.
 * @param {boolean} clickCierra Indica si la superposición se cierra al hacerle click.
 * @param {function} callback Se llama al cerrar la superposición con un click. Es opcional.
 */
function superposicionBlanca(clickCierra, callback) {
    $('#contenedorMapa').append('<div class="superposicion superposicionBlanca"></div>');
    if(clickCierra) {
        $('.superposicionBlanca').click(function() {
            if(callback !== undefined) {
                callback();
            }
            quitarSuperposiciones();
        });
    }
}

/**
 * Coloca una superposición negra.
 * @param {boolean} clickCierra Indica si la superposición se cierra al hacerle click.
 * @param {function} callback Se llama al cerrar la superposición con un click. Es opcional.
 */
function superposicionNegra(clickCierra, callback) {
    $('#contenedorMapa').append('<div class="superposicion superposicionNegra"></div>');
    if(clickCierra) {
        $('.superposicionNegra').click(function() {
            if(callback !== undefined) {
                callback();
            }
            quitarSuperposiciones();
        });
    }
}

function bloquearCabecera() {
    $('#cabecera').append('<div class="superposicion superposicionCabecera"></div>');
}

/**
 * Quita las superposiciones.
 */
function quitarSuperposiciones() {
    $('.superposicionNegra, .superposicionCabecera').remove();
}

function leyCookies() {
    if(!getCookie('leyCookies')) {
        $('body').append("<div id='leyCookies'><p>Al usar este sitio acepta el uso de cookies. <a href='' target='_blank'>Saber más</a> <input type='button' name='aceptar' value='Aceptar'/></p></div>");
        $('#leyCookies a').click(function() {
            abrirDialogo('<div id="leyCookiesInfo"><h2>¿Qué es una cookie?</h2>' +
                '<p>Las cookies son pequeños ficheros de texto que se envían a un navegador y se almacenan en el mismo, con el fin de registrar algunas actividades que hace el usuario en un sitio web. Nuestras cookies o las cookies que usamos de terceros no reconocen a ningún usuario de forma personal, solo el dispositivo mediante el que navega, así, por ejemplo, podemos contabilizar cuantas visitas tiene un sitio web. Gracias a las cookies podrás acceder al sitio web de forma segura en áreas privadas y recordarán tus gustos temporalmente con el fin de mejorar la experiencia web.</p>' + 
                '<h2>¿Qué tipo de cookies usamos?</h2>' +
                '<p>Usamos 4 tipos de cookies:</p>' +
                '<p>Cookies técnicas: Se usan principalmente para poder navegar de forma adecuada por el sitio web y poder acceder a todas las secciones sin problemas.</p>' +
                '<h2>¿Cómo activar o desactivar las cookies?</h2>' +
                '<p>La mayoría de los navegadores permiten las cookies por defecto, pero tú podrás activarlas o desactivarlas según tus preferencias. Depende del navegador que uses.</p>' +
                '<p>¡OJO! Si desactivas las cookies, perderás mucha información de interés y probablemente te impida la correcta visualización y funcionamiento del sitio.</p>' +
                '<p>A continuación os detallamos como desactivar o activar las cookies dependiendo del navegador que utilices:</p>' +
                '<p>Internet Explorer: Pincha en el botón de herramientas que está situado en la parte superior derecha y selecciona opciones de internet. Pincha en la pestaña de privacidad y desde ahí mueva el control deslizante para poder graduar la privacidad a su gusto.</p>' +
                '<p>Firefox: Pincha en la ventana de Firefox situada en la parte superior izquierda y selecciona el panel de privacidad. En historial debe seleccionar la opción Usar una configuración personalizada para el historial. Marca la opción  Aceptar cookies para activarla o déjala desmarcada para no aceptarlas. Firefox también te da la opción de elegir la duración de las cookies. Haz click en aceptar para conservar los cambios.</p>' +
                '<p>Google Chrome: Pincha en el botón de herramientas que está situado en la parte superior derecha, selecciona configuración y pincha en mostrar opciones avanzadas. En privacidad pincha el botón de configuración de contenido y desde ahí podrás configurar la recepción de cookies a tu gusto.</p>' +
                '<p>Safari: Pincha en Safari, ve a preferencias, abre la pestaña privacidad y marca la opción que quieras de la sección bloquear cookies.</p></div>');
        
            return false;
        });
    
        $('#leyCookies input').click(function() {
            setCookie('leyCookies', 1, 99999);
            $('#leyCookies').remove();
        });
    }
}

/**
 * Envía un archivo de manera asíncrona.
 * @param {jquery} formu El formulario con el input file a enviar.
 * @param {function} callback Se le pasa la respuesta como parámetro.
 */
function enviarArchivo(formu, callback) {
    var iframe = $('<iframe></iframe>'), id = 'upload_iframe_' + parseInt(Math.random()*100);
    iframe.hide();
    iframe.attr('id', id);
    iframe.attr('name', id);
    
    $('body').append(iframe);
    
    iframe.load(function() {
        var respuesta = iframe.contents().find('body').html();
        iframe.remove();
        callback(respuesta);
    });

    formu.attr('target', id);
    formu.submit();
}
 

//Para loguearse en Facebook
window.fbAsyncInit = function() {
    FB.init({
        appId      : '1408832206028267',
        status     : true, // check login status
        cookie     : true, // enable cookies to allow the server to access the session
        xfbml      : true  // parse XFBML
    });


    FB.Event.subscribe('auth.authResponseChange', function(response) {
        // Here we specify what we do with the response anytime this event occurs. 
        if (response.status === 'connected') {
            if(fbPulsado) {
                //Avisamos al servidor
                location.assign(base_url + "index.php/acceso/login/conFacebook");
            }
        }
        else {
            if (response.status === 'not_authorized') {
            //No hacemos nada
            } 
            else {
                usoFacebook(function(datos) {
                    if(datos.ok) {
                        //Deslogueamos de la aplicación
                        location.assign(base_url + "index.php/acceso/salir");
                    }
                }); 
            }
        }
        
        fbPulsado = false;
    });
};

// Load the SDK asynchronously
(function(d){
    var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
    if (d.getElementById(id)) {
        return;
    }
    js = d.createElement('script');
    js.id = id;
    js.async = true;
    js.src = "//connect.facebook.net/es_ES/all.js";
    ref.parentNode.insertBefore(js, ref);
}(document));
/////////////////

$(function() {
    //Abrir o cerrar el menú lateral
    $('#botonMenuLateral').click(function() {
        if($('.menuLateral').html()) {
            eliminarMenuLateral();
            quitarSuperposiciones();
            $('#botonMenuLateral').css('z-index', 0);
        }
        else {
            superposicionNegra(true, eliminarMenuLateral);
            bloquearCabecera();
            $('#botonMenuLateral').css('z-index', 999);
            crearMenuLateral();
        }
    });
    
    //Abrir o cerrar el menú de selección login o registro
    $('#botonPerfilDeslogueado').click(function() {
        if($('.ventanaFormulario').html()) {
            cerrarVentanaAcceso();
            quitarSuperposiciones();
            $('#botonPerfilDeslogueado').css('z-index', 0);
        }
        else {
            superposicionNegra(true, cerrarVentanaAcceso);
            bloquearCabecera();
            $('#botonPerfilDeslogueado').css('z-index', 999);
            abrirVentanaAcceso();
        }
    });
    
    //Abrir o cerrar el menú de usuario
    $('#botonPerfil').click(function() {
        if($('.ventanaUsuario').html()) {
            cerrarMenuPerfil();
        }
        else {
            abrirMenuPerfil();
        }
    });
    
    //Pasar al mapa
    $('#botonVistaMapa').click(function() {
        if($('#vistaLista').html()) {
            cerrarVistaLista();
            quitarSuperposiciones();
            $(this).addClass('botonVistaSeleccionado');
            $('#botonVistaLista').removeClass('botonVistaSeleccionado');
        }
    });
    
    //Pasar a vista de lista
    $('#botonVistaLista').click(function() {
        if(!$('#vistaLista').html()) {
            superposicionBlanca(false);
            abrirVistaLista();
            $(this).addClass('botonVistaSeleccionado');
            $('#botonVistaMapa').removeClass('botonVistaSeleccionado');
        }
    });
    
    //Registro escritorio
    $('#botonRegistroVentana').click(function() {
        if($('.ventanaFormulario').html()) {
            cerrarVentanaRegistro();
            quitarSuperposiciones();
            $('#botonRegistroVentana').css('z-index', 0);
        }
        else {
            superposicionNegra(true, cerrarVentanaRegistro);
            bloquearCabecera();
            $('#botonRegistroVentana').css('z-index', 999);
            abrirVentanaRegistro();
        }
    });
    
    //Acceso escritorio
    $('#botonAcceso').click(function() {
        if($('.ventanaFormulario').html()) {
            cerrarVentanaAcceso();
            quitarSuperposiciones();
            $('#botonAcceso').css('z-index', 0);
        }
        else {
            superposicionNegra(true, cerrarVentanaRegistro);
            bloquearCabecera();
            $('#botonAcceso').css('z-index', 999);
            abrirVentanaAcceso();
        }
    });
    
    //Botón idioma escritorio
    $('#botonIdioma').click(function() {
        if(!$('.padreVentanaIdioma').html()) {
            abrirVentanaIdiomas();
        }
        else {
            cerrarVentanaIdiomas();
        }
    });
    
    //Para cerrar sesión
    $('#botonSalir').click(cerrarSesion);

    leyCookies();

    iniciar();
});
