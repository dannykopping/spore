<?php
namespace Spore\ReST\Data\Deserializer;

use Spore\ReST\Data\Base;

class JSONDeserializer extends Base
{
    /**
     * Parse JSON
     *
     * This method converts the raw JSON input
     * into an associative array.
     *
     * @param   string $data
     * @return  array|string
     */
    public static function parse($data)
    {
        if (function_exists("json_decode")) {
            return json_decode($data);
        } else {
            return $data;
        }
    }
}
