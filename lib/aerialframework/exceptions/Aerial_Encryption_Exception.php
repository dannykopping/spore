<?php
	class Aerial_Encryption_Exception extends Exception
	{
	    const INVALID_KEY_ERROR = "The provided encryption key is invalid";
		const PRIVATE_KEY_INVALID_ERROR = "The private RSA key cannot be found or is invalid";
		const DECRYPTION_ERROR = "An error occurred while attempting to decrypt encrypted data";
		const ENCRYPTION_ERROR = "An error occurred while attempting to encrypt data";
		const AMF_ENCODING_ERROR = "An error occurred while encoding an AMF stream";
		const AMF_DECODING_ERROR = "An error occurred while encoding an AMF stream";
		const ENCRYPTION_NOT_USED_ERROR = "Encryption has been enabled but a regular request was issued.\nPlease either disable encryption in your configuration file or send an encrypted request";
		
	    const UNKNOWN = "Unknown exception";

	    public function __construct($message, $debug=null, Exception $ex=null)
	    {
			if(($message == null || $message == "unknown error") && $ex != null)
				$message = $ex->getMessage();

			$this->message = $message ? $message : self::UNKNOWN;
			$this->code = ($ex ? $ex->getCode() : 0);
	    }
}
?>