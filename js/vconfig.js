function iniciar() {
    //Salir sin grabar
    $('input[name="cancelar"]').click(function() {
        document.location='mapa';
    });
    
    //Validamos la configuración
    $('input[name="aceptar"]').click(function() {
        var errores='';
        //idiomasAudio
        var idiomasAudio=false;
        $('input[name="idiomasAudio[]"]').each(function() {
            if($(this).is(':checked'))
                idiomasAudio = true;
        });
        if(!idiomasAudio) errores += textos['faltaIdiomaAudio'] + '\n';
        //categorias
        var categorias=false;
        $('input[name="categorias[]"]').each(function() {
            if($(this).is(':checked'))
                categorias = true;
        });
        if(!categorias) errores += textos['faltaCategoria'];
        
        if(errores != '') {
            alert(errores);
            return false;
        }
        else return true;
    });
}