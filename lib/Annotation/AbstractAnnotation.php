<?php
namespace Spore\Annotation;

use DocBlock\Element\AnnotationElement;
use DocBlock\Element\ClassElement;
use Exception;
use Spore\Exception\AnnotationException;

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
    protected $raw;

    /**
     * Specifies whether this annotation can be defined at class level
     *
     * @var bool
     */
    protected $classDefinable = false;

    public function __construct(AnnotationElement $raw)
    {
        $this->setRaw($raw);

        if(!$raw || !$raw->getElement()) {
            return;
        }

        $container = $raw->getElement();
        if($container instanceof ClassElement && !$this->getClassDefinable()) {
            throw new AnnotationException(AnnotationException::ANNOTATION_AT_CLASS_LEVEL, $this->getIdentifier());
        }
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
     * @return boolean
     */
    public function getClassDefinable()
    {
        return $this->classDefinable;
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