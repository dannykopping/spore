<?php

    require_once dirname(__FILE__) . "/BaseDeserializer.php";

    class XMLDeserializer extends BaseDeserializer
    {
        /**
         * Parse XML
         *
         * This method creates a SimpleXMLElement
         * based upon the XML input. If the SimpleXML
         * extension is not available, the raw input
         * will be returned unchanged.
         *
         * @param   string $data
         * @return  SimpleXMLElement|string
         */
        public static function parse($data)
        {
            if(class_exists('SimpleXMLElement'))
            {
                try
                {
                    // read XML, merge CDATA elements into text nodes
                    $xml = new SimpleXMLElement($data, LIBXML_NOCDATA);
                    $obj = new stdClass();
                    $obj = self::xmlToObject($xml, $obj);

                    return $obj;
                } catch(Exception $e)
                {
                    throw new Exception("Unable to parse input data as XML.<br/>" . $e->getMessage());
                }
            }
            return $data;
        }

        private static function xmlToObject(SimpleXMLElement $element, $object=null)
        {
            if(!$object)
                $object = new stdClass();

            $attributes = (array) $element->attributes();
            $children = (array) $element->children();

            if(!empty($attributes) && !empty($children))
                $subElements = array_merge($attributes["@attributes"], $children);
            else if(empty($attributes))
                $subElements = $children;
            else
                $subElements = $attributes;

            if(!empty($subElements))
            {
                foreach($subElements as $key => $value)
                {
                    $object->$key = self::elementToObject($value);
                }
            }

            return $object;
        }

        private static function elementToObject($element)
        {
            if(is_scalar($element))
            {
                return is_numeric((string) $element) ? (int) $element : (string) $element;
            }
            else if(is_a($element, "SimpleXMLElement"))
            {
                return self::xmlToObject($element);
            }
            else if(is_array($element))
            {
                $elements = array();
                foreach($element as $subElement)
                {
                    $elements[] = self::elementToObject($subElement);
                }

                return $elements;
            }

            return null;
        }
    }