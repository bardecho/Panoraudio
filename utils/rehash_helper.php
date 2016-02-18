<?php
/**
* Created by Bardecho.
* Do more secure hash using a random or given key.
* @param String $data Information to be hashed.
* @param String $key Hashing key, if not present it generates a random one.
* @return String 104 caracter long string (using auto generated key) beginning with the key(64), then the hash(40).
*/
function reHash($data, $key=NULL) {
    if($key === NULL) {
        mt_srand(microtime(true)*10000);
        for($i=0;$i < 64;$i++) $key.=dechex(mt_rand(0, 15));
    }

    return $key.sha1(sha1($key.$data));
}
