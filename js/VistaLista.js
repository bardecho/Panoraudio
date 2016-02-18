/**
 * Genera la vista de lista y la muestra vacía.
 * @param {Tellmee_Servidor} servidor
 * @param {Tellmee_Mapa} mapa
 * @param {function} callback Se llama cuando se hace click sobre un elemento.
 * @param {string} imgCargando
 * @returns {VistaPrevia}
 */
function VistaLista(servidor, mapa, callback, imgCargando) {
    this.servidor = servidor;
    this.mapa = mapa;
    this.marcoVistaLista = $('<div id="vistaLista"><ul class="vistaLista"></ul></div>');
    this.callback = callback;
    this.imgCargando = imgCargando;
    
    $('#contenedorMapa').append(this.marcoVistaLista);
}

/**
 * Elimina la vista de lista.
 */
VistaLista.prototype.eliminarVistaLista = function() {
    this.marcoVistaLista.remove();
    this.marcoVistaLista = undefined;
};

/**
 * Actualiza la vista de lista.
 */
VistaLista.prototype.actualizarVistaLista = function() {
    var esto = this;

    esto.marcoVistaLista.empty();
    if(esto.imgCargando) {
        //Icono de carga
        esto.marcoVistaLista.css('textAlign', 'center');
        esto.marcoVistaLista.append('<img src="' + esto.imgCargando + '" alt=""/>');
    }
    var limites = esto.mapa.getLimitesVisibles();
    esto.servidor.vistaPrevia(function(resultado) {
        if(esto.imgCargando) {
            esto.marcoVistaLista.empty();
            esto.marcoVistaLista.css('textAlign', 'left');
        }
        
        if(resultado.ok) {
            var html = '<ul class="vistaLista">';
            for (var x = 0, len = resultado.ids.length; x < len; x++) {
                var marca = esto.mapa.capas.marcadores.obtenerMarcador(resultado.ids[x]);

                if(marca) {
                    html += esto._crearFila(marca);
                }
            }
            html += '</ul>';
            
            esto.marcoVistaLista.append(html);
            
            //Evento click
            if(esto.callback) {
                $('.vistaLista p').click(esto.callback);
            }
        }
    }, limites.latSupDer, limites.lonSupDer, limites.latInfIzq, limites.lonInfIzq, 0);
};

/**
 * Devuelve una fila de la vista de lista.
 * @param {marcador} marca
 * @returns {String}
 */
VistaLista.prototype._crearFila = function(marca) {
    if(fotoPuntos && marca.fondo) {
        var imagen = marca.fondo;
    }
    else {
        var imagen = base_url_img + 'img/categorias/' + marca.categoria + '.png';
    }
    
    
    var fila =
        '<li>' +
            '<p id="' + marca.id + '_vistaListaFila"><img src="' + imagen + '" alt="' + categoriasPlataforma[marca.categoria] + '"/>' +
            '<span class="medium">' + marca.usuario + '</span> — ' + marca.descripcion + '</p>' +
        '</li>' +
        '<li>' +
            '<hr/>' + 
        '</li>';

    return fila;
};