<?php
namespace Spore;

use Spore\Service\RouteInspector;

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

    public function initialise()
    {
        $this->inspectRoutes();
    }

    /**
     * Inspect all namespace targets and return an array of Spore\Model\Route instances
     */
    protected function inspectRoutes()
    {
        /**
         * @var $routeInspector RouteInspector
         */
        $routeInspector = $this->container[Container::ROUTE_INSPECTOR];
        $routeInspector->setTargets($this->getTargets());

        $routeInspector->run($this->getContainer());

        // TODO: Implement route caching
        // http://docs.doctrine-project.org/en/2.0.x/reference/caching.html
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
        if(empty($this->targets)) {
            $this->targets = [];
        }

        if(array_search($target, $this->targets, true)) {
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