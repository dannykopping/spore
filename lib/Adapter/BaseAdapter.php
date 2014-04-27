<?php
namespace Spore\Adapter;

use Exception;
use Spore\Container;
use Spore\Model\RouteModel;
use Spore\Traits\ContainerAware;

/**
 * @author Danny Kopping
 */
abstract class BaseAdapter
{
    use ContainerAware;

    /**
     * An instance of the adaptee's router that the adapter will interact with
     */
    protected $router;

    public function __construct(Container $container, $router)
    {
        $this->setContainer($container);
        $this->setRouter($router);
    }

    /**
     * Define multiple routes in the router
     *
     * @param RouteModel[] $models
     *
     * @return mixed
     */
    abstract public function createRoutes($models = array());

    /**
     * Define a single route in the router
     *
     * @param RouteModel $model
     *
     * @return mixed
     */
    abstract public function createRoute(RouteModel $model);

    /**
     * @param mixed $router
     *
     * @throws \Exception
     */
    public function setRouter($router)
    {
        $expectedRouterClass = $this->getRouterClass();
        if (!$router instanceof $expectedRouterClass) {
            throw new Exception(sprintf(
                'Invalid router given - expected "%s", given "%s"',
                $expectedRouterClass,
                get_class($router)
            ));
        }

        $this->router = $router;
    }

    /**
     * @return mixed
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @throws Exception
     * @return string
     */
    public static function getName()
    {
        throw new Exception('No name defined for adapter ' . get_called_class());
    }

    /**
     * Defines the expected class of the adapter's router
     *
     * @return string
     */
    abstract public function getRouterClass();
} 