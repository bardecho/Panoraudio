/////Para utilizar y crear una cola de reproducción de audios
function ColaReproduccion() {
    this._audios = new Array();
    this._actual = undefined;
}

//Poner un audio al final de la cola y devuelve su posición (le quita la extensión a la ruta)
ColaReproduccion.prototype.anhadirAudio = function(ruta, descripcion, idMarca) {
    ruta = ruta.substring(0, ruta.length - 3);
    return this._audios.push([ruta, descripcion, idMarca]) - 1;
}

//Eliminar un audio de la cola
ColaReproduccion.prototype.eliminarAudio = function(posicion) {
    this._audios.splice(posicion, 1);
    if(posicion < this._actual)
        this._actual--;
}

//Obtener un audio de cualquier posición
ColaReproduccion.prototype.obtenerAudio = function(posicion) {
    this._actual = posicion;
    return this._audios[posicion];
}

//Obtener el audio siguiente a reproducir
ColaReproduccion.prototype.siguienteAudio = function() {
    if(this._actual == undefined)
        this._actual = 0;
    else
        this._actual++;
    
    return this._audios[this._actual];
}

//Devuelve la posicion del audio actual
ColaReproduccion.prototype.obtenerActual = function() {
    return this._actual;
}

ColaReproduccion.prototype.obtenerLista = function() {
    return this._audios;
}