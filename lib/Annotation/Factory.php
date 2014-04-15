<?php
namespace Spore\Annotation;

use DocBlock\Element\AnnotationElement;
use Exception;
use ReflectionClass;

/**
 * @author Danny Kopping
 */
class Factory
{
    /**
     * Create an annotation instance based on a given identifier
     *
     * @param \DocBlock\Element\AnnotationElement $element
     *
     * @return null
     */
    public static function createAnnotationByElement(AnnotationElement $element)
    {
        $identifier = strtoupper($element->getName());
        if(empty($identifier)) {
            return null;
        }

        if(substr($identifier, 0, 1) == '@') {
            $identifier = substr($identifier, 1);
        }

        $classes = self::getAnnotationClasses();
        if(!isset($classes[$identifier])) {
            return null;
        }

        $annotationClass = $classes[$identifier];
        return new $annotationClass($element);
    }

    /**
     * Make this a little more flexible, injectable
     *
     * @throws \Exception
     */
    private static function getAnnotationClasses()
    {
        $classLoader = require(__DIR__ . '/../../vendor/autoload.php');
        if(empty($classLoader)) {
            throw new Exception('Composer is required in order for the Factory to function');
        }

        $classes = array_keys($classLoader->getClassMap());
        $searchNS = __NAMESPACE__;

        $matches = [];
        foreach($classes as $ns) {
            if(strpos($ns, $searchNS) !== false) {
                $ref = new ReflectionClass($ns);
                $matches[$ref->getShortName()] = $ns;
            }
        }

        return $matches;
    }
} 