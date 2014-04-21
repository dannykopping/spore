<?php
namespace Spore\Exception;

use Exception;

/**
 * @author Danny Kopping
 */
abstract class BaseException extends Exception
{
    /**
     * @var mixed
     */
    protected $context;

    public function __construct($code = 0, $context = null)
    {
        $message = $this->getExceptionMessage($code, $context);
        parent::__construct($message, $code);
    }

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Return an exception message based on a given code
     *
     * @param $code
     * @param $context
     *
     * @return string
     */
    abstract protected function getExceptionMessage($code, $context = null);
}