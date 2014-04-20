<?php
namespace Spore\Annotation;

use DocBlock\Element\AnnotationElement;
use Exception;

/**
 * @author Danny Kopping
 */
abstract class AbstractAnnotation
{
    /**
     * Raw annotation element
     *
     * @var AnnotationElement
     */
    private $raw;

    public function __construct($element)
    {
        $this->setRaw($element);
    }

    /**
     * @throws \Exception
     * @return string
     */
    public static function getIdentifier()
    {
        throw new Exception('No identifier implemented for ' . get_called_class());
    }

    /**
     * @param \DocBlock\Element\AnnotationElement $element
     */
    public function setRaw(AnnotationElement $element)
    {
        $this->raw = $element;
    }

    /**
     * @return \DocBlock\Element\AnnotationElement
     */
    public function getRaw()
    {
        return $this->raw;
    }

    public function __toString()
    {
        return "Annotation [identifier='{$this->getIdentifier()}']";
    }

} 