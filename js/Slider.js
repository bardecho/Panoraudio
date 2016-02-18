/**
 * Clase que sirve para crear un slider.
 * @param {string} posicion
 * @param {int} ladoSlider Indica cuantos elementos se cargan a cada lado del elemento central.
 * @param {function} callback
 * @returns {Slider}
 */
function Slider(posicion, ladoSlider, callback) {//TODO: mover hacia la izquierda mueve dos.
    var esto = this, posActual = 0, moviendo = false, slider = $(
            '<div id="contenedorPrincipalSlider">' +
            '   <img src="' + base_url_img + 'img/slider-izq.png" alt=""/>' +
            '   <div id="ventanaSlider">' +
            '       <div id="contenedorSlider">' +
            '       </div>' +
            '   </div>' +
            '   <img src="' + base_url_img + 'img/slider-der.png" alt=""/>' +
            '   <div style="float:none;clear:both"></div>' +
            '</div>'
        );
    
    this.anchoElementos = 0;
    this.marcadores = [];

    /**
     * Redimensiona las imágenes del slider y el contenedor más interno.
     * @param {jquery} imagen Si no se le pasa no se redimensionan imágenes.
     * @returns {Number}
     */
    function redimensionarImagenesYContenedor(imagen) {
        var anchoContenedor = parseFloat($('.imagenElementoSlider').width());
        var altoContenedor = parseFloat($('.imagenElementoSlider').height());

        //Redimensionamos el contenedor más interno
        $(posicion).find('#contenedorSlider').width(esto.marcadores.length * esto.getAnchoElementos() + 'px');
        //Redimensionamos las imágenes
        if(imagen) {
            $(posicion).find(imagen).load(function () {
                var altoImagen = parseFloat($(this).outerHeight(true));
                var anchoImagen = parseFloat($(this).outerWidth(true));

                $(this).css(redimensionarImagenCompletar(anchoImagen, altoImagen,
                        anchoContenedor, altoContenedor));
            });
        }
    };
    
    /**
     * Agrega el reproductor de una marca.
     * @param {object} marca
     */
    function aplicarReproductor(marca) {
        //Quitamos la parte de la extensión (dejando el punto)
        var rutaDividida = marca.ruta.split('.');
        var ruta = marca.ruta.substring(0, marca.ruta.length - rutaDividida[rutaDividida.length - 1].length);
        marca.reproductor = new Reproductor(ruta + 'mp3', ruta + 'ogg', 'playElementoSlider' + marca.pos, 
                base_url_img + 'img/play.png', base_url_img +
                'img/pause.png', base_url_img + 'img/cargando.gif', base_url_img + 'img/error.png', 
                'lineaTiempoElementoSlider' + marca.pos, undefined, 'relojElementoSlider' + marca.pos);
    }
    
    /**
     * Elimina el reproductor de una marca.
     * @param {object} marca
     */
    function desaplicarReproductor(marca) {
        marca.reproductor.eliminar();
        marca.reproductor = null;
    }

    /**
     * Muestra un elemento en el slider.
     * @param {object} marca
     * @param {boolean} alPrincipio
     */
    function colocarMarca(marca, alPrincipio) {
        var elemento = $(
                '<div class="elementoSlider" pos="' + marca.pos + '">' +
                '   <div class="imagenElementoSlider"><div><img src="" alt=""/></div></div>' +
                '   <div class="desplazamientoElementoSlider">' +
                '       <div id="playElementoSlider' + marca.pos + '"></div>' +
                '       <div id="lineaTiempoElementoSlider' + marca.pos + '" class="lineaTiempoElementoSlider"></div>' +
                '       <p id="relojElementoSlider' + marca.pos + '">00:00</p>' +
                '   </div>' +
                '</div>');

        //Ahora le añadimos la imagen de fondo
        if (marca.fondo) {
            elemento.find('.imagenElementoSlider img').attr('src', marca.fondo);
            elemento.find('.imagenElementoSlider img').click(function () {
                var vistaImagen = new VistaImagen(marca, tellmee_Servidor, nombreUsuario);
                vistaImagen.dibujar();
            });
        }
        else {
            obtenerImagenStreet(250, 185, marca.position.lat(), marca.position.lng(), function (ruta) {
                elemento.find('.imagenElementoSlider img').attr('src', ruta);
            });
        }

        if(alPrincipio) {
            slider.find('#contenedorSlider').prepend(elemento);
        }
        else {
            slider.find('#contenedorSlider').append(elemento);
        }
        
        redimensionarImagenesYContenedor(elemento.find('.imagenElementoSlider img'));

        aplicarReproductor(marca);
    };
    
    /**
     * Quita una marca del dom del slider.
     * @param {object} marca
     */
    function descolocarMarca(marca) {
        //Eliminamos la marca
        $('.elementoSlider[pos="' + marca.pos + '"]').remove();
        
        //Desplazar y redimensionar
        redimensionarImagenesYContenedor();
        if(marca.pos < posActual) {
            var izq = parseFloat($('#contenedorSlider').css('left'));
            $('#contenedorSlider').css('left', izq + esto.getAnchoElementos() + 'px');
        }
        
        desaplicarReproductor(marca);
    }

    /**
     * Coloca el slider.
     */
    this.dibujar = function () {
        $(posicion).html(slider);

        //Cargamos los elementos iniciales
        for (var i = 0; i < ladoSlider + 1; i++) {
            colocarMarca(esto.marcadores[i], false);
        }

        //Centramos el contedor
        $(posicion).css('left', (($(window).width() - $('#contenedorPrincipalSlider').outerWidth()) / 2) - 30 + 'px');

        //Posicionamos en la primera
        var centro = $('#ventanaSlider').width() / 2;
        $('#contenedorSlider').css('left', centro - esto.getAnchoElementos() / 2 + 'px');

        //Desplazamiento izquierda
        $('#contenedorPrincipalSlider > img:first-child').click(function () {
            if (posActual > 0 && moviendo == false) {
                moviendo = true;
                posActual--;
                
                //Agregar nueva marca
                if(posActual - ladoSlider >= 0) {
                    colocarMarca(esto.marcadores[posActual - ladoSlider], true);
                }

                //Desplazar
                var izq = parseFloat($('#contenedorSlider').css('left'));

                $('#contenedorSlider').animate({left: izq + esto.getAnchoElementos() + 'px'}, function () {
                    //Quitar la marca sobrante
                    if(posActual + ladoSlider + 1 < esto.marcadores.length) {
                        descolocarMarca(esto.marcadores[posActual + ladoSlider + 1]);
                    }
                    
                    if (typeof callback == 'function') {
                        callback(esto.marcadores[posActual]);
                    }

                    moviendo = false;
                });
            }
        });

        //Desplazamiento derecha
        $('#ventanaSlider + img').click(function () {
            if (posActual < esto.marcadores.length - 1 && moviendo == false) {
                moviendo = true;
                posActual++;
                
                //Agregar nueva marca
                if(posActual + ladoSlider < esto.marcadores.length) {
                    colocarMarca(esto.marcadores[posActual + ladoSlider], false);
                }

                //Desplazar
                var izq = parseFloat($('#contenedorSlider').css('left'));

                $('#contenedorSlider').animate({left: izq - esto.getAnchoElementos() + 'px'}, function () {
                    //Quitar la marca sobrante
                    if(posActual - (ladoSlider + 1) >= 0) {
                        descolocarMarca(esto.marcadores[posActual - (ladoSlider + 1)]);
                    }
                    
                    if (typeof callback == 'function') {
                        callback(esto.marcadores[posActual]);
                    }

                    moviendo = false;
                });
            }
        });
    };
}

/**
 * Añade un elemento al slider.
 * @param {object} marca
 */
Slider.prototype.addMarca = function (marca) {
    var pos = this.marcadores.push(marca) - 1;
    marca.pos = pos;
};

/**
 * Devuelve el ancho de los elementos del slider o 0.
 * @returns {Number}
 */
Slider.prototype.getAnchoElementos = function() {
    if(!this.anchoElementos) {
        this.anchoElementos = parseFloat($('.elementoSlider').outerWidth(true));
    }
    
    return this.anchoElementos;
};