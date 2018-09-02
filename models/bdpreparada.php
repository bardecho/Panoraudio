<?php
class BDPreparada {
    const INTEGER='i';
    const DOUBLE='d';
    const STRING='s';
    const BLOB='b';
    private $conexion;
    private $consulta;
    private $parametros;

    /**
     * Clase para utilizar consultas preparadas.
     * @param String $consulta Consulta preparada a generar.
     */
    public function __construct($consulta) {
        $this->conexion=mysqli_init();
        if(@$this->conexion->real_connect('localhost', '', '', 'panoraudio')) {
            $this->conexion->set_charset('utf8');
            $this->consulta=$this->conexion->prepare($consulta);
            if($this->consulta === FALSE) throw new Exception($this->conexion->error, $this->conexion->errno);
        }
        else throw new Exception($this->conexion->error, $this->conexion->errno);
    }

    /**
     * Identifica los parámetros que corresponden a las ? de la consulta.
     * Hay que meterlos todos a la vez y en orden.
     * @param Array $parametros Array con la forma array[nombre del parámetro]=tipo del parámetro.
     * Los tipos son: i -> entero, d -> double, s -> string, b -> blob (se envía en paquetes). Pueden usarse
     * las constantes predefinidas en la clase.
     */
    public function meterParametros($parametros) {
        $variables[0]='';
        foreach($parametros as $nombre => $tipo) {
            $variables[0].=$tipo;
            $variables[]=&$this->parametros[$nombre];
        }
        call_user_func_array(array($this->consulta, 'bind_param'), $variables);
    }

    /**
     * Para dar valor a los parámetros.
     * @param Array $parametros Array con la forma array[nombre del parámetro]=valor del parámetro.
     */
    public function rellenarParametros($parametros) {
        foreach($parametros as $name => $value)
            $this->parametros[$name]=$value;
    }

    /**
     * Ejecuta una consulta de obtención de datos.
     * @return Array Array['filas'] contiene la cantidad de filas devueltas. Array['datos'] los datos obtenidos.
     */
    public function obtenerDatos() {
        if($this->consulta->execute()) {
            $metadata=$this->consulta->result_metadata();
            while(($campo=$metadata->fetch_field()) !== FALSE) {
                $variables[]=&$datos[$campo->name];
            }
            $metadata->free_result();
            call_user_func_array(array($this->consulta, 'bind_result'), $variables);
            $i=0;
            while($this->consulta->fetch() !== NULL) {
                foreach($datos as $nombre => $dato) $resultado['datos'][$i][$nombre]=$dato;
                $i++;
            }
            $resultado['filas']=$i;
            $this->consulta->free_result();
        }
        else throw new Exception($this->consulta->error, $this->consulta->errno);

        return $resultado;
    }

    /**
     * Ejecuta una consulta de acción.
     * @return Array Array['filas'] contiene la cantidad de filas modificadas.
     */
    public function alterarDatos() {
        if($this->consulta->execute())
            $resultado['filas']=$this->consulta->affected_rows;
        else throw new Exception($this->consulta->error, $this->consulta->errno);

        return $resultado;
    }

    public function __destruct() {
        $this->consulta->close();
        $this->conexion->close();
    }
}
