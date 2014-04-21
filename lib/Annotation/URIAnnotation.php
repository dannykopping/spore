<?php
namespace Spore\Annotation;

class URIAnnotation extends AbstractAnnotation
{
    public static function getIdentifier()
    {
        return 'uri';
    }

    public function getDefaultValue()
    {
        return null;
    }
}