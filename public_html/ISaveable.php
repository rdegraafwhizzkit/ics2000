<?php

interface ISaveable {
    public function save($object);
    public function setup();
    public function teardown();
}

