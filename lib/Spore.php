<?php
namespace Spore;

use Spore\Model\Route;
use Spore\Service\RouteInspectorService;

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
     * @return Route[]
     */
    public function initialise()
    {
        return $this->inspectRoutes();
    }

    /**
     * Inspect all namespace targets and return an array of Spore\Model\Route instances
     *
     * @return Route[]
     */
    protected function inspectRoutes()
    {
        /**
         * @var $routeInspector RouteInspectorService
         */
        $routeInspector = $this->container[Container::ROUTE_INSPECTOR];
        $routeInspector->setTargets($this->getTargets());

        $routes = $routeInspector->run($this->getContainer());

        return $routes;
    }

    /**
     * @return \Spore\Container
     */
    public function &getContainer()
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
} 