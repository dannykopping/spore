<?php
use Slim\Slim;
use Spore\Adapter\SlimAdapter;
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
        $spore = new Spore([new HelloSlimController()]);
        $adapter = $spore->createAdapter(SlimAdapter::getName(), $slim);

        $routes = $spore->getRoutes();

        $this->assertArrayHasKey('hello', $routes);
        $route = $routes['hello'];


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
     */
    public function hello()
    {
        return 'hello, world';
    }
}