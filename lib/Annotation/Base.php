<?php
namespace Spore\Annotation;

class Base extends AbstractAnnotation
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