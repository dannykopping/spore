<?php
namespace Spore\ReST\Data\Serializer;

use Spore\ReST\Data\Base;

class JSONSerializer extends Base
{
    public static function parse($data)
    {
        if (function_exists("json_encode")) {
            return json_encode($data);
        } else {
            return $data;
        }
    }
}
