////////Este objeto se comunica con el servidor
function Tellmee_Servidor(cadenaConexion, tiempoSolicitud, callbackSolicitud, cache) {
    this.cadenaConexion = cadenaConexion;
    this.tiempoSolicitud = tiempoSolicitud; //30000
    this.callbackSolicitud = callbackSolicitud;
    this.cache = cache; //Boolean
    if(Storage == undefined)
        this.cache = false;
    this._solicitudMarcas = undefined;
}

//Puntuar un audio
Tellmee_Servidor.prototype.puntuar = function(id, puntos, callback) {
    $.getJSON(this.cadenaConexion + 'valorar/' + id + "/" + puntos + '?callback=?', callback).
        error(function(data, texto, http) {});
};

//Contar una descarga
Tellmee_Servidor.prototype.contarDescarga = function(id, callback) {
    $.getJSON(this.cadenaConexion + 'nuevaDescarga/' + id + '?callback=?', callback).
        error(function(data, texto, http) {});
};

//Borrar un elemento
Tellmee_Servidor.prototype.borrarElemento = function(id, callback) {
    $.getJSON(this.cadenaConexion + 'quitarMarca/' + id + '?callback=?', callback).
        error(function(data, texto, http) {});
};

//Marcar un audio como inapropiado
Tellmee_Servidor.prototype.marcarInapropiado = function(id, tipoDenuncia, callback) {
    $.getJSON(this.cadenaConexion + 'marcarInapropiado/' + id + '/' + tipoDenuncia + '?callback=?', callback).
        error(function(data, texto, http) {});
};

//Recargar los datos
Tellmee_Servidor.prototype.solicitarAudios = function() {
    var callback, cadenaConexion;
    
    if(arguments[0] == undefined) {
        callback = this.callbackSolicitud;
        cadenaConexion = this.cadenaConexion;
    }
    else {
        callback = arguments[0].callbackSolicitud;
        cadenaConexion = arguments[0].cadenaConexion;
    }
    
    $.getJSON(cadenaConexion + 'obtenerAudios?callback=?', function(data) {
        var datosProcesados, marcadores = new Object(), fondo;
        
        if(data.ok) {//Temporalmente se le añade base_url para reducir el consumo de ancho de banda
            for(var x = 0;x < data.marcadores.length;x++) {
                if(data.marcadores[x][11])
                    fondo = base_url_img + 'img/fondos/' + data.marcadores[x][0] + '_mini.jpg';
                else
                    fondo = false;
                
                if(marcadores[data.marcadores[x][6]] === undefined) {
                    marcadores[data.marcadores[x][6]] = new Array();
                }
                
                marcadores[data.marcadores[x][6]].push({'id': data.marcadores[x][0], 'la': data.marcadores[x][1], 
                    'lo': data.marcadores[x][2], 'ruta': base_url_audio + 'sonido/' + data.marcadores[x][3], 
                    'puntosPositivos': data.marcadores[x][4], 'puntosNegativos': data.marcadores[x][5], 
                    'categoria': data.marcadores[x][6], 'user': data.marcadores[x][7], 'marca': data.marcadores[x][8], 
                    'descripcion': data.marcadores[x][9], 'descargas': data.marcadores[x][10], 
                    'fondo': fondo, 'rutas': data.marcadores[x][12], 'idUser': data.marcadores[x][13], 
                    'idArea': data.marcadores[x][14], 'nombreArea': data.marcadores[x][15], 'limitesArea': data.marcadores[x][16]
                });
            }
        
            datosProcesados = {'ok': true, 'marcadores': marcadores};
        }
        else
            datosProcesados = data;
        
        callback(datosProcesados);
    }).error(function(data, texto, http) {});
};

//Solicita los comentarios de un audio
Tellmee_Servidor.prototype.solicitarComentarios = function(idAudio, callback) {
    $.getJSON(this.cadenaConexion + 'obtenerComentarios' + '/' + idAudio + '?callback=?', function(data) {
        var datosProcesados, comentarios = [];
        
        if(data.ok) {
            for(var x = 0;x < data.comentarios.length;x++) {
                comentarios.push({'idUser': data.comentarios[x][0], 'user': data.comentarios[x][1],
                    'comentario': data.comentarios[x][2]});
            }
        
            datosProcesados = {'ok': true, 'comentarios': comentarios };
        }
        else
            datosProcesados = data;
        
        callback(datosProcesados);
    }).error(function(data, texto, http) {});
};

//Solicita los datos de perfil
Tellmee_Servidor.prototype.solicitarDatosPerfil = function(idUsuario, callback) {
    $.getJSON(this.cadenaConexion + 'obtenerDatosPerfil' + '/' + idUsuario + '?callback=?', function(data) {
        var datosProcesados, imagenes = [];
        
        if(data.ok) {
            for(var x = 0;x < data.datos.length;x++) {
                imagenes.push({'idAudio': data.datos[x][0], 'puntuacion': data.datos[x][1],
                    'cantidadComentarios': data.datos[x][2]});
            }
        
            datosProcesados = {'ok': true, 'imagenes': imagenes };
        }
        else
            datosProcesados = data;
        
        callback(datosProcesados);
    }).error(function(data, texto, http) {});
};

//Graba un nuevo comentario
Tellmee_Servidor.prototype.enviarComentario = function(idAudio, texto, callback, callbackError) {
    $.getJSON(this.cadenaConexion + 'guardarComentario' + '/' + idAudio + '/' + texto + '?callback=?', callback).
            error(callbackError);
};

//Activar un intervalo de actualización de marcas
Tellmee_Servidor.prototype.arrancarAutoSolicitarAudios = function() {
    var esto = this;

    this._solicitudMarcas = setInterval(function() { esto.solicitarAudios(esto); }, this.tiempoSolicitud);
};

//Desactivar la actualización de marcas
Tellmee_Servidor.prototype.pararAutoSolicitarAudios = function() {
    clearInterval(this._solicitudMarcas);
};

//Coloca una nueva marca en el servidor
Tellmee_Servidor.prototype.nuevaMarca = function(categoria, idiomaAudio, latitud, longitud, descripcion, callback, callbackError) {
    $.getJSON(this.cadenaConexion + 'nuevaMarca' + '/' + categoria + '/' + idiomaAudio + '/' + latitud + '/' + 
        longitud + '/' + descripcion + '?callback=?', callback).
        error(callbackError);
};

//Encuentra las coordenadas de una marca
Tellmee_Servidor.prototype.localizarAudio = function(id, callback, callbackError) {
    $.getJSON(this.cadenaConexion + 'localizarAudio' + '/' + id + '?callback=?', callback).
        error(callbackError);
};

//Consigue el nombre del usuario
Tellmee_Servidor.prototype.obtenerUsuario = function(callback) {
    $.getJSON(this.cadenaConexion + 'obtenerUsuario?callback=?', callback).
        error(function(data, texto, http) {});
};

//Solicitamos las categorías
Tellmee_Servidor.prototype.solicitarCategorias = function(callback) {
    $.getJSON(this.cadenaConexion + 'listar/categorias?callback=?', callback).
        error(function(data, texto, http) {});
};

//Modificamos las categorías en las preferencias
Tellmee_Servidor.prototype.modificarCategorias = function(callback) {
    var cat='', esto=this;

    if(listaCat.length > 0) {
        for(var x=0, len=listaCat.length;x < len;x++)
            cat+=listaCat[x]+',';
        cat=cat.substr(0, cat.length-1);

        $.getJSON(this.cadenaConexion + 'modificarCategoriasPreferidas/' + cat +'?callback=?', function() { 
            //esto.solicitarAudios(esto);
            
            if(callback != undefined)
                callback();
        }).error(function(data, texto, http) {});
    }
};

//Solicitamos una búsqueda de marca
Tellmee_Servidor.prototype.buscarMarca = function(texto, callback, latSupDer, lonSupDer, latInfIzq, lonInfIzq) {
    var limites = '';
    if(latSupDer !== undefined && lonSupDer !== undefined && latInfIzq !== undefined && lonInfIzq !== undefined) {
        limites = '/' + latSupDer + '/' + lonSupDer + '/' + latInfIzq + '/' + lonInfIzq;
    }
    
    $.getJSON(this.cadenaConexion + 'buscarMarca/' + texto + limites + '?callback=?', callback).
        error(function(data, texto, http) {});
};

//Solicitamos una búsqueda por zona
Tellmee_Servidor.prototype.vistaPrevia = function(callback, latSupDer, lonSupDer, latInfIzq, lonInfIzq, cantidad) {
    var limites = latSupDer + '/' + lonSupDer + '/' + latInfIzq + '/' + lonInfIzq + '/' + cantidad;
    
    $.getJSON(this.cadenaConexion + 'vistaPrevia/' + limites + '?callback=?', callback).
        error(function(data, texto, http) {});
};

//Guardamos una ruta
Tellmee_Servidor.prototype.guardarRuta = function(idsRuta, callback) {
    if(idsRuta instanceof Array && idsRuta.length >= 2) {
        var parametro = '';
        for(var x=0,len=idsRuta.length;x<len;x++) {
            parametro += idsRuta[x] + ',';
        }

        $.getJSON(this.cadenaConexion + 'guardarRutas/' + parametro.substr(0, parametro.length - 1) + '?callback=?', callback).
            error(function(data, texto, http) {});
    }
};

//Borramos unas rutas
Tellmee_Servidor.prototype.borrarRuta = function(idsRuta, callback) {
    if(idsRuta instanceof Array) {
        var parametro = '';
        for(var x=0,len=idsRuta.length;x<len;x++) {
            parametro += idsRuta[x] + ',';
        }

        $.getJSON(this.cadenaConexion + 'borrarRutas/' + parametro.substr(0, parametro.length - 1) + '?callback=?', callback).
            error(function(data, texto, http) {});
    }
};

//Solicitamos las rutas del usuario
Tellmee_Servidor.prototype.solicitarRutas = function(callback) {
    $.getJSON(this.cadenaConexion + 'cargarRutas?callback=?', callback).
        error(function(data, texto, http) {});
};

//Une un audio y su area
Tellmee_Servidor.prototype.enlazarArea = function(idAudio, idArea, area, latitudIzquierdaInferior, longitudIzquierdaInferior, 
            latitudDerechaSuperior, longitudDerechaSuperior, callback) {
    $.getJSON(this.cadenaConexion + 'enlazarArea/' + idAudio + '/' + idArea + '/' + area + '/' + latitudIzquierdaInferior + 
            '/' + longitudIzquierdaInferior + '/' + latitudDerechaSuperior + 
            '/' + longitudDerechaSuperior + '?callback=?', callback).
        error(function(data, texto, http) {});
};

//Solicita recuperar la contraseña
Tellmee_Servidor.prototype.solicitarRecuperar = function(callback, email) {
    $.getJSON(this.cadenaConexion + 'recuperar/' + email + '?callback=?', callback).
        error(function(data, texto, http) {});
};

//Solicita cambiar la contraseña
Tellmee_Servidor.prototype.cambiarPass = function(callback, nuevoPass) {
    $.getJSON(this.cadenaConexion + 'cambiarPass/' + nuevoPass + '?callback=?', callback).
        error(function(data, texto, http) {});
};