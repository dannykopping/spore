<?php
namespace Spore\Exception;

/**
 * @author Danny Kopping
 */
class TargetException extends Base
{
    const MISSING_CLASS_LOADER = 100;
    const MISSING_NAMESPACE_TARGETS = 101;

    /**
     * Return an exception message based on a given code
     *
     * @param $code
     *
     * @return string
     */
    protected function getExceptionMessage($code)
    {
        switch ($code) {
            case self::MISSING_CLASS_LOADER:
                return <<<MSG
A ClassLoader instance was not provided. Simply pass the output of
`require_once('vendor/autoload.php');` to the constructor
MSG
;

            case self::MISSING_NAMESPACE_TARGETS:
                return 'You did not provide any namespaces to target for inclusion';

            default:
                return '...';
        }
    }
}