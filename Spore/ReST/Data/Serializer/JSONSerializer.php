<?php
namespace Spore\ReST\Data\Serializer;

use Spore\ReST\Data\Base;

class JSONSerializer extends Base
{
    public static function parse($data)
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
    		$data = $data->toArray();
    	}
        
        if (function_exists("json_encode")) {
            return json_encode($data);
        } else {
            return $data;
        }
    }
}
