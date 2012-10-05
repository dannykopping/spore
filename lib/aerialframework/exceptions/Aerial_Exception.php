<?php 
    class Aerial_Exception extends Exception
    {
        const CONNECTION = "Error connecting to database";
		const SERVER_UNRESPONSIVE = "The server you're attempting to connect to is unresponsive";
	    const UNKNOWN = "Unknown exception";

		public $debug;

	    public function __construct($message, $debug=null, Exception $ex=null)
	    {
			if(!empty($ex))
				$message = $ex->getMessage();

			$this->message = $message ? $message : self::UNKNOWN;
			$this->code = ($ex ? $ex->getCode() : 0);

			if(Configuration::get("DEBUG_MODE"))
				$this->debug = $debug;
	    }

		public static function parseIntegrityConstraint($message)
		{
			$error = "Cannot add or update a child row: a foreign key constraint fails ";
			$message = substr($message, strlen($error));		// cut out error message to get to the message
			if (preg_match('/\(`\w+`.`\w+`, CONSTRAINT `\w+` FOREIGN KEY '.
								'\(`(\w+)`\) REFERENCES `(\w+)` \(`(\w+)`\)/', $message, $parts))
			{
				$message = "A relation error occured on the foreign key \"".$parts[1]."\"";
			}

			return $message;
		}
    }
?>