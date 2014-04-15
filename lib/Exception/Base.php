<?php
namespace Spore\Exception;

use Exception;

/**
 * @author Danny Kopping
 */
abstract class Base extends Exception
{
    public function __construct($code = 0)
    {
        $message = $this->getExceptionMessage($code);
        parent::__construct($message, $code);
    }

    /**
     * Return an exception message based on a given code
     *
     * @param $code
     *
     * @return string
     */
    abstract protected function getExceptionMessage($code);
}