<?php
namespace Spore\ReST\Data\Serializer;

use SimpleXMLElement;
use Spore\ReST\Data\Base;
use stdClass;

/**
 * @see http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/
 */
class XMLSerializer extends Base
{
    public static function parse($data)
    {
        $topNodeName = self::getApp()->config("xml-top-node");
        $nodeName = self::getApp()->config("xml-node");
        return self::generateValidXmlFromArray((array) $data, $topNodeName, $nodeName);
    }

    private static function generateValidXmlFromObj(stdClass $obj, $node_block = 'nodes', $node_name = 'node')
    {
        $arr = (array) $obj;
        return self::generateValidXmlFromArray($arr, $node_block, $node_name);
    }

    private static function generateValidXmlFromArray($array, $node_block = 'nodes', $node_name = 'node')
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    private static function generateXmlFromArray($array, $node_name)
    {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }

                $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $node_name) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }

}
