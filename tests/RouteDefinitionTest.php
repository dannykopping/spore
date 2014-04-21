<?php

use Spore\Annotation\BaseAnnotation;
use Spore\Annotation\URIAnnotation;
use Spore\Container;
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
        $spore->getRoutes();

        $this->assertContains($resource, $spore->getTargets());
    }

    /**
     * Test that only Route classes are returned when retrieving routes
     */
    public function testRoutesCorrectness()
    {
        $spore  = new Spore([new MyRouteWithBaseController()]);
        $routes = $spore->getRoutes();

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
        $routes = $spore->getRoutes();

        $container = $spore->getContainer();

        $route = current($routes);
        $this->assertArrayHasKey($container[Container::BASE_ANNOTATION], $route->getAnnotations());
        $this->assertArrayHasKey($container[Container::URI_ANNOTATION], $route->getAnnotations());
    }

    /**
     * Test that a route is not created when a method is defined without its prerequisite annotations
     */
    public function testUnroutableRoute()
    {
        $spore  = new Spore([new MyUnroutableRouteController()]);
        $routes = $spore->getRoutes();

        $this->assertEmpty($routes);
    }

    /**
     * Test that a route's URI is returned correctly
     */
    public function testRouteURI()
    {
        $spore  = new Spore([new MyRouteWithBaseController()]);
        $routes = $spore->getRoutes();
        $route = current($routes);

        $this->assertEquals('/resource/action', $route->getURI());

        $spore  = new Spore([new MyBaselessRouteController()]);
        $routes = $spore->getRoutes();
        $route = current($routes);

        $this->assertEquals('/regular', $route->getURI());
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

class MyBaselessRouteController
{
    /**
     * @uri         /regular
     */
    public function regularAction()
    {
    }
}