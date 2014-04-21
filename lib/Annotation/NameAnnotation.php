<?php
namespace Spore\Annotation;

class NameAnnotation extends AbstractAnnotation
{
    public static function getIdentifier()
    {
        return 'name';
    }

    public function getDefaultValue()
    {
        return null;
    }
}