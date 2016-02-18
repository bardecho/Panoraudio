//Crea un control personalizado para la etiqueta audio
//Si no se indica el idObjetivoControles, solamente se crea la etiqueta que se puede controlar con los métodos de la clase
//TODO: Parece que hay algún problema en el móvil con el modo sin reproductor
function Reproductor(rutaAudioMP3, rutaAudioOGG, idObjetivoControles, imgReproducir, imgPausa, imgCargando, imgError, idObjetivoTiempo, imgPivote, idObjetivoReloj) {
    var esto = this, rutaAudio;

    //Creamos el elemento audio
    this.audio = document.createElement('audio');
    this.audio.style.display = 'none';
    document.body.appendChild(this.audio);
    //Vamos a comprobar si podemos reproducir el audio
    if(rutaAudioMP3 != undefined && (this.audio.canPlayType('audio/mpeg') == 'probably' || this.audio.canPlayType('audio/mpeg') == 'maybe'))
        rutaAudio = rutaAudioMP3;
    else            
        rutaAudio = rutaAudioOGG;
    //¿Está preparado el audio?
    this.listo = false;
    
    //Espera a que el audio esté listo
    this.audio.addEventListener('loadedmetadata', function() {
        if(esto.controles != undefined)
            esto.controles.src = imgReproducir;
        if(esto.reloj !== undefined) {
            esto.reloj.innerHTML = minutosSegundos(esto.audio.duration - esto.audio.currentTime);
        }
        esto.listo = true;
        //Si la reproducción con retardo existe la ejecutamos
        if(esto.reproducirConRetardo != undefined) {
            esto.reproducir();
            esto.reproducirConRetardo = undefined;
        }
        //Lanzamos el evento de usuario
        if(esto.callback != undefined)
            esto.callback();
    }, false);
    
    //Se activa cuando el audio llega al final
    this.audio.addEventListener('ended', function() {
        //Rebobinamos para que funcione en android
        if(esto.audio.readyState == 4) {
            esto.listo = false;
            esto.audio.load();
            //Al acabar cambiamos el icono
            if(esto.controles != undefined) 
                esto.controles.src = imgCargando;
        }
        else {
            //Al acabar cambiamos el icono
            if(esto.controles != undefined) 
                esto.controles.src = imgReproducir;
        }
        
        if(esto.callbackFinalizado !== undefined) {
            esto.callbackFinalizado();
        }
    }, false);
    
    //Ahora incrustamos los controles si se ha indicado un id
    if(idObjetivoControles != undefined) {
        var objetivo = document.getElementById(idObjetivoControles);
        if(objetivo) {
            this.imgPausa = imgPausa;
            this.imgReproducir = imgReproducir;
            //El elemento a mostrar para los controles
            this.controles = document.createElement('img');
            this.controles.src = imgCargando;
            this.controles.className = 'play';
            //El click
            this.controles.addEventListener('click', function(event) {
                event.stopPropagation();
                if(esto.listo) {
                    if(esto.audio.paused || esto.audio.ended)
                        esto.reproducir();
                    else
                        esto.pausar();
                }
            }, false);

            objetivo.appendChild(this.controles);
        }
    }
    
    //Creamos la barra de avance
    if(idObjetivoTiempo != undefined) {
        this.objetivoTiempo = document.getElementById(idObjetivoTiempo);
        if(this.objetivoTiempo) {
            this.objetivoTiempo.style.position = 'relative';
            //Le añadimos un div que lo va a ir llenando
            this.divLlenado = document.createElement('div');
            this.divLlenado.style.height = '100%';
            this.divLlenado.style.width = '0';
            this.divLlenado.innerHTML = '&nbsp;';
            this.objetivoTiempo.appendChild(this.divLlenado);
            //Le añadimos también un pivote
            if(imgPivote !== undefined) {
                this.pivote = document.createElement('img');
                this.pivote.src = imgPivote;
                this.pivote.style.height = '100%';
                this.pivote.style.position = 'absolute';
                this.pivote.style.top = '0';
                this.pivote.style.left = '-' + this.objetivoTiempo.offsetHeight/2 + 'px';
                this.objetivoTiempo.appendChild(this.pivote);
            }
            //El evento que actualiza el tamaño de la barra y mueve el pivote
            this.audio.addEventListener('timeupdate', function() {
                if(esto.divLlenado != undefined) {
                    esto.divLlenado.style.width = esto.audio.currentTime/esto.audio.duration*100 + '%';
                    
                    if(esto.pivote !== undefined) {
                        esto.pivote.style.left = (esto.audio.currentTime/esto.audio.duration*esto.objetivoTiempo.offsetWidth - esto.pivote.width/2) + 'px';   
                    }
                }
            }, false);
            //Eventos que activan arrastar a una parte del audio
            var eventoMove = function(evt) { 
                        esto.arrastrar(evt, esto); 
                    };
            this.objetivoTiempo.addEventListener('mousedown', function(evt) {
                if(esto.controles.src != imgError) {
                    esto.arrastrar(evt, esto); 
                    //El evento para saltar a una parte determinada del audio
                    document.addEventListener('mousemove', eventoMove, false);
                }
            }, false);
            //Evento que desactiva saltar a una parte del audio
            document.addEventListener('mouseup', function(evt) {
                document.removeEventListener('mousemove', eventoMove, false);
            }, false);
        }
    }
    
    function minutosSegundos(segundosTotales) {
        var minutos = parseInt(segundosTotales/60);
        var segundos = parseInt(segundosTotales%60);
        
        return (minutos < 10 ? '0' + minutos : minutos) + ':' + (segundos < 10 ? '0' + segundos : segundos);
    };
    
    //Colocamos un reloj
    if(idObjetivoReloj !== undefined) {
        this.reloj = document.getElementById(idObjetivoReloj);
        
        //El evento que actualiza el tiempo restante
        this.audio.addEventListener('timeupdate', function() {
            if(esto.reloj !== undefined) {
                esto.reloj.innerHTML = minutosSegundos(esto.audio.duration - esto.audio.currentTime);
            }
        }, false);
    }
    
    //Si ocurre un error cambiamos el icono
    this.audio.addEventListener('error', function() {
        if(esto.controles != undefined) 
            esto.controles.src = imgError;
    }, false);
    //Preparamos la carga
    this.audio.src = rutaAudio;
    this.audio.load();
}

//Se encarga de llevar la barra del audio a donde está el ratón
Reproductor.prototype.arrastrar = function(evt, esto) {
    //Calculamos la posición del elemento
    var curleft = 0, obj = esto.objetivoTiempo;
    if(obj.offsetParent) {
        do {
            curleft += obj.offsetLeft;
            if(isNaN(obj.style.borderLeftWidth))
                curleft += parseInt(obj.style.borderLeftWidth);
        } while(obj = obj.offsetParent);
    }
    var xRelativa = evt.pageX - curleft;

    var pos = xRelativa/esto.objetivoTiempo.offsetWidth;
    try {
        //TODO: Por alguna razón este objeto a veces deja de existir
        esto.audio.currentTime = esto.audio.duration * pos;
    }
    catch(ex) {}
};

//Pone el audio a sonar si está listo
Reproductor.prototype.reproducir = function() {
    if(this.listo) {
        this.audio.play();
        if(this.controles != undefined) {
            //Cambiamos la imagen
            this.controles.src = this.imgPausa;
        }
        
        if(this.callbackReproducir !== undefined) {
            this.callbackReproducir();
        }
    }
    else {
        this.reproducirConRetardo = true;
    }
};

//Pausa el audio si está listo
Reproductor.prototype.pausar = function() {
    if(this.listo) {
        this.audio.pause();
        if(this.controles != undefined) {
            //Cambiamos la imagen
            this.controles.src = this.imgReproducir;
        }
        
        if(this.callbackPausar !== undefined) {
            this.callbackPausar();
        }
    }
};

//Añade un manejador para el evento que indica si el audio está listo
//CUIDADO: En android este evento vuelve a activarse cada vez que el audio suena hasta el final
Reproductor.prototype.eventoListo = function(callback) {
    this.callback = callback;
};

//Manejador para el evento reproducir
Reproductor.prototype.addAccionReproducirHandler = function(callback) {
    this.callbackReproducir = callback;
};

//Manejador para el evento pausar
Reproductor.prototype.addAccionPausarHandler = function(callback) {
    this.callbackPausar = callback;
};

//Manejador para el evento eliminar
Reproductor.prototype.addAccionEliminarHandler = function(callback) {
    this.callbackEliminar = callback;
};

//Manejador para el evento reproducción finalizada
Reproductor.prototype.addAccionFinalizadoHandler = function(callback) {
    this.callbackFinalizado = callback;
};

//Elimina todo rastro del reproductor
Reproductor.prototype.eliminar = function() {
    if(this.audio != undefined) {
        this.audio.pause();
        document.body.removeChild(this.audio);
        this.audio = undefined;
    }
    if(this.controles != undefined) {
        this.controles.parentNode.removeChild(this.controles);
        this.controles = undefined;
    }
    if(this.divLlenado != undefined) {
        this.divLlenado.parentNode.removeChild(this.divLlenado);
        this.divLlenado = undefined;
        if(this.pivote != undefined) {
            this.pivote.parentNode.removeChild(this.pivote);
            this.pivote = undefined;
        }
    }
    
    if(this.callbackEliminar !== undefined) {
        this.callbackEliminar();
    }
};