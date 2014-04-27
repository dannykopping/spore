<?php
namespace Spore\Adapter;

use Exception;
use Spore\Container;
use Spore\Model\RouteModel;
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

    /**
     * Define multiple routes in the adaptee
     *
     * @param RouteModel[] $models
     *
     * @return mixed
     */
    abstract public function createRoutes($models = array());

    /**
     * Define a single route in the adaptee
     *
     * @param RouteModel $model
     *
     * @return mixed
     */
    abstract public function createRoute(RouteModel $model);

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