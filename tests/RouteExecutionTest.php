<?php

use Spore\Annotation\Base;
use Spore\Annotation\URI;
use Spore\Container;
use Spore\Model\Route;
use Spore\Spore;

/**
 * @group routing
 */
class RouteExecutionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that executing a route returns an expected result (the injected Route parameter)
     */
    public function testRouteExecution()
    {
        $spore  = new Spore([new HelloWorldController()]);
        $routes = $spore->initialise();

        $this->assertNotEmpty($routes);

        $route = current($routes);
        $this->assertSame($route, $route->execute());
    }

    /**
     * Test that executing a route will, in turn, fire the beforeCallback
     */
    public function testBeforeCallbackExecution()
    {
        $spore  = new Spore([new HelloWorldController()]);
        $routes = $spore->initialise();

        $container = $spore->getContainer();

        $executed = false;
        $container->extend(Container::BEFORE_CALLBACK, function() use (&$executed) {
            return function(Route $route) use (&$executed) {
                $executed = true;
            };
        });

        $this->assertArrayHasKey('echoRoute', $routes);
        $route = $routes['echoRoute'];

        $this->assertSame($route, $route->execute());
        $this->assertTrue($executed);
    }

    /**
     * Test that executing a route will, in turn, fire the beforeCallback and pass it the result
     */
    public function testAfterCallbackExecution()
    {
        $spore  = new Spore([new HelloWorldController()]);
        $routes = $spore->initialise();

        $container = $spore->getContainer();

        $passedResult = false;
        $container->extend(Container::AFTER_CALLBACK, function() use (&$passedResult) {
            return function(Route $route, $routeResult = null) use (&$passedResult) {
                $passedResult = $routeResult;
            };
        });

        $this->assertArrayHasKey('sayHello', $routes);
        $route = $routes['sayHello'];

        $routeResult = $route->execute();
        $this->assertSame($passedResult, $routeResult);
    }
}

class HelloWorldController
{
    /**
     * @uri         /echo
     */
    public function echoRoute(Route $route)
    {
        return $route;
    }

    /**
     * @uri         /allo
     */
    public function sayHello(Route $route)
    {
        return 'allo, allo!';
    }
}