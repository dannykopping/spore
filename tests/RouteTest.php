<?php

use Spore\Annotation\Base;
use Spore\Annotation\URI;
use Spore\Spore;

/**
 * @group routing
 */
class RouteTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that adding a target to Spore actually works
     */
    public function testTarget()
    {
        $resource = new MyRouteWithBase();

        $spore = new Spore([$resource]);
        $spore->initialise();

        $this->assertContains($resource, $spore->getTargets());
    }

    /**
     * Test that only Route classes are returned when retrieving routes
     */
    public function testRoutesCorrectness()
    {
        $spore  = new Spore([new MyRouteWithBase()]);
        $routes = $spore->initialise();

        $this->assertGreaterThanOrEqual(1, $routes);

        $route = $routes[0];
        $this->assertContainsOnlyInstancesOf('\\Spore\\Annotation\\AbstractAnnotation', $route->getAnnotations());
    }

    /**
     * Test that class & method are both inspected, with class annotations filtering down to method-level
     */
    public function testBaseRoute()
    {
        $spore  = new Spore([new MyRouteWithBase()]);
        $routes = $spore->initialise();

        $route = $routes[0];
        $this->assertArrayHasKey(Base::getIdentifier(), $route->getAnnotations());
        $this->assertArrayHasKey(URI::getIdentifier(), $route->getAnnotations());
    }

    /**
     * Test that a route is not created when a method is defined without its prerequisite annotations
     */
    public function testUnroutableRoute()
    {
        $spore  = new Spore([new MyUnroutableRoute()]);
        $routes = $spore->initialise();

        $this->assertEmpty($routes);
    }
}

/**
 * @base        /resource
 */
class MyRouteWithBase
{
    /**
     * @uri     /action
     */
    public function myAction()
    {
    }
}

class MyUnroutableRoute
{
    /**
     * @invalid     /unroutable
     */
    public function unroutableAction()
    {
    }
}