<?php
namespace Spore;

use Spore\Factory\AdapterFactory;
use Spore\Model\RouteModel;
use Spore\Service\RouteInspectorService;
use stdClass;

/**
 * @author Danny Kopping
 */
class Spore
{
    /**
     * @var array
     */
    protected $targets;

    /**
     * @var Container
     */
    protected $container;

    public function __construct(array $targets = array())
    {
        $this->setTargets($targets);

        $this->setupContainer();
    }

    /**
     * Set up the dependency injection container
     */
    protected function setupContainer()
    {
        $this->container = new Container();
        $this->container->initialise();
    }

    /**
     * Inspect all namespace targets and return an array of Spore\Model\Route instances
     *
     * @return RouteModel[]
     */
    public function getRoutes()
    {
        $routeInspector = $this->getRouteInspectorService();
        $routeInspector->setTargets($this->getTargets());

        $routes = $routeInspector->getRoutes($this->getContainer());
        return $routes;
    }

    /**
     * Create an adapter by name, and pass along a concrete instance
     * of the object being adapted
     *
     * @param $adapterName
     * @param $router
     *
     * @return null|\Spore\Adapter\BaseAdapter
     */
    public function createAdapter($adapterName, $router)
    {
        $adapter = $this->getAdapterFactory()->createByName($adapterName, $router);
        return $adapter;
    }

    /**
     * @return \Spore\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param $target
     */
    public function addTarget($target)
    {
        if (empty($this->targets)) {
            $this->targets = [];
        }

        if (array_search($target, $this->targets, true)) {
            return;
        }

        $this->targets[] = $target;
    }

    /**
     * @param array $targets
     */
    public function setTargets($targets)
    {
        $this->targets = $targets;
    }

    /**
     * @return array
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @return AdapterFactory
     */
    protected function getAdapterFactory()
    {
        return $this->container[Container::ADAPTER_FACTORY];
    }

    /**
     * @return RouteInspectorService
     */
    private function getRouteInspectorService()
    {
        return $this->container[Container::ROUTE_INSPECTOR];
    }
} 