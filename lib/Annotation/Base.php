<?php
namespace Spore\Annotation;

use DocBlock\Element\AnnotationElement;
use Exception;

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

    public static function getIdentifier()
    {
        throw new Exception('No identifier implemented for '.get_called_class());
    }

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