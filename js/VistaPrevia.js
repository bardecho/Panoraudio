/**
 * Genera una vista previa de los audios.
 * @param {Tellmee_Servidor} servidor
 * @param {Tellmee_Mapa} mapa
 * @param {int} cantidadPuntos
 * @param {string} usuarioActual
 * @returns {VistaPrevia}
 */
function VistaPrevia(servidor, mapa, cantidadPuntos, usuarioActual) {
    this.servidor = servidor;
    this.mapa = mapa;
    this.cantidadPuntos = cantidadPuntos;
    this.usuarioActual = usuarioActual;
    this.reproductores = [];

    /**
     * Crea el marco de la vista previa oculto. Se llama automáticamente al instanciar.
     */
    this.crearVistaPrevia = function() {
        this.marcoVistaPrevia = $('<div id="marcoVistaPrevia"></div>');
        this.marcoVistaPrevia.hide();
        $('#contenedorMapa').append(this.marcoVistaPrevia);
    };
    
    this.crearVistaPrevia();
}

/**
 * Actualiza la vista previa. Llama a crearVistaPrevia de ser necesario.
 */
VistaPrevia.prototype.actualizarVistaPrevia = function() {
    var esto = this;
    
    if(esto.marcoVistaPrevia === undefined) {
        esto.crearVistaPrevia();
    }
    
    var limites = esto.mapa.getLimitesVisibles();
    esto.servidor.vistaPrevia(function(resultado) {
        esto.marcoVistaPrevia.empty();
        esto.eliminarReproductores();
        if(resultado.ok) {
            for (var x = 0, len = resultado.ids.length; x < len; x++) {
                var marca = esto.mapa.capas.marcadores.obtenerMarcador(resultado.ids[x]);

                if(marca) {
                    esto._crearElementoVistaPrevia(esto, marca);
                }
            }
            esto.marcoVistaPrevia.show();
        }
        else {
            esto.marcoVistaPrevia.hide();
        }
    }, limites.latSupDer, limites.lonSupDer, limites.latInfIzq, limites.lonInfIzq, esto.cantidadPuntos);
};

/**
 * Elimina el marco de la vista previa.
 */
VistaPrevia.prototype.eliminarVistaPrevia = function() {
    this.eliminarReproductores();
    this.marcoVistaPrevia.remove();
    this.marcoVistaPrevia = undefined;
};

VistaPrevia.prototype.eliminarReproductores = function() {
    for(var x in this.reproductores) {
        this.reproductores[x].eliminar();
    }
};

/**
 * Crea un elemento individual de la vista previa.
 * @param {VistaPrevia} esto
 * @param {marca} marca
 */
VistaPrevia.prototype._crearElementoVistaPrevia = function(esto, marca) {
    var elemento = $('<div id="imagenPrevia-' + marca.id + '"></div>');
    esto.marcoVistaPrevia.append(elemento);
    
    if (marca.fondo) {
        elemento.css('background-image', 'url(' + marca.fondo + ')');
        elemento.click(function() {
            var infoImagen = new VistaImagen(marca, esto.servidor, esto.usuarioActual);
            infoImagen.dibujar();
        });
    }
    else {
        obtenerImagenStreet(250, 185, marca.position.lat(), marca.position.lng(), function(rutaImagen) {
            elemento.css('background-image', 'url(' + rutaImagen + ')');
        });
    }

    var rutaDividida = marca.ruta.split('.');
    var ruta = marca.ruta.substring(0, marca.ruta.length - rutaDividida[rutaDividida.length - 1].length);
    this.reproductores.push(new Reproductor(ruta + 'mp3', ruta + 'ogg', 'imagenPrevia-' + marca.id, base_url_img + 'img/play.png', base_url_img +
            'img/pause.png', base_url_img + 'img/cargando.gif', base_url_img + 'img/error.png'));
};