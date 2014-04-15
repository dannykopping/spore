<?php
namespace Spore\Sample;

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