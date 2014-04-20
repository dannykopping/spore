<?php
namespace Spore\Sample;

/**
 * Class RESTfulController
 *
 * @package Spore\Sample
 * @base    /sample
 */
class RESTfulController
{
    /**
     * @uri     /hello
     * @verbs   GET
     *
     * @return string
     */
    public function sayHello()
    {
        return 'hello world';
    }

    /**
     * @uri
     * @return string
     */
    public function ignoreMe()
    {
        return 'ignored';
    }

    /**
     * @uri /bob
     */
    public function getBob()
    {
        return 'bob, at your service';
    }
} 