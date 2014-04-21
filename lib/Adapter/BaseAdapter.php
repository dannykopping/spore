<?php
namespace Spore\Adapter;

use Exception;
use Spore\Container;
use Spore\Model\Route;
use Spore\Traits\ContainerAware;

/**
 * @author Danny Kopping
 */
abstract class BaseAdapter
{
    use ContainerAware;

    /**
     * An instance of the class the adapter will interact with
     */
    protected $adaptee;

    public function __construct(Container $container, $adaptee)
    {
        $this->setContainer($container);
        $this->setAdaptee($adaptee);
    }

    abstract public function createRoute(Route $route);

    /**
     * @param mixed $instance
     */
    public function setAdaptee($instance)
    {
        $this->adaptee = $instance;
    }

    /**
     * @return mixed
     */
    public function getAdaptee()
    {
        return $this->adaptee;
    }

    /**
     * @throws Exception
     * @return string
     */
    public static function getName()
    {
        throw new Exception('No name defined for adapter ' . get_called_class());
    }
} 