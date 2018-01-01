<?php

require_once dirname(__FILE__) . '/../ISaveable.php';

class ESUploader implements ISaveable {

    private $ch;
    private $host;

    private $conf = array(
        "Reading" => array(
            "index" => "energy_reading",
            "type" => "reading",
            "mapping" => "conf/reading.json"
        ),
        "Usage" => array(
            "index" => "energy_usage",
            "type" => "usage",
            "mapping" => "conf/usage.json"
        )
    );

    function __construct() {

        $conf=json_decode(file_get_contents(dirname(__FILE__) . "/conf/es.json"),true);
        $this->host=$conf['host'];
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Connection: Keep-Alive'
        ));
    }

    function __destruct() {
        curl_close($this->ch);
    }

    public function save($object) {
        $classname = get_class($object);
        curl_setopt($this->ch, CURLOPT_URL, $this->host . "/" . $this->conf[$classname]['index'] . "/" . $this->conf[$classname]['type'] . "/" . md5($object->timestamp));
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($object));
        echo "Creating entry " . curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL) . "\n";
        $response = curl_exec($this->ch);
        if (!$response) {
            echo $response . "\n";
        }
    }

    public function setup() {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Connection: Keep-Alive'
        ));

        foreach ($this->conf as $classname => $values) {
            curl_setopt($ch, CURLOPT_URL, $this->host . "/${values['index']}");
            echo "Creating index " . curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) . "\n";
            echo curl_exec($ch) . "\n";

            curl_setopt($ch, CURLOPT_URL, $this->host . "/${values['index']}/_mapping/${values['type']}");
            echo "Creating mapping " . curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) . "\n";
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents(dirname(__FILE__) . "/${values['mapping']}"));
            echo curl_exec($ch) . "\n";
            curl_setopt($ch, CURLOPT_POSTFIELDS, "");
        }

        curl_close($ch);
    }

    public function teardown() {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Keep-Alive'));

        foreach ($this->conf as $classname => $values) {
            curl_setopt($ch, CURLOPT_URL, $this->host . "/${values['index']}");
            echo "Dropping index " . curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) . "\n";
            echo curl_exec($ch) . "\n";
        }

        curl_close($ch);
    }

}
