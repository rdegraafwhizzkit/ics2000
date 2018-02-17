<?php

require_once dirname(__FILE__) . '/../ISaveable.php';

class MySQLUploader implements ISaveable {

    private $mysqli;

    function __construct() {

        $this->conf=json_decode(file_get_contents(dirname(__FILE__) . "/conf/mysql.json"),true);

        $this->mysqli = new mysqli($this->conf['host'], $this->conf['username'], $this->conf['password'], $this->conf['database'],$this->conf['port']);
        if ($this->mysqli->connect_errno) {
            $this->handle_error();
        }
    }

    function __destruct() {
        mysqli_close($this->mysqli);
    }

    public function save($object) {
        $classname = get_class($object);
        $sql = "insert into " . $this->conf['tables'][$classname]['table'];
        $columns = "(";
        $values = "(";
        $duplicate = "";

        if(array_key_exists('mapping',$this->conf['tables'][$classname])) {
            $mapping=$this->build_mapping($this->conf['tables'][$classname]['mapping']);
            $array=array();
            foreach ($mapping as $key => $target) {
                eval("\$value=\$object->$target;");
                $array[$key]=$value;
            }
            $object=new $classname($array);
        }

        foreach ($object as $key => $value) {
            $columns .= ($columns == "(" ? "" : ",") . $key;
            $values .= ($values == "(" ? "" : ",") . (is_numeric($value) ? $value : "'${value}'");
            $duplicate .= ($duplicate == ""?"":",")."${key}=". (is_numeric($value) ? $value : "'${value}'");
        }

        $sql .= $columns . ") values " . $values . ") on duplicate key update ".$duplicate;
        if (!$this->mysqli->query($sql)) {
            $this->handle_error();
        }
    }

    public function setup() {
        foreach ($this->conf['tables'] as $classname => $values) {
            echo "Creating table ${values['table']}\n";
            if (!$this->mysqli->query("create table ${values['table']} ".file_get_contents(dirname(__FILE__) . "/${values['sql']}"))) {
                $this->handle_error();
            }
        }
    }

    public function teardown() {
        foreach ($this->conf['tables'] as $classname => $values) {
            echo "Dropping table ${values['table']}\n";
            if (!$this->mysqli->query("drop table ${values['table']}")) {
                $this->handle_error();
            }
        }
    }
    
    private function handle_error() {
        echo "Statement failed: (" . $this->mysqli->errno . ") " . $this->mysqli->error . "\n";
    }

    private function build_mapping($values) {
        $mapping=array();
        foreach($values as $value) {
            foreach($value as $k => $v ) {
                $mapping[$k]=$v;
            }
        }        
        return $mapping;
    }
}
