<?php
use Slim\Slim;
use Spore\Adapter\SlimAdapter;
use Spore\Spore;

require_once 'vendor/autoload.php';

$s = new Slim();
$spore = new Spore([new MyXXX()]);
$routes = $spore->getRoutes();
$adapter = $spore->createAdapter(SlimAdapter::getName(), $s->router());

$adapter->createRoute(current($routes));
$s->run();

class MyXXX
{
    /**
     * @uri     /hi/:id
     */
    public function sayHi($id)
    {
        die($id);
    }
}