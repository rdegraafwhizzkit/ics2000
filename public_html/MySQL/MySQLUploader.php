<?php

require_once dirname(__FILE__) . '/../ISaveable.php';

class MySQLUploader implements ISaveable {

    private $host;
    private $port;
    private $username;
    private $password;
    private $database;
    private $mysqli;

    private $conf = array(
        "Reading" => array(
            "table" => "energy_reading",
            "sql" => "conf/reading.sql"
        ),
        "Usage" => array(
            "table" => "energy_usage",
            "sql" => "conf/usage.sql"
        )
    );

    function __construct() {

        $conf=json_decode(file_get_contents(dirname(__FILE__) . "/conf/mysql.json"),true);
        $this->host=$conf['host'];
        $this->port=$conf['port'];
        $this->username=$conf['username'];
        $this->password=$conf['password'];
        $this->database=$conf['database'];

        $this->mysqli = new mysqli($this->host, $this->username, $this->password, $this->database,$this->port);
        if ($this->mysqli->connect_errno) {
            $this->handle_error();
        }
    }

    function __destruct() {
        mysqli_close($this->mysqli);
    }

    public function save($object) {
        $classname = get_class($object);
        $sql = "insert into " . $this->conf[$classname]['table'];
        $columns = "(";
        $values = "(";
        $duplicate = "";
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
        foreach ($this->conf as $classname => $values) {
            echo "Creating table ${values['table']}\n";
            if (!$this->mysqli->query("create table ${values['table']} ".file_get_contents(dirname(__FILE__) . "/${values['sql']}"))) {
                $this->handle_error();
            }
        }
    }

    public function teardown() {
        foreach ($this->conf as $classname => $values) {
            echo "Dropping table ${values['table']}\n";
            if (!$this->mysqli->query("drop table ${values['table']}")) {
                $this->handle_error();
            }
        }
    }
    
    private function handle_error() {
        echo "Statement failed: (" . $this->mysqli->errno . ") " . $this->mysqli->error . "\n";
    }

}
