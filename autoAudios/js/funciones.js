//var base_url='http://panoraudio.com/autoAudios/';
var base_url='http://localhost/autoAudios/', procesadas = 0, fallidas = 0;

function cogerTexto(url, nombre) {
    $.getJSON(base_url + 'cogerTexto.php?callback=?', 
    {
        url: url
    }, function(data) {
        if(data.ok) {
            $('#obteniendo').attr('src', base_url + 'img/ok.jpg');
            //Traducimos y leemos
            traducirLeer(nombre, data.texto);
        }
        else
            $('#obteniendo').attr('src', base_url + 'img/error.jpg');
    });
}

function traducirLeer(nombre, texto) {
    var idiomas = ['es'];//, 'en', 'pt', 'fr', 'de'
    $('#idiomas').empty();
    for(var i=0, len = idiomas.length;i < len;i++) {
        var idioma = idiomas[i];
        $('#idiomas').append('<p>Traduciendo y leyendo en ' + idioma + ' <img src="' + base_url + 'img/cargando.gif"  id="' + idioma + '" width="15"/></p>');
        $.post(base_url + 'traducirLeer.php', {
            texto: texto,
            idioma: idioma,
            nombre: nombre
        }, function(dataLeer) {
            //Terminado
            dataLeer = $.parseJSON(dataLeer);

            if(dataLeer.ok) {
                $('#' + dataLeer.idioma).attr('src', base_url + 'img/ok.jpg');
                procesadas++;
            }
            else {
                $('#' + dataLeer.idioma).attr('src', base_url + 'img/error.jpg');
                fallidas++;
            }
            $('#procesadasInterior').html('<p>' + procesadas + ' procesadas correctamente y ' + fallidas + ' fallidas.</p>');
            
        });
    }
}

$(function() {
    $('input[name="limpiar"]').click(function() {
        //Vaciamos los campos
        $('input[type="text"]').val('');
        $('textarea').val('');
    });
    
    //Enviar uno
    $('input[name="enviar"]').click(function() {
        //Cogemos el texto
        $('#obteniendo').attr('src', base_url + 'img/cargando.gif');
        var url = $.trim($('input[name="url"]').val()), nombre = $.trim($('input[name="nombre"]').val());
        if(url) {
            cogerTexto(url, nombre);
        }
    });
    
    //Enviar varios
    $('input[name="enviarVarios"]').click(function() {
        $('#procesadas').empty();
        $('#procesadas').append('<p id="procesadasInterior"></p>');
        procesadas = 0;
        fallidas = 0;
        //Cogemos el texto
        $('#obteniendo').attr('src', base_url + 'img/cargando.gif');
        var urls = $('textarea[name="urls"]').val().split('\n'), contador = parseInt($('input[name="numero"]').val());
        for(var x=0,len=urls.length;x<len;x++) {
            if(urls[x]) {
                cogerTexto($.trim(urls[x]), contador);
                contador++;
            }
        }
        $('#procesadas').prepend('<p>' + urls.length + ' introducidas en total.</p>');
    });
});