<?php
	class Encryption
	{
		public static function decrypt(ByteArray $data)
		{
			$key = self::strToHex($_SESSION["KEY"]);
			if(!$key || strlen($key) == 0)
				throw new Aerial_Encryption_Exception(Aerial_Encryption_Exception::INVALID_KEY_ERROR);
			
			try
			{
				$decrypted = rc4crypt::decrypt($key, self::hex2bin($data->data), 1);
			}
			catch(Exception $e)
			{
				throw new Aerial_Encryption_Exception(Aerial_Encryption_Exception::DECRYPTION_ERROR);
			}

			return $decrypted;
		}
	
		public static function encrypt($amf)
		{
			$key = self::strToHex($_SESSION["KEY"]);
			if(!$key || strlen($key) == 0)
				throw new Aerial_Encryption_Exception(Aerial_Encryption_Exception::INVALID_KEY_ERROR);

			try
			{
				$encrypted = rc4crypt::encrypt($key, self::hex2bin($amf), 1);
			}
			catch(Exception $e)
			{
				throw new Aerial_Encryption_Exception(Aerial_Encryption_Exception::ENCRYPTION_ERROR);
			}

			return $encrypted;
		}

		public static function strToHex($string)
		{
			$hex='';
			for ($i=0; $i < strlen($string); $i++)
			{
				$hex .= dechex(ord($string[$i]));
			}

			return $hex;
		}
	
		public static function hex2bin($str)
		{
			$bin = "";
			$i = 0;
			do
			{
				$bin .= chr(hexdec($str{$i} . $str{($i + 1)}));
				$i += 2;
			}
			while ($i < strlen($str));
			
			return $bin;
		}

		public static function canUseEncryption()
		{
			if(!session_start())
				return false;

			if($_SESSION["KEY"] && ConfigXml::getInstance()->useEncryption)
				return true;
			else
				return false;
		}
	}
?>