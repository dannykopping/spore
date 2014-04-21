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
        $slim->notFound(function () {
            $this->fail('Route cannot be found');
        });
        $slim->call();
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
}