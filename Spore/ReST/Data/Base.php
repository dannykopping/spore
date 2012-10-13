<?php
	namespace Spore\ReST\Data;

    /**
	 *	The Base serialization/deserialization class
	 */
	abstract class Base
    {
		/**
		 * Parse the given data to/from an encoding
		 *
		 * @param $data
		 */
		public static function parse($data){}
    }
