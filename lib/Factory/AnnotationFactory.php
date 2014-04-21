<?php
namespace Spore\Factory;

use DocBlock\Element\AnnotationElement;
use Spore\Annotation\AbstractAnnotation;
use Spore\Container;

/**
 * @author Danny Kopping
 */
class AnnotationFactory extends AbstractFactory
{
    /**
     * Create an annotation instance based on a given identifier
     *
     * @param \DocBlock\Element\AnnotationElement $element
     *
     * @return null|AbstractAnnotation
     */
    public function createByElement(AnnotationElement $element)
    {
        $identifier = strtoupper($element->getName());
        if (empty($identifier)) {
            return null;
        }

        if (substr($identifier, 0, 1) == '@') {
            $identifier = strtolower(substr($identifier, 1));
        }

        $classes = array_change_key_case(self::getAnnotationClasses(), CASE_LOWER);
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