/**
 * Genera una pantalla de datos de imagen.
 * @param {object} marca
 * @param {Tellmee_Servidor} servidor
 * @param {string} usuarioActual
 * @returns {VistaImagen}
 */
function VistaImagen(marca, servidor, usuarioActual) {
    this.marca = marca;
    this.servidor = servidor;
    this.usuarioActual = usuarioActual;
}

/**
 * Crea la vista de imagen ampliada.
 */
VistaImagen.prototype.dibujar = function() {
    var esto = this;
    
    var html = 
            "<div id='vistaImagen_contenedor'>" + 
                "<div id='vistaImagen_imagen'>" +
                    "<img src='' alt=''/>" +
                "</div>" +
                
                "<div>" + 
                    "<div id='vistaImagen_datos'>" + 
                        "<p><img src='"+ base_url_img + "img/fotosPerfil/" + this.marca.idUsuario + ".jpg' alt=''/> " + this.marca.usuario + "</p>" +
                        "<p>" + this.marca.descripcion + "</p>" +
                        "<form action='" + cadenaConexion + "subirFotoPerfil' name='formuFotoPerfil' method='POST' enctype='multipart/form-data'>" +
                            "<input type='file' name='fotoPerfil' value='' accept='image/*'/>" +
                        "</form>" +
                    "</div>" +
                    
                    "<div id='vistaImagen_puntos'>" + 
                        "<p><img src='" + base_url_img + "img/bien-icon_gris.png' alt=''/> <span>" + this.marca.puntosPositivos + "</span></p>" +
                    "</div>" +
                    
                    "<div id='vistaImagen_padreComentarios'>" + 
                        "<div id='vistaImagen_comentarios'></div>" +
                        
                        "<div>" + 
                            "<p><input class='inputText comentario' disabled='disabled' type='text' value='' name='comentario'> <span id='botonComentario' class='boton'>" + textos['enviar'] + "</span></p>" +
                        "</div>" +
                    "</div>" +
                "</div>" +
                
                '<img src="' + base_url_img + 'img/cerrar.png" alt="' + textos['cerrar'] + '" class="cerrar"/>' +
            "</div>";
    
    $('body').append(html);
    
    $('#vistaImagen_contenedor .cerrar').click(function() {
        esto.eliminar();
    });

    //Si no se carga la imagen la cambiamos por la por defecto
    $('#vistaImagen_datos img').error(function() {
        $(this).attr('src', base_url_img + 'img/marco-foto-vacio-63.png');
    });
    
    if(this.usuarioActual == this.marca.usuario) {
        $('#vistaImagen_datos img').css('cursor', 'pointer');
        
        //Cambiamos la foto del perfil
        $('input[name="fotoPerfil"]').change(function() {
            enviarArchivo($('form[name="formuFotoPerfil"]'), function(respuesta) {
                if(respuesta) {
                    $('#vistaImagen_datos img').attr('src', base_url_img + "img/fotosPerfil/" + esto.marca.idUsuario + ".jpg");
                }
            });
        });
    
        //Se desea cambiar la foto del perfil
        $('#vistaImagen_datos img').click(function() {
            $('input[name="fotoPerfil"]').click();
        });
    }
    
    if(this.usuarioActual) {
        $('#vistaImagen_puntos img').css('cursor', 'pointer');
        $('#vistaImagen_puntos img').attr('src', base_url_img + "img/bien-icon.png");
        
        //Cambiamos la imagen de puntuar y añadimos el evento
        $('#vistaImagen_puntos img').click(function() { 
            esto.servidor.puntuar(esto.marca.id, 1, function (data) {
                if (data.ok) {
                    //Actualizamos la puntuación
                    $('#vistaImagen_puntos span').html(data.puntosPositivos);
                }
            });
        });
        
        //Permitimos enviar comentarios
        $('input[name="comentario"]').removeAttr('disabled');
        $('#botonComentario').click(function() {
            var texto = $('input[name="comentario"]').val();
            esto.servidor.enviarComentario(esto.marca.id, texto, function(respuesta) {
                if(respuesta.ok) {
                    esto.refrescarComentarios();
                }
            });
        });
    }
    
    //Ahora le añadimos la imagen de fondo
    if (this.marca.fondo) {
        var fondoGrande = this.marca.fondo.split('_');
        $('#vistaImagen_imagen img').attr('src', fondoGrande[0] + '.jpg');
    }
    else {
        obtenerImagenStreet(500, 500, this.marca.position.lat(), this.marca.position.lng(), function (ruta) {
            $('#vistaImagen_imagen img').attr('src', ruta);
        });
    }
    
    var contenedorImagen = $('#vistaImagen_imagen'), imagen = $('#vistaImagen_imagen img');
    imagen.load(function() {
        var dimensiones = redimensionarImagen(parseFloat(imagen.width()), parseFloat(imagen.height()), 
                parseFloat(contenedorImagen.width()) * 0.95, parseFloat(contenedorImagen.height()) * 0.95);
        imagen.css(dimensiones);
        imagen.css(centrarImagen(parseFloat(imagen.width()), parseFloat(imagen.height()), 
                parseFloat(contenedorImagen.width()), parseFloat(contenedorImagen.height())));
        imagen.css('visibility', 'visible');
    });
    
    //Solicitamos los comentarios
    this.refrescarComentarios();
};

/**
 * Solicita los comentarios y los introduce en la vista.
 */
VistaImagen.prototype.refrescarComentarios = function() {
    this.servidor.solicitarComentarios(this.marca.id, function(datos) {
        if(datos.ok) {
            var comentariosString = '';
            for(var x=0,len=datos.comentarios.length;x<len;x++) {
                comentariosString += 
                        '<div class="vistaImagen_comentario">' +
                            '<div><img src="' + base_url_img + 'img/fotosPerfil/' + datos.comentarios[x].idUser + '.jpg" alt=""/></div>' + 
                            '<div>' + 
                            '<p class="nombreUsuario">' + datos.comentarios[x].user + '</p>' +
                            '<p>' + datos.comentarios[x].comentario + '</p>' +
                            '</div>' + 
                            '<div style="float:none;clear:both"></div>' + 
                        '</div>';
            }
            
            $('#vistaImagen_comentarios').html(comentariosString);
            
            //Si no se carga la imagen la cambiamos por la por defecto
            $('#vistaImagen_comentarios img').error(function() {
                $(this).attr('src', base_url_img + 'img/marco-foto-vacio-63.png');
            });
        }
    });
};

/**
 * Elimina la vista de imagen ampliada.
 */
VistaImagen.prototype.eliminar = function() {
    $('#vistaImagen_contenedor').remove();
};