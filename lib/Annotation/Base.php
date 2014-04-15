<?php
namespace Spore\Annotation;

use DocBlock\Element\AnnotationElement;

/**
 * @author Danny Kopping
 */
abstract class Base
{
    /**
     * @var AnnotationElement
     */
    private $element;

    public function __construct($element)
    {
        $this->setElement($element);
    }

    abstract public function getIdentifier();

    /**
     * @param \DocBlock\Element\AnnotationElement $element
     */
    public function setElement(AnnotationElement $element)
    {
        $this->element = $element;
    }

    /**
     * @return \DocBlock\Element\AnnotationElement
     */
    public function getElement()
    {
        return $this->element;
    }
} 