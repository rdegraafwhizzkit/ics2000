<?php

class Reading {
  function __construct($parameters = array()) {
    foreach($parameters as $key => $value) {
        $this->$key = $value;
    }
  }
  public $timestamp;    
  public $electricityHighkWh;
  public $electricityLowkWh;
  public $gassm3;
}

class Usage {
  function __construct($parameters = array()) {
    foreach($parameters as $key => $value) {
        $this->$key = $value;
    }
  }
  public $timestamp;    
  public $electricityHighkWh;
  public $electricityLowkWh;
  public $gassm3;
  public $electricityHighkWhCost;
  public $electricityLowkWhCost;
  public $gassm3Cost;
}
