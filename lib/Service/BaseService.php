<?php
namespace Spore\Service;

use Spore\Container;
use Spore\Traits\ContainerAware;

/**
 * @author Danny Kopping
 */
abstract class BaseService
{
    use ContainerAware;

    public function __construct(Container &$container)
    {
        $this->setContainer($container);
    }

    abstract public function run();
} 