<?php

use Spore\Annotation\BaseAnnotation;
use Spore\Annotation\URIAnnotation;
use Spore\Annotation\VerbsAnnotation;
use Spore\Container;
use Spore\Model\RouteModel;
use Spore\Model\Verbs;
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
        $routes = $spore->getRoutes();

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
        $routes = $spore->getRoutes();

        $container = $spore->getContainer();

        $executed = false;
        $container->extend(
            Container::BEFORE_CALLBACK,
            function () use (&$executed) {
                return function (RouteModel $route) use (&$executed) {
                    $executed = true;
                };
            }
        );

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
        $routes = $spore->getRoutes();

        $container = $spore->getContainer();

        $passedResult = false;
        $container->extend(
            Container::AFTER_CALLBACK,
            function () use (&$passedResult) {
                return function (RouteModel $route, $routeResult = null) use (&$passedResult) {
                    $passedResult = $routeResult;
                };
            }
        );

        $this->assertArrayHasKey('sayHello', $routes);
        $route = $routes['sayHello'];

        $routeResult = $route->execute();
        $this->assertSame($passedResult, $routeResult);
    }

    /**
     * Test that not defining a @verbs annotation will produce that
     * will be executable with any of the supported HTTP verbs
     */
    public function testDefaultVerbs()
    {
        $spore     = new Spore([new HelloWorldController()]);
        $container = $spore->getContainer();

        $routes = $spore->getRoutes();

        $route = $routes['echoRoute'];

        $this->assertEmpty($route->getAnnotationByName($container[Container::VERBS_ANNOTATION]));
        $this->assertEquals(Verbs::getAll(), $route->getVerbs());
    }

    /**
     * Test that defining a @verbs annotation with a single value will work as expected
     */
    public function testSingeVerb()
    {
        $spore     = new Spore([new HelloWorldController()]);
        $container = $spore->getContainer();

        $routes = $spore->getRoutes();

        $route = $routes['singleVerb'];

        $this->assertNotEmpty($route->getAnnotationByName($container[Container::VERBS_ANNOTATION]));
        $this->assertEquals([Verbs::GET], $route->getVerbs());
    }

    /**
     * Test that defining a @verbs annotation with multiple values will work as expected
     */
    public function testMultipleVerbs()
    {
        $spore     = new Spore([new HelloWorldController()]);
        $container = $spore->getContainer();

        $routes = $spore->getRoutes();

        $route = $routes['multipleVerbs'];

        $this->assertNotEmpty($route->getAnnotationByName($container[Container::VERBS_ANNOTATION]));
        $this->assertEquals([Verbs::GET, Verbs::POST, Verbs::HEAD], $route->getVerbs());
    }
}

class HelloWorldController
{
    /**
     * @uri         /echo
     */
    public function echoRoute(RouteModel $route)
    {
        return $route;
    }

    /**
     * @uri         /allo
     */
    public function sayHello(RouteModel $route)
    {
        return 'allo, allo!';
    }

    /**
     * @uri         /single-verb
     * @verbs       GET
     */
    public function singleVerb()
    {
    }

    /**
     * @uri         /single-verb
     * @verb        GET,POST,HEAD
     */
    public function multipleVerbs()
    {
    }
}