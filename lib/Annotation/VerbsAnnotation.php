<?php
namespace Spore\Annotation;

use Spore\Model\Verbs;

class VerbsAnnotation extends AbstractAnnotation
{
    public static function getIdentifier()
    {
        return 'verbs';
    }

    /**
     * Return an array of verbs from a comma-delimited list of values
     *
     * @return array|string
     */
    public function getValues()
    {
        $value = parent::getValue();
        $value = explode(',', $value);
        return $value;
    }

    public function getDefaultValue()
    {
        return implode(',', Verbs::getAll());
    }
}