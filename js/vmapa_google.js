////////Objeto principal para manejar el mapa
function Tellmee_Mapa(contenedor, latitud, longitud, zoom, limiterSuperiores) {
    this.capas = new Object();
    var opciones = {
      zoom: zoom,
      center: new google.maps.LatLng(latitud, longitud),
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      panControl: false,
      zoomControl: true,
      zoomControlOptions: {
          style: google.maps.ZoomControlStyle.SMALL,
          position: google.maps.ControlPosition.LEFT_CENTER
      },
      mapTypeControl: false,
      scaleControl: false,
      streetViewControl: true,
      streetViewControlOptions: {
          position: google.maps.ControlPosition.LEFT_CENTER
      },
      overviewMapControl: false
    };
    this.mapa = new google.maps.Map(contenedor, opciones);
    this.globo = new Tellmee_Globo(this.mapa);
    this._eventosMapa = new Array();
    this._geocoder = new google.maps.Geocoder();
    
    if(limiterSuperiores) {
        //Impide que te quedes en la zona gris
        var mapa = this.mapa;

        google.maps.event.addListener(mapa, 'mouseup', function() {
            var posActual = mapa.getCenter();

            if (posActual.lat() > 86) {
                mapa.panTo(new google.maps.LatLng(86, posActual.lng()));
            }
            else if(posActual.lat() < -85) {
                mapa.panTo(new google.maps.LatLng(-85, posActual.lng()));
            }
        });
    }
}

//Añade una capa a Tellmee
Tellmee_Mapa.prototype.anhadirCapa = function(nombre, capa) {
    if(capa instanceof Tellmee_Capa || capa instanceof Tellmee_CapaPolilineas || capa instanceof Tellmee_CapaRutas)
        this.capas[nombre] = capa;
};

//Elimina una capa de Tellmee
Tellmee_Mapa.prototype.eliminarCapa = function(nombre) {
    delete this.capas[nombre]; 
};

//Muestra una capa en el mapa
Tellmee_Mapa.prototype.mostrarCapa = function(nombre) {
    if(this.capas[nombre] != undefined) {
        this.capas[nombre].mostrar(true, this.mapa);
    }
};

//Oculta una capa del mapa
Tellmee_Mapa.prototype.ocultarCapa = function(nombre) {
    if(this.capas[nombre] != undefined) {
        this.capas[nombre].ocultar();
    }
};

//Devuelve las coordenadas de los límites visibles del mapa.
Tellmee_Mapa.prototype.getLimitesVisibles = function() {
    var limites = this.mapa.getBounds(), resultado;

    if(limites !== undefined) {
        resultado = {
            latSupDer: limites.getNorthEast().lat(), 
            lonSupDer: limites.getNorthEast().lng(), 
            latInfIzq: limites.getSouthWest().lat(), 
            lonInfIzq: limites.getSouthWest().lng() 
        };
    }
    else {
        resultado = {
            latSupDer: 84.12497319, 
            lonSupDer: 174.7265625, 
            latInfIzq: -85.11141578, 
            lonInfIzq: -176.8359375
        };
    }
    
    return resultado;
};

//Activa un evento click que solo funciona cuando no se arrastra
Tellmee_Mapa.prototype.activarClickSinArrastrar = function(callback) {
    var quieto = true;

    this._eventosMapa[0] = google.maps.event.addListener(this.mapa, 'mousedown', function() {
        quieto = true;
    });

    this._eventosMapa[1] = google.maps.event.addListener(this.mapa, 'mousemove', function() {
        quieto = false;
    });

    this._eventosMapa[2] = google.maps.event.addListener(this.mapa, 'mouseup', function(eventoRaton) {
        if(quieto) {
            callback(eventoRaton);
        }
    });
};

//Desactiva el evento click que solo funciona cuando no se arrastra
Tellmee_Mapa.prototype.desactivarClickSinArrastrar = function() {
    for(var x in this._eventosMapa)
        google.maps.event.removeListener(this._eventosMapa[x]);
};

//Centra el mapa en una dirección solicitada y muestra un icono animado
Tellmee_Mapa.prototype.localizarDireccion = function(direccion, icono, mensajeError, zoom, callback) {
    var esto = this;
    
    this._geocoder.geocode( { address: direccion }, 
        function(results, status) {
            if(status == google.maps.GeocoderStatus.OK) {
                if(results[0].types[0] == "country") {
                    esto.mapa.fitBounds(results[0].geometry.bounds);
                }
                else {
                    esto.mapa.setCenter(results[0].geometry.location);
                    esto.mapa.setZoom(zoom);
                }
                
                //Creamos la marca
                var marca = new google.maps.Marker({
                    position: results[0].geometry.location, 
                    map: esto.mapa, 
                    title: 'Localizador', 
                    id: 0,
                    icon: icono, 
                    zIndex: 2
                }); 
 
                esto._marcaAnimada(marca, icono, 1, 9, 100, 2, esto);
                
                if(callback) {
                    callback();
                }
            } 
            else {
                alert(mensajeError);
            }
        }
    );
};

//Devuelve el identificador y nombre formateado de un área
//[idArea, nombreArea, getLatitudIzquierdaInferior, getLongitudIzquierdaInferior, getLatitudDerechaSuperior, getLongitudDerechaSuperior]
Tellmee_Mapa.prototype.identificarArea = function(latitud, longitud, callback) {
    this._geocoder.geocode( { latLng: new google.maps.LatLng(latitud, longitud) }, 
        function(results, status) {
            var zona = false;
            
            if(status == google.maps.GeocoderStatus.OK) {
                var areaAnterior = 0;

                for(var x=0,len=results.length;x<len;x++) {
                    if(enArray(results[x].types, 'locality')) {
                        //Cogemos la localidad
                        zona = [results[x].place_id, results[x].formatted_address, 
                            results[x].geometry.bounds.getSouthWest().lat(), results[x].geometry.bounds.getSouthWest().lng(),
                            results[x].geometry.bounds.getNorthEast().lat(), results[x].geometry.bounds.getNorthEast().lng()
                        ];
                        break;
                    }
                    else { 
                        var indice = indiceDeParcial(results[x].types, 'administrative_area_level');
                        if(indice != -1) {
                            var area = results[x].types[indice].split('_');
                            if(area[area.length - 1] > areaAnterior) {
                                //Cogemos el área administrativa más baja
                                areaAnterior = area;
                                zona = [results[x].place_id, results[x].formatted_address, 
                                    results[x].geometry.bounds.getSouthWest().lat(), results[x].geometry.bounds.getSouthWest().lng(),
                                    results[x].geometry.bounds.getNorthEast().lat(), results[x].geometry.bounds.getNorthEast().lng()
                                ];
                            }
                        }
                    }
                }
            } 

            callback(zona);
        }
    );
};

//Cambia el icono de una marca de manera sucesiva
Tellmee_Mapa.prototype._marcaAnimada = function(marca, imagen, inicio, fin, retardo, repeticion, esto) { 
    var imagenArray = imagen.split('.'), inicioAbsoluto = arguments[7];

    if(imagenArray[imagenArray.length-2] != inicio) {
        //Ponemos la nueva imagen
        imagenArray[imagenArray.length-2] = inicio;
        imagen = imagenArray.join('.');

        marca.setIcon(imagen);
    }
    else
        inicioAbsoluto = inicio;
    
    inicio++;
    
    if(imagenArray[imagenArray.length-2] < fin)
        setTimeout(function() { esto._marcaAnimada(marca, imagen, inicio, fin, retardo, repeticion, esto, inicioAbsoluto); }, retardo);
    else
        if(repeticion > 1) {
            repeticion--;
            inicio = inicioAbsoluto;
            setTimeout(function() { esto._marcaAnimada(marca, imagen, inicio, fin, retardo, repeticion, esto, inicioAbsoluto); }, retardo);
        }
        else
            marca.setMap(null);
};

//Devuelve el zoom actual del mapa
Tellmee_Mapa.prototype.obtenerZoom = function() {
    return this.mapa.getZoom();
};

//Añade un evento especial de mapa
Tellmee_Mapa.prototype.anhadirEvento = function(evento, eventoCallback, unaVez) {
    if(unaVez != undefined)
        google.maps.event.addListenerOnce(this.mapa, evento, eventoCallback);
    else
        google.maps.event.addListener(this.mapa, evento, eventoCallback);
};

//Cambia el tipo de mapa
//Tipo es el nombre de la constante de MapTypeId
Tellmee_Mapa.prototype.cambiarTipo = function(tipo) {
    if(tipo == undefined) {
        //Si no se indica tipo cambiamos de mapa a híbrido y viceversa (por defecto va a mapa)
        if(this.mapa.getMapTypeId() != google.maps.MapTypeId.ROADMAP)
            this.mapa.setMapTypeId(google.maps.MapTypeId.ROADMAP);
        else
            this.mapa.setMapTypeId(google.maps.MapTypeId.HYBRID);
    }
    else
        this.mapa.setMapTypeId(google.maps.MapTypeId[tipo]);
};

//Centra el mapa en la posición deseada
Tellmee_Mapa.prototype.centrarMapa = function(latitud, longitud) {
    this.mapa.setCenter(new google.maps.LatLng(latitud, longitud));
};

//Posiciona el mapa para que se vea un área determinada
Tellmee_Mapa.prototype.centrarVista = function(latitudIzquierdaInferior, longitudIzquierdaInferior, 
        latitudDerechaSuperior, longitudDerechaSuperior) {
    this.mapa.fitBounds(new google.maps.LatLngBounds(
            new google.maps.LatLng(latitudIzquierdaInferior, longitudIzquierdaInferior), 
            new google.maps.LatLng(latitudDerechaSuperior, longitudDerechaSuperior)));
};

//Cambia el zoom del mapa
Tellmee_Mapa.prototype.cambiarZoom = function(zoom) {
    this.mapa.setZoom(zoom);
};




////////Objeto que representa una capa llena de marcadores
//Nota: en todas las capas, al añadir un elemento nuevo hay que llamar a mostrar para que se actualice. Al eliminar un elemento hay que llamar a ocultar y después a mostrar para que se actualice
function Tellmee_Capa(iconoGrupo, alcanceGrupos) {
    this.marcadores = new Array();
    this.grupos = new Array();
    this.marcadoresSueltos = new Array();
    this.iconoGrupo = iconoGrupo; //Si es una url se le añade el parámetro cantidad y al generar el grupo la cantidad
    this.iconoUrl = '';
    if(iconoGrupo !== undefined && iconoGrupo.indexOf('http') === 0) {
        if(iconoGrupo.indexOf('?') !== -1) {
            this.iconoUrl = '&';
        }
        else {
            this.iconoUrl = '?';
        }

        this.iconoUrl += 'cantidad=';
    }
    
    this.alcanceGrupos = alcanceGrupos;
}

//Añade un marcador a la capa, extra: { grupo, usuario, polilinea, categoria, descargas, descripcion, puntosPositivos, puntosNegativos ...} (los metadatos)
Tellmee_Capa.prototype.anhadirMarcador = function(id, latitud, longitud, icono, eventoCallback, extra) {
    var marcador = this._crearMarcador(id, latitud, longitud, icono, eventoCallback, extra);
    
    this.marcadores.push(marcador);
    
    return marcador;
};

//Crea un nuevo marcador
Tellmee_Capa.prototype._crearMarcador = function(id, latitud, longitud, icono, eventoCallback, extra) {
    var posicion = new google.maps.LatLng(latitud, longitud);
    
    var marcador = new google.maps.Marker({
        position: posicion, 
        map: null, 
        id: id,
        icon: icono, 
        zIndex: 1
    });
    
    if(extra != undefined)
        for(var x in extra)
            marcador[x]=extra[x];
    
    if(eventoCallback != undefined)
        google.maps.event.addListener(marcador, 'click', eventoCallback);    
    
    return marcador;
};

//Añade un evento a un marcador
Tellmee_Capa.prototype.anhadirEvento = function(id, evento, eventoCallback, unaVez) {
    var indice = this._obtenerIndice(id);
    
    if(unaVez != undefined)
        google.maps.event.addListenerOnce(this.marcadores[indice], evento, eventoCallback);
    else
        google.maps.event.addListener(this.marcadores[indice], evento, eventoCallback);
};

//Elimina un marcador de la capa
Tellmee_Capa.prototype.eliminarMarcador = function(id) {
    var indice = this._obtenerIndice(id);
    
    if(indice !== false)
        this.marcadores.splice(indice, 1);
};

//Devuelve un marcador por su id
Tellmee_Capa.prototype.obtenerMarcador = function(id) {
    var indice = this._obtenerIndice(id), resultado = false;
    
    if(indice !== false)
        resultado = this.marcadores[indice];
    
    return resultado;
};

//Devuelve el índice de un marcador
Tellmee_Capa.prototype._obtenerIndice = function(id) {
    var resultado = false;
    
    for(var x = 0;x < this.marcadores.length; x++) {
        if(this.marcadores[x].get('id') == id) {
            resultado = x;
            break;
        }
    }
    
    return resultado;
};

//Crea los grupos y los marcadores sueltos, la más rápida (Dentro del marcador del grupo están sus agrupados)
Tellmee_Capa.prototype.generarGruposRadial = function(zoom, eventoCallback) {
    var marcadoresTemp = new Array(), dist, alcanceCalculado = this.alcanceGrupos/Math.pow(2, zoom-1), 
        longitud, indiceMayor, elementosGrupo = new Array(), marcadoresTemp2 = new Array();

    //Calculamos la distancia de los marcadores a un punto 0,0
    for(var x = 0, l = this.marcadores.length; x < l; x++) {
        dist = Math.sqrt(Math.pow(this.marcadores[x].getPosition().lng(), 2) + Math.pow(this.marcadores[x].getPosition().lat(), 2));
        marcadoresTemp.push({ distancia: dist, marcador: this.marcadores[x] });
    }
    longitud = marcadoresTemp.length;
    
    //Ahora los ordenamos por esa distancia
    marcadoresTemp = marcadoresTemp.sort(function(a, b) {
        return a.distancia - b.distancia;
    });

    //Ahora comprobamos si los que entran en el rango están al alcance
    this.marcadoresSueltos = new Array();
    this.grupos = new Array();
    
    if(longitud == 1)
        this.marcadoresSueltos.push(marcadoresTemp[0].marcador);
    else
        while(longitud > 0) {
            indiceMayor = ultimoIndiceDe(marcadoresTemp, marcadoresTemp[0].distancia + alcanceCalculado, marcadoresTemp[0].distancia, 'distancia');
            
            for(var y=1;y <= indiceMayor;y++) {
                if(this._elementosProximos(marcadoresTemp[0].marcador.getPosition().lat(), 
                        marcadoresTemp[0].marcador.getPosition().lng(), marcadoresTemp[y].marcador.getPosition().lat(), 
                        marcadoresTemp[y].marcador.getPosition().lng(), zoom)) {
                     //Acumulamos los marcadores de un mismo grupo
                     elementosGrupo.push(marcadoresTemp[y].marcador);
                }
            }

            //Creamos el grupo de ser necesario
            if(elementosGrupo.length > 0) {
                elementosGrupo.unshift(marcadoresTemp[0].marcador);
                
                //Añadimos el parámetro de ser necesario
                var icono = this.iconoGrupo;
                if(this.iconoUrl) {
                    icono += this.iconoUrl + elementosGrupo.length;
                }
                
                this.grupos.push(this._crearMarcador(this.grupos.length, marcadoresTemp[0].marcador.getPosition().lat(), 
                    marcadoresTemp[0].marcador.getPosition().lng(), icono, eventoCallback, { agrupados: elementosGrupo, tipo: 'grupo' }));
            }
            else {
                //Es un marcador suelto
                this.marcadoresSueltos.push(marcadoresTemp[0].marcador);
            }

            //Reinicializamos
            for(var x = 1, l = marcadoresTemp.length;x < l;x++) {
                if(!this.enArray(elementosGrupo, marcadoresTemp[x].marcador.id))
                    marcadoresTemp2.push(marcadoresTemp[x]);
            }
            marcadoresTemp = marcadoresTemp2;
            longitud = marcadoresTemp.length;
            marcadoresTemp2 = new Array();
            elementosGrupo = new Array();
        }
};

//Crea los grupos y los marcadores sueltos, versión mejorada (Dentro del marcador del grupo están sus agrupados)
Tellmee_Capa.prototype.generarGruposEnCruz = function(zoom, eventoCallback) {
    var arrayLo = new Array(), arrayLa = new Array(), marcadoresTemp = copiarArray(this.marcadores), 
        longitud = marcadoresTemp.length, indicesLa, indicesLo, alcanceCalculado = this.alcanceGrupos/Math.pow(2, zoom-1), 
        elementosGrupo, marcadoresTemp2 = new Array();
    
    //Generamos dos arrays
    for(var x = 0, l = this.marcadores.length;x < l; x++) {
        arrayLa.push({ latitud: this.marcadores[x].getPosition().lat(), marca: this.marcadores[x] });
        arrayLo.push({ longitud: this.marcadores[x].getPosition().lng(), marca: this.marcadores[x] });
    }
    //Ordenamos uno por longitud y el otro por latitud
    arrayLa.sort(function(a, b) {
        return a.latitud - b.latitud;
    });
    arrayLo.sort(function(a, b) {
        return a.longitud - b.longitud;
    });

    //Ahora vamos punto por punto
    if(longitud == 1)
        this.marcadoresSueltos.push(marcadoresTemp[0]);
    else
        while(longitud > 0) {
            //Calculamos los límites
            indicesLa = { 
                menor: primerIndiceDe(arrayLa, marcadoresTemp[0].getPosition().lat() - alcanceCalculado, marcadoresTemp[0].getPosition().lat(), 'latitud'),
                mayor: ultimoIndiceDe(arrayLa, marcadoresTemp[0].getPosition().lat() + alcanceCalculado, marcadoresTemp[0].getPosition().lat(), 'latitud')
            };
            indicesLo = { 
                menor: primerIndiceDe(arrayLo, marcadoresTemp[0].getPosition().lng() - alcanceCalculado, marcadoresTemp[0].getPosition().lng(), 'longitud'),
                mayor: ultimoIndiceDe(arrayLo, marcadoresTemp[0].getPosition().lng() + alcanceCalculado, marcadoresTemp[0].getPosition().lng(), 'longitud')
            };

            //Ahora la intersección
            elementosGrupo = interseccion(arrayLa.slice(indicesLa.menor, indicesLa.mayor + 1), arrayLo.slice(indicesLo.menor, indicesLo.mayor + 1));

            if(elementosGrupo.length > 1) {
                //Añadimos el parámetro de ser necesario
                var icono = this.iconoGrupo;
                if(this.iconoUrl) {
                    icono += this.iconoUrl + elementosGrupo.length;
                }
                
                this.grupos.push(this._crearMarcador(this.grupos.length, marcadoresTemp[0].getPosition().lat(), 
                    marcadoresTemp[0].getPosition().lng(), icono, eventoCallback, { agrupados: elementosGrupo, tipo: 'grupo' }));
                }
            else
                this.marcadoresSueltos.push(marcadoresTemp[0]);

            //Reinicializamos
            for(var x = 1, l = marcadoresTemp.length;x < l;x++) {
                if(!this.enArray(elementosGrupo, marcadoresTemp[x].id))
                    marcadoresTemp2.push(marcadoresTemp[x]);
            }
            marcadoresTemp = marcadoresTemp2;
            longitud = marcadoresTemp.length;
            marcadoresTemp2 = new Array();
        }
};

//Crea los grupos y los marcadores sueltos (Dentro del marcador del grupo están sus agrupados)
Tellmee_Capa.prototype.generarGrupos = function(zoom, eventoCallback) {
    var marcadoresTemp = copiarArray(this.marcadores), longitud = marcadoresTemp.length, 
        elementosGrupo = new Array(), marcadoresTemp2 = new Array();
        
    this.marcadoresSueltos = new Array();
    this.grupos = new Array();
    
    if(longitud == 1)
        this.marcadoresSueltos.push(marcadoresTemp[0]);
    else
        while(longitud > 0) {
            for(var y=1;y < longitud;y++) {
                if(this._elementosProximos(marcadoresTemp[0].getPosition().lat(), 
                        marcadoresTemp[0].getPosition().lng(), marcadoresTemp[y].getPosition().lat(), 
                        marcadoresTemp[y].getPosition().lng(), zoom)) {
                     //Acumulamos los marcadores de un mismo grupo
                     elementosGrupo.push(marcadoresTemp[y]);
                }
                else
                    marcadoresTemp2.push(marcadoresTemp[y]);
            }

            //Creamos el grupo de ser necesario
            if(elementosGrupo.length > 0) {
                //Añadimos el parámetro de ser necesario
                var icono = this.iconoGrupo;
                if(this.iconoUrl) {
                    icono += this.iconoUrl + elementosGrupo.length;
                }
                
                elementosGrupo.unshift(marcadoresTemp[0]);
                this.grupos.push(this._crearMarcador(this.grupos.length, marcadoresTemp[0].getPosition().lat(), 
                    marcadoresTemp[0].getPosition().lng(), icono, eventoCallback, { agrupados: elementosGrupo, tipo: 'grupo' }));
            }
            else {
                //Es un marcador suelto
                this.marcadoresSueltos.push(marcadoresTemp[0]);
            }

            //Reinicializamos
            marcadoresTemp = marcadoresTemp2;
            longitud = marcadoresTemp.length;
            marcadoresTemp2 = new Array();
            elementosGrupo = new Array();
        }
};

//Busca un elemento en un array por su id
Tellmee_Capa.prototype.enArray = function(array, valor) {
    var resultado = false;
    
    for(var x = 0;x < array.length;x++) {
        if(array[x].id == valor) {
            resultado = true;
            break;
        }
    }
    
    return resultado;
};

//Decide si dos posiciones están lo bastante cerca como para formar un grupo
Tellmee_Capa.prototype._elementosProximos = function(latitud1, longitud1, latitud2, longitud2, zoom) {
    var difLa = 0, difLo = 0, alcanceCalculado = this.alcanceGrupos/Math.pow(2, zoom-1), resultado;
    
    difLa = Math.abs(latitud1 - latitud2);
    difLo = Math.abs(longitud1 - longitud2);
    
    if(difLa < alcanceCalculado && difLo < alcanceCalculado)
        resultado = true;
    else
        resultado = false;
    
    return resultado;
};

//Coloca los marcadores en un mapa, usando los grupos (1º habría que crearlos) o todos sueltos
Tellmee_Capa.prototype.mostrar = function(agrupada, mapa) {
    if(agrupada) {
        for(var x = 0;x < this.grupos.length; x++)
            this.grupos[x].setMap(mapa);
        
        for(var x = 0;x < this.marcadoresSueltos.length; x++)
            this.marcadoresSueltos[x].setMap(mapa);
    }
    else
        for(var x = 0;x < this.marcadores.length; x++)
            this.marcadores[x].setMap(mapa);
};

//Quita los marcadores del mapa
Tellmee_Capa.prototype.ocultar = function() {
    for(var x = 0;x < this.grupos.length; x++)
        this.grupos[x].setMap(null);
    
    for(var x = 0;x < this.marcadores.length; x++)
        this.marcadores[x].setMap(null);
};




////////Representa un globo de información
function Tellmee_Globo(mapa) {
    this.marcador = undefined;
    this.globo = new google.maps.InfoWindow();
    this._contenido = undefined;
    this.mapa = mapa;
}

//Abre el globo con los datos actuales
Tellmee_Globo.prototype.abrirGlobo = function() {
    this.globo.setContent(this._contenido);
    this.globo.open(this.mapa, this.marcador);
};

//Modifica los datos del globo
Tellmee_Globo.prototype.modificarGlobo = function(marcador, contenido) {
    //this.cerrarGlobo(); //Hay que cerrar para que se reconstruya correctamente (?)
    this._contenido = contenido;
    this.marcador = marcador;
};

//Cierra el globo
Tellmee_Globo.prototype.cerrarGlobo = function() {
    this.globo.close();
    this.marcador = undefined;
};

Tellmee_Globo.prototype.anhadirEvento = function(evento, callback, unaVez) {
    if(unaVez != undefined)
        google.maps.event.addListenerOnce(this.globo, evento, callback);
    else
        google.maps.event.addListener(this.globo, evento, callback);
};





////////Representa un globo de información
function Tellmee_GloboDecorado(mapa, estilo) {
    this.marcador = undefined;
    this._contenido = undefined;
    this.mapa = mapa;

    var myOptions = {
            boxStyle: estilo
            ,closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
            ,isHidden: false
            ,pane: "floatPane"
            ,enableEventPropagation: false
            ,disableAutoPan: false
            ,infoBoxClearance: new google.maps.Size(1, 1)
            ,pixelOffset: new google.maps.Size(-85, 0)
            ,closeBoxMargin: "10px 2px 2px 2px"
    };

    this.globo = new InfoBox(myOptions);
    
}

//Abre el globo con los datos actuales
Tellmee_GloboDecorado.prototype.abrirGlobo = function() {
    this.globo.setContent(this._contenido);
    this.globo.open(this.mapa, this.marcador);
};

//Modifica los datos del globo
Tellmee_GloboDecorado.prototype.modificarGlobo = function(marcador, contenido) {
    //this.cerrarGlobo(); //Hay que cerrar para que se reconstruya correctamente (?)
    this._contenido = contenido;
    this.marcador = marcador;
};

//Cierra el globo
Tellmee_GloboDecorado.prototype.cerrarGlobo = function() {
    this.globo.close();
    this.marcador = undefined;
};

Tellmee_GloboDecorado.prototype.anhadirEvento = function(evento, callback, unaVez) {
    if(unaVez != undefined)
        google.maps.event.addListenerOnce(this.globo, evento, callback);
    else
        google.maps.event.addListener(this.globo, evento, callback);
};




//TODO: no está muy probada
////////Una capa que contiene polilineas
function Tellmee_CapaPolilineas() {
    this.polilineas = new Array();
}

//TODO: si guardar los markers ocupa mucho se podría guardar sólo el id
//Añade una polilinea a la capa, marcadores (array de markers), extra: { usuario, descripcion, ...} (los metadatos)
Tellmee_CapaPolilineas.prototype.anhadirPolilinea = function(id, marcadores, eventoCallback, extra) {
    var polilinea = this._crearPolilinea(id, marcadores, eventoCallback, extra);
    
    this.polilineas.push(polilinea);
};

//Crea una polilinea
Tellmee_CapaPolilineas.prototype._crearPolilinea = function(id, marcadores, eventoCallback, extra) {
    if(marcadores instanceof Array) {
        //Generamos la polilinea 
        var posiciones = new google.maps.MVCArray();
        for(var x in marcadores) {
            posiciones.push(marcadores[x].getPosition());
        }
        
        var polilinea = new google.maps.Polyline({
            id: id,
            marcadores: marcadores,
            map: null,
            path: posiciones,
            zIndex: 1
        });

        if(extra != undefined)
            for(var x in extra)
                polilinea[x]=extra[x];

        if(eventoCallback != undefined)
            google.maps.event.addListener(polilinea, 'click', eventoCallback);    
    }
    
    return polilinea;
};

//Añade un marcador al final de la polilinea indicada por el id
Tellmee_CapaPolilineas.prototype.ampliarPolilinea = function(id, marcador) {
    var indice = this._obtenerIndice(id);
    
    if(indice !== false) {
        var posiciones = this.polilineas[indice].getPath(), marcadores = this.polilineas[indice].get('marcadores');
        posiciones.push(marcador.getPosition());
        marcadores.push(marcador);
    }
};

//Elimina la última entrada de la polilinea
Tellmee_CapaPolilineas.prototype.reducirPolilinea = function(id) {
    var indice = this._obtenerIndice(id);
    
    if(indice !== false) {
        var posiciones = this.polilineas[indice].getPath(), marcadores = this.polilineas[indice].get('marcadores');
        posiciones.pop();
        marcadores.pop();
    }
};

//Añade un evento a una polilinea
Tellmee_CapaPolilineas.prototype.anhadirEvento = function(id, evento, eventoCallback, unaVez) {
    var indice = this._obtenerIndice(id);
    
    if(unaVez != undefined)
        google.maps.event.addListenerOnce(this.polilineas[indice], evento, eventoCallback);
    else
        google.maps.event.addListener(this.polilineas[indice], evento, eventoCallback);
};

//Elimina una polilinea de la capa
Tellmee_CapaPolilineas.prototype.eliminarPolilinea = function(id) {
    var indice = this._obtenerIndice(id);
    
    if(indice !== false)
        this.polilineas.splice(indice, 1);
};

//Devuelve una polilinea por su id
Tellmee_CapaPolilineas.prototype.obtenerPolilinea = function(id) {
    var indice = this._obtenerIndice(id), resultado = false;
    
    if(indice !== false)
        resultado = this.polilineas[indice];
    
    return resultado;
};

//Devuelve el índice de una polilinea
Tellmee_CapaPolilineas.prototype._obtenerIndice = function(id) {
    var resultado = false;
    
    for(var x = 0;x < this.polilineas.length; x++) {
        if(this.polilineas[x].get('id') == id) {
            resultado = x;
            break;
        }
    }
    
    return resultado;
};

//Coloca las polilineas en un mapa
Tellmee_CapaPolilineas.prototype.mostrar = function(agrupada, mapa) {
    for(var x = 0;x < this.polilineas.length; x++)
        this.polilineas[x].setMap(mapa);
};

//Quita las polilineas del mapa
Tellmee_CapaPolilineas.prototype.ocultar = function() {
    for(var x = 0;x < this.polilineas.length; x++)
        this.polilineas[x].setMap(null);
};




////////Una capa que contiene rutas
function Tellmee_CapaRutas() {
    this.rutas = new Array();
    this._direccionador = new google.maps.DirectionsService();
}

//TODO: si guardar los markers ocupa mucho se podría guardar sólo el id
//Añade una ruta a la capa, marcadores (array de markers, al menos 2 y como máximo 10), callback se ejecuta cuando se termina de crear la ruta
//Es asíncrono
Tellmee_CapaRutas.prototype.anhadirRuta = function(id, marcadores, callback) {
    if(marcadores.length >= 2 && marcadores.length < 10) {
        var arrayIntermedios = new Array();
        
        if(marcadores.length > 2) {
            var puntosIntermedios = marcadores.slice(1, marcadores.length - 1);

            for(var x in puntosIntermedios) {
                arrayIntermedios.push({location: puntosIntermedios[x].getPosition()});
            }
        }

        var request = {
            origin: marcadores[0].getPosition(),
            destination: marcadores[marcadores.length - 1].getPosition(),
            travelMode: google.maps.TravelMode.WALKING,
            waypoints: arrayIntermedios //Un máximo de 8
        }, esto = this;

        this._direccionador.route(request, function(response, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                var rendererOptions = {
                    map: null,
                    preserveViewport: true
                };

                var renderizador = new google.maps.DirectionsRenderer(rendererOptions);
                renderizador.setDirections(response);
                renderizador.marcadores = marcadores;
                renderizador.id = id;   

                esto.rutas.push(renderizador);
                
                //Ejecutamos el evento
                if(callback != undefined)
                    callback();
            }
        });
    }
};

//Añade un marcador al final de la ruta indicada por el id
//Es asíncrono
Tellmee_CapaRutas.prototype.ampliarRuta = function(idRuta, marcador, callback) {
    var indice = this._obtenerIndice(idRuta);
    
    if(indice !== false) {
        var marcadores = this.rutas[indice].marcadores;
        marcadores.push(marcador);
        
        this.eliminarRuta(idRuta);
        this.anhadirRuta(idRuta, marcadores, callback);
    }
};

//Elimina la última entrada de la ruta
//Es asíncrono
Tellmee_CapaRutas.prototype.reducirRuta = function(idRuta, callback) {
    var indice = this._obtenerIndice(idRuta);
    
    if(indice !== false) {
        var marcadores = this.rutas[indice].marcadores;
        marcadores.pop();
        
        this.eliminarRuta(idRuta);
        this.anhadirRuta(idRuta, marcadores, callback);
    }
};

//Elimina una ruta de la capa
Tellmee_CapaRutas.prototype.eliminarRuta = function(idRuta) {
    var indice = this._obtenerIndice(idRuta);
    
    if(indice !== false)
        this.rutas.splice(indice, 1);
};

//Devuelve una ruta por su id
Tellmee_CapaRutas.prototype.obtenerRuta = function(idRuta) {
    var indice = this._obtenerIndice(idRuta), resultado = false;
    
    if(indice !== false)
        resultado = this.rutas[indice];
    
    return resultado;
};

//Devuelve el índice de una ruta
Tellmee_CapaRutas.prototype._obtenerIndice = function(idRuta) {
    var resultado = false;
    
    for(var x = 0;x < this.rutas.length; x++) {
        if(this.rutas[x].id == idRuta) {
            resultado = x;
            break;
        }
    }
    
    return resultado;
};

//Coloca las rutas en un mapa
Tellmee_CapaRutas.prototype.mostrar = function(agrupado, mapa) {
    for(var x = 0;x < this.rutas.length; x++)
        this.rutas[x].setMap(mapa);
};

//Quita las rutas del mapa
Tellmee_CapaRutas.prototype.ocultar = function() {
    for(var x = 0;x < this.rutas.length; x++)
        this.rutas[x].setMap(null);
};



////Icono para el mapa
//Si se deja a 0 ancho o alto no se reescala.
function Tellmee_Icono(url, ancho, alto) {
    if(ancho > 0 && alto > 0) {
        this.scaledSize = new google.maps.Size(ancho, alto);
    }
    this.url = url;
}

Tellmee_Icono.prototype.toString = function() {
    return this.url;
};