<?php
use Slim\Environment;
use Slim\Slim;
use Spore\Adapter\SlimAdapter;
use Spore\Container;
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
        $model  = $routes['hello'];

        $adapter->createRoute($model);

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
        $model  = $routes['jollyWell'];

        /**
         * @var $route \Slim\Route
         */
        $route = $adapter->createRoute($model);

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
        $model  = $routes['tallyHo'];

        /**
         * @var $route \Slim\Route
         */
        $route = $adapter->createRoute($model);

        $this->assertInstanceOf('\\Slim\\Route', $route);
        $this->assertEquals('tally-ho!', $route->getName());
    }

    /**
     * Ensure that parameters passed in URI will be passed along to callback
     *
     * @dataProvider    adapteeDataProvider
     */
    public function testRouteParams(Slim $slim)
    {
        $spore   = new Spore([new HelloSlimController()]);
        $adapter = $spore->createAdapter(SlimAdapter::getName(), $slim);

        $routes = $spore->getRoutes();
        $model  = $routes['testParams'];

        $adapter->createRoute($model);

        // fake a request
        Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO'      => '/slim/params/1/2',
            ]
        );

        $slim->call();
        $this->assertEquals('1,2', $slim->response->getBody());
    }

    /**
     * Ensure that even though Slim is executing a callback, Spore still marks the route as 'current'
     *
     * @dataProvider    adapteeDataProvider
     */
    public function testCurrentRoute(Slim $slim)
    {
        $spore     = new Spore([new HelloSlimController()]);
        $container = $spore->getContainer();
        $adapter   = $spore->createAdapter(SlimAdapter::getName(), $slim);

        $routes = $spore->getRoutes();
        $model  = $routes['testParams'];

        $adapter->createRoute($model);

        // fake a request
        Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO'      => '/slim/params/1/2',
            ]
        );

        $slim->call();
        $this->assertSame($container[Container::CURRENT_ROUTE], $model);
    }

    /**
     * Ensure that multiple routes can be created at once
     *
     * @dataProvider    adapteeDataProvider
     */
    public function testMultipleRouteCreation(Slim $slim)
    {
        $spore       = new Spore([new HelloSlimController()]);
        $adapter     = $spore->createAdapter(SlimAdapter::getName(), $slim);
        $routeModels = $spore->getRoutes();
        $routes      = $adapter->createRoutes($routeModels);

        $this->assertGreaterThan(0, count($routeModels));
        $this->assertCount(count($routeModels), $routes);
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
        return [
            [new Slim()]
        ];
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

    /**
     * @uri     /params/:one/:two
     * @verbs   GET
     */
    public function testParams($one, $two)
    {
        echo implode(',', func_get_args());
    }
}