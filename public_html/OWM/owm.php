<?php

class OWM {

    private $ch;
    private $conf;

    function __construct() {

        $this->conf = json_decode(file_get_contents(dirname(__FILE__) . "/conf/owm.json"), true);
        date_default_timezone_set($this->conf['timezone']);

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Connection: Keep-Alive'
        ));
    }

    function __destruct() {
        curl_close($this->ch);
    }

    function get() {
        $url = sprintf(
                "%s?id=%s&appid=%s&units=%s", 
                $this->conf['url'], 
                $this->conf['id'], 
                $this->conf['appid'], 
                $this->conf['units']
        );
        curl_setopt($this->ch, CURLOPT_URL, $url);
        return curl_exec($this->ch);
    }
}
