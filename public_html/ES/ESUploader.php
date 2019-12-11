<?php

require_once dirname(__FILE__) . '/../ISaveable.php';

class ESUploader implements ISaveable {

    private $ch;

    function __construct() {

        $this->conf=json_decode(file_get_contents(dirname(__FILE__) . "/conf/es.json"),true);
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
        // var_dump($object);
//        curl_setopt($this->ch, CURLOPT_URL, $this->conf['host'] . "/" . $this->conf['indexes'][$classname]['index'] . "/" . $this->conf['indexes'][$classname]['type'] . "/" . md5($object->timestamp));
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->ch, CURLOPT_URL, $this->conf['host'] . "/" . $this->conf['indexes'][$classname]['index'] . "/_doc/" . md5($object->timestamp));
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($object));
        $response = curl_exec($this->ch);
        echo $response;
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

        foreach ($this->conf['indexes'] as $classname => $values) {
            curl_setopt($ch, CURLOPT_URL, $this->conf['host'] . "/${values['index']}");
            echo "Creating index " . curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) . "\n";
            echo curl_exec($ch) . "\n";

            curl_setopt($ch, CURLOPT_URL, $this->conf['host'] . "/${values['index']}/_mapping");
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

        foreach ($this->conf['indexes'] as $classname => $values) {
            curl_setopt($ch, CURLOPT_URL, $this->conf['host'] . "/${values['index']}");
            echo "Dropping index " . curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) . "\n";
            echo curl_exec($ch) . "\n";
        }

        curl_close($ch);
    }

}
