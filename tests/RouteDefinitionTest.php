<?php

use Spore\Annotation\Base;
use Spore\Annotation\URI;
use Spore\Model\Route;
use Spore\Spore;

/**
 * @group routing
 */
class RouteDefinitionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that adding a target to Spore actually works
     */
    public function testTarget()
    {
        $resource = new MyRouteWithBaseController();

        $spore = new Spore([$resource]);
        $spore->initialise();

        $this->assertContains($resource, $spore->getTargets());
    }

    /**
     * Test that only Route classes are returned when retrieving routes
     */
    public function testRoutesCorrectness()
    {
        $spore  = new Spore([new MyRouteWithBaseController()]);
        $routes = $spore->initialise();

        $this->assertNotEmpty($routes);

        $route = current($routes);
        $this->assertContainsOnlyInstancesOf('\\Spore\\Annotation\\AbstractAnnotation', $route->getAnnotations());
    }

    /**
     * Test that class & method are both inspected, with class annotations filtering down to method-level
     */
    public function testBaseRoute()
    {
        $spore  = new Spore([new MyRouteWithBaseController()]);
        $routes = $spore->initialise();

        $route = current($routes);
        $this->assertArrayHasKey(Base::getIdentifier(), $route->getAnnotations());
        $this->assertArrayHasKey(URI::getIdentifier(), $route->getAnnotations());
    }

    /**
     * Test that a route is not created when a method is defined without its prerequisite annotations
     */
    public function testUnroutableRoute()
    {
        $spore  = new Spore([new MyUnroutableRouteController()]);
        $routes = $spore->initialise();

        $this->assertEmpty($routes);
    }
}

/**
 * @base        /resource
 */
class MyRouteWithBaseController
{
    /**
     * @uri     /action
     */
    public function myAction()
    {
    }
}

class MyUnroutableRouteController
{
    /**
     * @invalid     /unroutable
     */
    public function unroutableAction()
    {
    }
}