<?php
namespace Spore\Factory;

use Spore\Container;

/**
 * @author Danny Kopping
 */
abstract class Base
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->setContainer($container);
    }

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