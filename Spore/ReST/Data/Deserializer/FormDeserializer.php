<?php
namespace Spore\ReST\Data\Deserializer;

use Spore\ReST\Data\Base;

class FormDeserializer extends Base
{
    /**
     * Parse HTML form data
     *
     * This method parses CSV content into a numeric array
     * containing an array of data for each CSV line.
     *
     * @param   string $data
     * @return  array
     */
    public static function parse($data)
    {
        $res = array();
        parse_str($data, $res);
        return $res;
    }
}
