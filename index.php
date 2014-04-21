<?php
use Slim\Slim;
use Spore\Adapter\SlimAdapter;
use Spore\Spore;

require_once 'vendor/autoload.php';

$s = new Slim();
$spore = new Spore([new MyXXX()]);
$routes = $spore->getRoutes();
$adapter = $spore->createAdapter(SlimAdapter::getName(), $s);

$adapter->createRoute(current($routes));

$s->run();

class MyXXX
{
    /**
     * @uri     /hi
     */
    public function sayHi()
    {
        die("HI!");
    }
}