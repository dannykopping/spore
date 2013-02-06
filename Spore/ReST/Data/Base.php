<?php
namespace Spore\ReST\Data;

use Spore\Spore;

/**
 *    The Base serialization/deserialization class
 */
abstract class Base
{
    /**
     * @var         \Spore\Spore        A reference to the main application
     */
    protected static $app;

    /**
     * Parse the given data to/from an encoding
     *
     * @param $data
     */
    public static function parse($data)
    {
    }

    /**
     * @param \Spore\Spore $app
     */
    public static function setApp(Spore $app)
    {
        self::$app = $app;
    }

    /**
     * @return \Spore\Spore
     */
    public static function getApp()
    {
        return self::$app;
    }
}
