<?php
namespace Spore\Model;

use DocBlock\Element\AnnotationElement;

/**
 * @author Danny Kopping
 */
class Route
{
    /**
     * @var AnnotationElement[]
     */
    protected $annotations;

    /**
     * @var callable
     */
    protected $callback;

    public function __construct(callable $callback, array $annotations = array())
    {
        $this->annotations = [];

        if (!count($annotations)) {
            return;
        }

        foreach ($annotations as $annotation) {
            $this->addAnnotation($annotation);
        }
    }

    /**
     * @param AnnotationElement $annotation
     */
    public function addAnnotation(AnnotationElement $annotation)
    {
        if (empty($annotation)) {
            return;
        }

        $this->annotations[$annotation->getName()] = $annotation;
    }

    /**
     * @param $name
     *
     * @return null|AnnotationElement
     */
    public function getAnnotationByName($name)
    {
        if (!isset($this->annotations[$name])) {
            return null;
        }

        return $this->annotations[$name];
    }

    /**
     * @return AnnotationElement[]
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
} 