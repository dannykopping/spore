<?php
namespace Spore\Factory;

use Spore\Container;
use Spore\Traits\ContainerAware;

/**
 * @author Danny Kopping
 */
abstract class AbstractFactory
{
    use ContainerAware;

    public function __construct(Container $container)
    {
        $this->setContainer($container);
    }
} 