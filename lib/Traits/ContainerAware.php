<?php
namespace Spore\Traits;

use Spore\Container;

trait ContainerAware
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param \Spore\Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return \Spore\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
} 