<?php
	namespace Spore\ReST\Data\Deserializer;

    /**
	 *	The Base deserialization class
	 */
	abstract class Base
    {
		/**
		 * Parse the given data from an encoding
		 *
		 * @param $data
		 */
		public static function parse($data){}
    }
