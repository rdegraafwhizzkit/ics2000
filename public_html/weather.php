<?php

require_once dirname(__FILE__) . '/classes.php';
require_once dirname(__FILE__) . '/OWM/owm.php';

$conf = json_decode(file_get_contents(dirname(__FILE__) . "/conf/ics2000.json"), true);
date_default_timezone_set($conf['timezone']);

$iSaveables = array();
foreach($conf['destinations'] as $iSaveable) {
    require_once dirname(__FILE__) . '/'.$iSaveable['file'];
    $iSaveables[]=new $iSaveable['class']();    
}

$owm = new OWM();
$response = $owm->get();
$array = json_decode($response, true);
$array['timestamp'] = gmdate('Y-m-d H:i:s', $array['dt']);
unset($array['dt']);
$weather=new Weather($array);

foreach($iSaveables as $iSaveable) {
    $iSaveable->save($weather);
}
