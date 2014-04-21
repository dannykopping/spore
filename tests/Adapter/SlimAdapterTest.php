<?php
use Slim\Environment;
use Slim\Slim;
use Spore\Adapter\SlimAdapter;
use Spore\Model\Verbs;
use Spore\Spore;

/**
 * @group   adapters
 */
class SlimAdapterTest extends BaseAdapterTest
{
    /**
     * @dataProvider adapteeDataProvider
     */
    public function testBasicRouteCreation(Slim $slim)
    {
        $spore   = new Spore([new HelloSlimController()]);
        $adapter = $spore->createAdapter(SlimAdapter::getName(), $slim);

        $routes = $spore->getRoutes();
        $route  = $routes['hello'];
        $adapter->createRoute($route);

        // fake a request
        Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO'      => '/slim/hello',
            ]
        );

        // use environment values to execute a route, if one exists - otherwise fail!
        $slim->notFound(
            function () {
                $this->fail('Route cannot be found');
            }
        );
        $slim->call();

        // PHPUnit, why you haz no pass method?
        $this->assertTrue(true, 'Route was called successfully');
    }

    /**
     * @dataProvider    adapteeDataProvider
     */
    public function testRouteCreationWithVerbs(Slim $slim)
    {
        $spore   = new Spore([new HelloSlimController()]);
        $adapter = $spore->createAdapter(SlimAdapter::getName(), $slim);

        $routes = $spore->getRoutes();
        $route  = $routes['jollyWell'];

        /**
         * @var $route \Slim\Route
         */
        $route  = $adapter->createRoute($route);

        $this->assertInstanceOf('\\Slim\\Route', $route);
        $this->assertEquals([Verbs::GET, Verbs::POST, Verbs::PUT], $route->getHttpMethods());
    }

    /**
     * @dataProvider    adapteeDataProvider
     */
    public function testRouteCreationWithName(Slim $slim)
    {
        $spore   = new Spore([new HelloSlimController()]);
        $adapter = $spore->createAdapter(SlimAdapter::getName(), $slim);

        $routes = $spore->getRoutes();
        $route  = $routes['tallyHo'];

        /**
         * @var $route \Slim\Route
         */
        $route  = $adapter->createRoute($route);

        $this->assertInstanceOf('\\Slim\\Route', $route);
        $this->assertEquals('tally-ho!', $route->getName());
    }

    public function getAdapterName()
    {
        return SlimAdapter::getName();
    }

    public function getMainClassNamespace()
    {
        return '\\Slim\\Slim';
    }

    public function adapteeDataProvider()
    {
        return [[new Slim()]];
    }
}

/**
 * @base        /slim
 */
class HelloSlimController
{
    /**
     * @uri     /hello
     * @verbs   GET
     */
    public function hello()
    {
    }

    /**
     * @uri     /jolly-well
     * @verbs   GET,POST,PUT
     */
    public function jollyWell()
    {
    }

    /**
     * @uri     /tally-ho
     * @name    tally-ho!
     */
    public function tallyHo()
    {
    }
}