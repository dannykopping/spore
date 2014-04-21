<?php
namespace Spore\Exception;

/**
 * @author Danny Kopping
 */
class AnnotationException extends BaseException
{
    const ANNOTATION_AT_CLASS_LEVEL = 100;

    /**
     * Return an exception message based on a given code
     *
     * @param $code
     * @param $context
     *
     * @return string
     */
    protected function getExceptionMessage($code, $context = null)
    {
        $message = null;

        switch ($code) {
            case self::ANNOTATION_AT_CLASS_LEVEL:
                $message = 'Annotation @%s cannot be defined at class level';
        }

        return sprintf($message, $context);
    }
}