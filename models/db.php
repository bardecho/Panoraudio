<?php
//Warning: Querys must be escaped before send
/**
 * Basic model.
 *
 * @author bardecho
 */
class DB {
    private $connection;
    const SERVER_GONE=2006;
    const SERVER_LOST=2013;

    public function __construct() {
        $this->connect();
    }

    /**
     * Creates a connection.
     */
    private function connect() {
        $this->connection=mysqli_init();
        if(@$this->connection->real_connect('localhost', '', '', 'panoraudio')) {
            $this->connection->set_charset('utf8');
        }
        else {
            throw new Exception($this->connection->error, $this->connection->errno);
        }
    }

    /**
     * Escapes data.
     * @param array $data Array of data to escape.
     * @return array Array with escaped data.
     */
    public function escapeData($data) {
        try {
            foreach((array)$data as $key => $value) {
                $escaped[$key]=$this->connection->real_escape_string($value);
		$escaped[$key]=addcslashes($escaped[$key] ,'-');
            }
        }
        catch(Exception $e) { throw $e; }

        return $escaped;
    }

    //Consultas select
    //$second controla los reintentos, si es true no hay reintentos
    //'datos' devuelve los datos solicitados
    //'filas' devuelve el número de filas encontradas
    //Excepciones: Errores SQL
    /**
     * Executes select querys.
     * @param string $query The mysql query to execute.
     * @param boolean $second If set to TRUE, don't try to reconnect on fail.
     * @return array 'rows' => numbers of rows returned, 'data' => array with obtained data.
     */
    public function obtainData($query, $second=false) {
        try {
            if($this->connection->real_query($query)) {
                $result=$this->connection->use_result();
                $tempData=$result->fetch_assoc();
                while($tempData) {
                    $data['data'][]=$tempData;
                    $tempData=$result->fetch_assoc();
                }
                $data['rows']=$result->num_rows;
                $result->free();
            }
            else {
                //Si es un error por desconexión, conectamos e intentamos una vez más
                if(($this->connection->errno == self::SERVER_GONE || $this->connection->errno == self::SERVER_LOST) && !$second) {
                    $this->connect();
                    return $this->obtainData($query, true);
                }
                else {
                    throw new Exception($this->connection->error, $this->connection->errno);
                }
            }
        }
        catch(Exception $e) { throw $e; }

        return $data;
    }

    //Excepciones: Errores SQL
    /**
     * Executes insert, delete, update.
     * @param string $query The mysql query to execute.
     * @param boolean $second If set to TRUE, don't try to reconnect on fail.
     * @return array 'rows' => numbers of rows affected, insert_id => auto generated id.
     */
    public function alterData($query, $second=false) {
        try {
            if($this->connection->real_query($query)) {
                $data['rows']=$this->connection->affected_rows;
                $data['insert_id']=$this->connection->insert_id;
            }
            else {
                //Si es un error por desconexión, conectamos e intentamos una vez más
                if(($this->connection->errno == self::SERVER_GONE || $this->connection->errno == self::SERVER_LOST) && !$second) {
                    $this->connect();
                    return $this->alterData($query, true);
                }
                else {
                    throw new Exception($this->connection->error, $this->connection->errno);
                }
            }
        }
        catch(Exception $e) { throw $e; }

        return $data;
    }

    public function __destruct() {
        $this->connection->close();
    }
}
