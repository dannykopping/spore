<?php
namespace Spore\Model;

use Spore\Annotation\AbstractAnnotation;
use Spore\Container;

/**
 * @author Danny Kopping
 */
class Route
{
    /**
     * @var AbstractAnnotation[]
     */
    protected $annotations;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var callable
     */
    protected $callback;

    public function __construct(callable $callback, Container $container, array $annotations = array())
    {
        $this->annotations = [];

        if (!count($annotations)) {
            return;
        }

        foreach ($annotations as $annotation) {
            $this->addAnnotation($annotation);
        }

        $this->setContainer($container);
        $this->setCallback($callback);
    }

    /**
     * @param AbstractAnnotation $annotation
     */
    public function addAnnotation(AbstractAnnotation $annotation)
    {
        if (empty($annotation)) {
            return;
        }

        $this->annotations[strtolower($annotation->getIdentifier())] = $annotation;
    }

    /**
     * @param $name
     *
     * @return null|AbstractAnnotation
     */
    public function getAnnotationByName($name)
    {
        if (!isset($this->annotations[$name])) {
            return null;
        }

        return $this->annotations[$name];
    }

    /**
     * @return AbstractAnnotation[]
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * @param callable $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
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

    /**
     * Execute the route, calling pre- and post-execution callbacks
     *
     * @return mixed
     */
    public function execute()
    {
        $container = $this->getContainer();
        $before    = $container[Container::BEFORE_CALLBACK];
        $after     = $container[Container::AFTER_CALLBACK];

        call_user_func_array($before, [$this]);
        $result = call_user_func($this->getCallback());
        call_user_func_array($after, [$this, $result]);

        return $result;
    }
} 