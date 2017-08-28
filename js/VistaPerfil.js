function VistaPerfil(idUsuario, nombreUsuario, servidor, usuarioActual) {
    this.idUsuario = idUsuario;
    this.nombreUsuario = nombreUsuario;
    this.servidor = servidor;
    this.datos = null;
    this.usuarioActual = usuarioActual;
}

/**
 * Crea la vista de perfil.
 */
VistaPerfil.prototype.dibujar = function() {
    var esto = this;
    
    var html = 
            "<div id='vistaPerfil_contenedor'>" + 
                "<div id='vistaPerfil_cabecera'></div>" +
                "<div id='vistaPerfil_datos'>" +
                    "<p><img id='fotoPerfil' src='"+ base_url_img + "img/fotosPerfil/" + this.idUsuario + ".jpg' alt=''/> " + this.nombreUsuario + "</p>" +
                    "<form action='" + cadenaConexion + "subirFotoPerfil' name='formuFotoPerfil' method='POST' enctype='multipart/form-data'>" +
                        "<input type='file' name='fotoPerfil' value='' accept='image/*'/>" +
                    "</form>" +
                "</div>" + 
                "<div id='vistaPerfil_imagenes'></div>" +
                
                '<img src="' + base_url_img + 'img/cerrar.png" alt="' + textos['cerrar'] + '" class="cerrar"/>' +
            "</div>";
    
    $('body').append(html);
    
    $('#fotoPerfil').error(function() {
        $(this).attr('src', base_url_img + 'img/marco-foto-vacio-63.png');
    });
    
    this.generarCabecera();
    this.generarLista();
    
    $('#vistaPerfil_contenedor .cerrar').click(function() {
        esto.eliminar();
    });
    
    $('#vistaPerfil_imagenes img').click();
    
    if(this.usuarioActual == this.nombreUsuario) {
        $('#vistaPerfil_datos img').css('cursor', 'pointer');
        
        //Cambiamos la foto del perfil
        $('input[name="fotoPerfil"]').change(function() {
            enviarArchivo($('form[name="formuFotoPerfil"]'), function(respuesta) {
                if(respuesta) {
                    $('#vistaPerfil_datos img').attr('src', base_url_img + "img/fotosPerfil/" + esto.idUsuario + ".jpg");
                }
            });
        });
    
        //Se desea cambiar la foto del perfil
        $('#vistaPerfil_datos img').click(function() {
            $('input[name="fotoPerfil"]').click();
        });
    }
};

/**
 * Elimina la vista de perfil.
 */
VistaPerfil.prototype.eliminar = function() {
    $('#vistaPerfil_contenedor').remove();
};

/**
 * Carga los datos del perfil.
 */
VistaPerfil.prototype.solicitarDatos = function(callback) {
    var esto = this;
    
    this.servidor.solicitarDatosPerfil(this.idUsuario, function(data) {
        if(data.ok) {
            esto.datos = data;
        }
        
        callback(data.ok);
    });
};

/**
 * Crea la cabecera con las imágenes disponibles.
 */
VistaPerfil.prototype.generarCabecera = function() {
    var esto = this;
    
    if(esto.datos === null) {
        //Si no están los datos los solicitamos
        esto.solicitarDatos(function(resultado) {
            if(resultado) {
                esto.generarCabecera();
            }
        });
    }
    else {
        //Generamos la cabecera según la cantidad de imágenes disponible
        var imagenesDesordenadas = desordenarLista(esto.datos.imagenes, 7);
        $('#vistaPerfil_cabecera').html(VistaPerfil.cabeceras[imagenesDesordenadas.length]);
        if(imagenesDesordenadas.length > 0) {
            var i = 0;
            $('.vistaPerfil_contenedorImagen').each(function() {
                var anchoContenedor = $(this).innerWidth(), altoContenedor = $(this).innerHeight();
                var imagen = $('<img src="' + base_url_img + 'img/fondos/' + imagenesDesordenadas[i].idAudio + '.jpg" alt=""/>');
                imagen.load(function() {
                    var multiplicadorW = anchoContenedor / $(this).width();
                    var multiplicadorH = altoContenedor / $(this).height();
                    if (multiplicadorH < multiplicadorW)
                        $(this).css('width', '100%');
                    else
                        $(this).css('height', '100%');
                    
                    $(this).css('visibility', 'visible');
                });
                
                $(this).html(imagen);
                i++;
            });
        }
        
        function crearBotonSeguir() {
            $('#fotoPerfil').parent().append("<img class='seguir' src='"+ base_url_img + "img/seguir.png' alt=''/>");
            $('.seguir').click(function() {
                esto.servidor.modificarSeguir(function() {
                    $('.seguir').remove();
                    crearBotonNoSeguir();
                }, 1, esto.idUsuario);
            });
        }
        
        function crearBotonNoSeguir() {
            $('#fotoPerfil').parent().append("<img class='noSeguir' src='"+ base_url_img + "img/noSeguir.png' alt=''/>");
            $('.noSeguir').click(function() {
                esto.servidor.modificarSeguir(function() {
                    $('.noSeguir').remove();
                    crearBotonSeguir();
                }, 0, esto.idUsuario);
            });
        }

        //Ponemos el botón seguir o dejar de seguir
        if(this.usuarioActual && this.usuarioActual != this.nombreUsuario) {
            if(esto.datos.siguiendo) {
                crearBotonNoSeguir();
            }
            else {
                crearBotonSeguir();
            }
        }
    
    }
};

VistaPerfil.cabeceras = {
    0: '<div style="width:100%;height:100%;"><img src="' + base_url_img + 'img/marco-foto-vacio-63.png" alt=""/></div>',
    1: '<div class="vistaPerfil_contenedorImagen" style="width:100%;height:100%;"></div>',
    2: '<div class="vistaPerfil_contenedorImagen" style="width:50%;height:100%;"></div>' + 
            '<div class="vistaPerfil_contenedorImagen" style="width:50%;height:100%;"></div>',
    3: '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:100%;"></div>' + 
            '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:100%;"></div>' + 
            '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:100%;"></div>',
    4: '<div class="vistaPerfil_contenedorImagen" style="width:50%;height:50%;"></div>' + 
            '<div class="vistaPerfil_contenedorImagen" style="width:50%;height:50%;"></div>' + 
            '<div class="vistaPerfil_contenedorImagen" style="width:50%;height:50%;"></div>' +
            '<div class="vistaPerfil_contenedorImagen" style="width:50%;height:50%;"></div>',
    5: '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:50%;"></div>' + 
            '<div class="vistaPerfil_contenedorImagen" style="width:66.66%;height:50%;"></div>' + 
            '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:50%;"></div>' +
            '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:50%;"></div>' +
            '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:50%;"></div>',
    6: '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:50%;"></div>' + 
            '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:50%;"></div>' + 
            '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:50%;"></div>' +
            '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:50%;"></div>' +
            '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:50%;"></div>' +
            '<div class="vistaPerfil_contenedorImagen" style="width:33.33%;height:50%;"></div>',
    7: '<div style="width:25%;height:100%;">' +
            '<div class="vistaPerfil_contenedorImagen" style="width:100%;height:50%;"></div>' +
            '<div class="vistaPerfil_contenedorImagen" style="width:100%;height:50%;"></div>' +
       '</div>' +
       '<div style="width:25%;height:100%;">' +
            '<div class="vistaPerfil_contenedorImagen" style="width:100%;height:100%;"></div>' +
       '</div>' +
       '<div style="width:50%;height:100%;">' +
            '<div class="vistaPerfil_contenedorImagen" style="width:50%;height:50%;"></div>' +
            '<div class="vistaPerfil_contenedorImagen" style="width:50%;height:50%;"></div>' +
            '<div class="vistaPerfil_contenedorImagen" style="width:50%;height:50%;"></div>' +
            '<div class="vistaPerfil_contenedorImagen" style="width:50%;height:50%;"></div>' +
       '</div>'
};

VistaPerfil.prototype.generarLista = function() {
    var esto = this;
    
    if(esto.datos === null) {
        //Si no están los datos los solicitamos
        esto.solicitarDatos(function(resultado) {
            if(resultado) {
                esto.generarLista();
            }
        });
    }
    else {
        var html = 
                '<section id="least">' +
                    '<div class="least-preview"></div>' +
                    '<ul class="least-gallery">';
        for(var x in esto.datos.imagenes) {
            html += 
                '<li>' +
                    '<a href="' + base_url_img + 'img/fondos/' + esto.datos.imagenes[x].idAudio + '.jpg" ' +
                    'title="Votos: ' + esto.datos.imagenes[x].puntuacion + '" ' +
                    'data-subtitle="Comentarios: ' + esto.datos.imagenes[x].cantidadComentarios + '" >' +
                        '<img id="' + esto.datos.imagenes[x].idAudio + '_foto" src="' + base_url_img + 'img/fondos/' + esto.datos.imagenes[x].idAudio + '_mini.jpg" alt="" />' +
                    '</a>' +
                '</li>';
        }
        html += 
                '</ul>' +
            '</section>';
        
        $('#vistaPerfil_imagenes').html(html);
        
        $('#vistaPerfil_imagenes li').click(function() {
            var id = parseInt($(this).find('img').attr('id'));
            
            esto._htmlFuturo(id, 5);
        });
        
        $('.least-gallery').least();
    }
};

/**
 * Aplica el evento sobre la imagen ampliada una vez haya cambiado.
 * @param {int} idBuscado
 * @param {int} ttl La cantidad de intentos.
 */
VistaPerfil.prototype._htmlFuturo = function(idBuscado, ttl) {
    var continuar = true, esto = this;

    var url = $('.least-preview img').attr('src');
    if(url) {
        var partes1 = url.split('/');
        var partes2 = partes1[partes1.length - 1].split('.');
        var id = partes2[0];
    
        if(id == idBuscado) {
            continuar = false;
            
            $('.least-preview img').click(function() {
                window.open(base_url + '?id=' + id);
            });
        }
    }

    if (continuar && ttl > 0) {
        setTimeout(function() {
            esto._htmlFuturo(idBuscado, --ttl);
        }, 250);
    }
};