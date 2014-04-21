<?php
namespace Spore\Annotation;

class BaseAnnotation extends AbstractAnnotation
{
    public function getClassDefinable()
    {
        return true;
    }

    public static function getIdentifier()
    {
        return 'base';
    }
}