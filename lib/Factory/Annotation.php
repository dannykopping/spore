<?php
namespace Spore\Factory;

use DocBlock\Element\AnnotationElement;
use Exception;
use Spore\Container;

/**
 * @author Danny Kopping
 */
class Annotation extends Base
{
    /**
     * Create an annotation instance based on a given identifier
     *
     * @param \DocBlock\Element\AnnotationElement $element
     *
     * @return null
     */
    public function createByElement(AnnotationElement $element)
    {
        $identifier = strtoupper($element->getName());
        if (empty($identifier)) {
            return null;
        }

        if (substr($identifier, 0, 1) == '@') {
            $identifier = substr($identifier, 1);
        }

        $classes = self::getAnnotationClasses();
        if (!isset($classes[$identifier])) {
            return null;
        }

        $annotationClass = $classes[$identifier];
        return new $annotationClass($element);
    }

    /**
     * Retrieve an array of annotation classes, name as key => namespace as value
     *
     * return array
     */
    private function getAnnotationClasses()
    {
        $container = $this->getContainer();
        return $container[Container::ANNOTATION_CLASSES];
    }
}