<?php
use Spore\Sample\RESTfulController;
use Spore\Spore;

$classLoader = require_once __DIR__.'/../vendor/autoload.php';

$c = new RESTfulController();
$s = new Spore($classLoader, array(
    '\Spore\Sample'
));

$s->initialise();
var_dump('...'); die();