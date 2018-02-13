<?php

require_once 'classes.php';
require_once 'ES/ESUploader.php';
require_once 'OWM/owm.php';

$owm = new OWM();
$response = $owm->get();

$array = json_decode($response, true);
$array['timestamp'] = gmdate('Y-m-d H:i:s', $array['dt']);
unset($array['dt']);

$es = new ESUploader();
$es->save(new Weather($array));
