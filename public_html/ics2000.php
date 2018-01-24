<?php

require_once dirname(__FILE__) . '/classes.php';

$conf = json_decode(file_get_contents(dirname(__FILE__) . "/conf/ics2000.json"), true);
date_default_timezone_set($conf['timezone']);

$iSaveables = array();
foreach($conf['destinations'] as $iSaveable) {
    require_once dirname(__FILE__) . '/'.$iSaveable['file'];
    $iSaveables[]=new $iSaveable['class']();    
}

$date_end = new DateTime();
$date_start = clone $date_end;
$date_start->sub(new DateInterval($conf['period']));

$url = sprintf(
        "%s?start_date=%s&end_date=%s&precision=%s&password_hash=%s&action=%s&differential=%s&mac=%s&interpolate=%s&email=%s", 
        "https://trustsmartcloud2.com/ics2000_api/p1.php", 
        rawurlencode($date_start->format('Y-m-d H:00:00')), 
        rawurlencode($date_end->format('Y-m-d H:00:00')), 
        "hour", 
        urlencode($conf['password_hash']), 
        "aggregated_reports", 
        "false", 
        $conf['mac'], 
        "true", 
        urlencode($conf['email'])
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_URL, $url);
$json = json_decode(curl_exec($ch), true);
curl_close($ch);

$prevkWhHigh = 0;
$prevkWhLow = 0;
$prevgass = 0;

foreach ($json as $value) {

    $kWhHigh = $value[0];
    $kWhLow = $value[1];
    $gass = $value[4];

    foreach($iSaveables as $iSaveable) {

        $iSaveable->save(new Reading(array(
            "timestamp" => $date_start->format('Y-m-d H:00:00'),
            "electricityHighkWh" => $kWhHigh / 1000,
            "electricityLowkWh" => $kWhLow / 1000,
            "gassm3" => $gass / 1000

        )));

        if ($prevkWhHigh != 0 && $kWhHigh != 0 && $prevkWhLow != 0 && $kWhLow != 0 && $prevgass != 0 && $gass != 0) {

            $date_start->sub(new DateInterval('PT1H'));

            $iSaveable->save(new Usage(array(
                "timestamp" => $date_start->format('Y-m-d H:00:00'),
                "electricityHighkWh" => ($kWhHigh - $prevkWhHigh) / 1000,
                "electricityLowkWh" => ($kWhLow - $prevkWhLow) / 1000,
                "gassm3" => ($gass - $prevgass) / 1000,
                "electricityHighkWhCost" => $conf['kWhHighPrice'] * ($kWhHigh - $prevkWhHigh) / 1000,
                "electricityLowkWhCost" => $conf['kWhLowPrice'] * ($kWhLow - $prevkWhLow) / 1000,
                "gassm3Cost" => $conf['gassPrice'] * ($gass - $prevgass) / 1000
            )));

            $date_start->add(new DateInterval('PT1H'));

        }

    }

    $date_start->add(new DateInterval('PT1H'));

    $prevkWhHigh = $kWhHigh;
    $prevkWhLow = $kWhLow;
    $prevgass = $gass;
    
}
