<?php
namespace Spore;

use Composer\Autoload\ClassLoader;
use Spore\Service\RouteInspector;
use Spore\Service\TargetMapAnalyser;

/**
 * @author Danny Kopping
 */
class Spore
{
    /**
     * @var ClassLoader
     */
    protected $classLoader;

    /**
     * @var array
     */
    protected $namespaceTargets;

    /**
     * @var array
     */
    protected $targetMap;

    /**
     * @var Container
     */
    protected $container;

    public function __construct(ClassLoader $classLoader, array $namespaceTargets = array())
    {
        $this->setClassLoader($classLoader);
        $this->setNamespaceTargets($namespaceTargets);

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
        $this->analyseTargets();
        $this->inspectRoutes();
    }

    /**
     * Analyse the Composer ClassLoader and find classes within the given namespace targets
     * to target for routing
     */
    protected function analyseTargets()
    {
        $this->targetMap = array();

        /**
         * @var $analyser TargetMapAnalyser
         */
        $analyser = $this->container[Container::TARGET_MAP_ANALYSER];
        $analyser->setClassLoader($this->getClassLoader());
        $analyser->setNamespaceTargets($this->getNamespaceTargets());

        $this->targetMap = $analyser->run();

        return $this->targetMap;
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
        $routeInspector->setTargetMap($this->getTargetMap());

        $routeInspector->run();





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
     * @param ClassLoader $classLoader
     */
    public function setClassLoader($classLoader)
    {
        $this->classLoader = $classLoader;
    }

    /**
     * @return ClassLoader
     */
    public function getClassLoader()
    {
        return $this->classLoader;
    }

    /**
     * @param array $namespaceTargets
     */
    public function setNamespaceTargets($namespaceTargets)
    {
        // strip leading slashes
        if(count($namespaceTargets)) {
            foreach($namespaceTargets as &$namespaceTarget) {
                $namespaceTarget = trim($namespaceTarget);
                if(substr($namespaceTarget, 0, 1) == '\\') {
                    $namespaceTarget = substr($namespaceTarget, 1);
                }
            }
        }

        $this->namespaceTargets = $namespaceTargets;
    }

    /**
     * @return array
     */
    public function getNamespaceTargets()
    {
        return $this->namespaceTargets;
    }

    /**
     * @param array $targetMap
     */
    public function setTargetMap($targetMap)
    {
        $this->targetMap = $targetMap;
    }

    /**
     * @return array
     */
    public function getTargetMap()
    {
        return $this->targetMap;
    }
} 