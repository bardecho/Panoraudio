function iniciar() {
    var oldIE;
    
    //Para que funcione en versiones antiguas del ie
    if(navigator.appName == 'Microsoft Internet Explorer' && parseInt(navigator.appVersion) < 5) {
        $('.formNormal').hide();
        $('.formCompatible').show();
        oldIE=true;
    }
    else {
        //Texto informativo
        $('input[type="text"],input[type="password"]').focusin(entraInput);
        $('input[type="text"],input[type="password"]').focusout(saleInput);
        oldIE=false;
    }
        
    function validarEmail(email) {
        if(email.match(/[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/)) return true;
        else return false;
    }

    //Intenta registrarse
    $('form[name="compatibleRegistro"],form[name="normalRegistro"]').submit(function() {
        //Validamos el registro
        //Todos los campos son obligatorios
        if((!oldIE && ayuda['email'] == undefined) || $.trim($(this).children('input[name="email"]').val()) == '' || 
                (!oldIE && ayuda['pass'] == undefined) || $.trim($(this).children('input[name="pass"]').val()) == '' || 
                (!oldIE && ayuda['pass2'] == undefined) || $.trim($(this).children('input[name="pass2"]').val()) == '' || 
                (!oldIE && ayuda['usuario'] == undefined) || $.trim($(this).children('input[name="usuario"]').val()) == '') {
            alert(textos['mensaje_faltan_campos']);
        }
        else {
            //El email debe ser correcto
            if(!validarEmail($(this).children('input[name="email"]').val())) {
                alert(textos['mensaje_revisa_email']);
            }
            else {
                //Las contraseñas deben ser iguales
                if($(this).children('input[name="pass"]').val() != $(this).children('input[name="pass2"]').val()) {
                    alert(textos['mensaje_pass']);
                }
                else {
                    //La contraseña debe contener al menos 5 caracteres
                    if($(this).children('input[name="pass"]').val().length < 5) {
                        alert(textos['mensaje_longitud']);
                    }
                    else {
                        //Correcto
                        return true;
                    }
                }
            }
        }
        
        return false;
    });

    //Intenta loguearse
    $('form[name="compatibleLogin"],form[name="normalLogin"]').submit(function() {
        //Validamos el login
        //Todos los campos son obligatorios
        if((!oldIE && ayuda['entrar_email'] == undefined) || $.trim($(this).children('input[name="entrar_email"]').val()) == '' ||
                (!oldIE && ayuda['entrar_pass'] == undefined) || $.trim($(this).children('input[name="entrar_pass"]').val()) == '') {
            alert(textos['mensaje_faltan_campos']);
        }
        else { 
            //La contraseña debe contener al menos 5 caracteres
            if($(this).children('input[name="entrar_pass"]').val().length < 5) {
                alert(textos['mensaje_longitud']);
            }
            else {
                //Correcto
                return true;
            }
        }
        
        return false;
    });
    
    //Forzamos a que salga del input al pulsar enter
    $('input[type="text"]').keydown(function(event) {
        if(event.which == 13) {
            saleInput(event.currentTarget);
        }
    });
}
