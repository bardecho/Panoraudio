function RutaInterfaz(mapa) {
    this._marcas = new Array();
    this._capaRutas = new Tellmee_CapaRutas();
    this._mapa = mapa;
}

//Añade una marca al final de la ruta
RutaInterfaz.prototype.anhadirMarca = function(marca, iniCallback, finCallback) {
    var esto = this;
    
    //El máximo es 10
    if(this._marcas.length < 10) {
        this._marcas.push(marca);

        //Si tenemos 2 creamos la ruta
        if(this._marcas.length == 2) {
            if(iniCallback != undefined)
                iniCallback();
                
            this._capaRutas.anhadirRuta(1, this._marcas.slice(0), function() {
                esto._capaRutas.mostrar(undefined, esto._mapa);
                if(finCallback != undefined)
                    finCallback();
            });
        }
        else {
            //Si superamos 2 añadimos a la ruta
            if(this._marcas.length > 2) {
                if(iniCallback != undefined)
                    iniCallback();
                
                this._capaRutas.ocultar();
                this._capaRutas.ampliarRuta(1, marca, function() {
                    esto._capaRutas.mostrar(undefined, esto._mapa);
                    if(finCallback != undefined)
                        finCallback();
                });
            }
        }
    }
};

//Elimina la última marca de la ruta
RutaInterfaz.prototype.eliminarMarca = function(iniCallback, finCallback) {
    var esto = this;

    if(this._marcas.length > 0) {
        this._marcas.pop();
    
        //Si tenemos menos de 2 eliminamos la ruta
        if(this._marcas.length < 2) {
            this._capaRutas.ocultar();
            this._capaRutas.eliminarRuta(1);
        }
        else {
            if(iniCallback != undefined)
                iniCallback();
            
            this._capaRutas.ocultar();
            this._capaRutas.reducirRuta(1, function() {
                esto._capaRutas.mostrar(undefined, esto._mapa);
                if(finCallback != undefined)
                    finCallback();
            });
        }
    }
};

//Devuelve la cantidad de marcas actual
RutaInterfaz.prototype.cantidad = function() {
    return this._marcas.length;
};

//Devuelve los ids de los que se compone la ruta
RutaInterfaz.prototype.idsRuta = function() {
    var ids = new Array();
    
    for(var x=0,len=this._marcas.length;x<len;x++) {
        ids.push(this._marcas[x].id);
    }
    
    return ids;
};