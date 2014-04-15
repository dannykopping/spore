<?php
namespace Spore\Service;

use Spore\Factory\Base as BaseFactory;

/**
 * @author Danny Kopping
 */
abstract class Base extends BaseFactory
{
    abstract public function run();
} 