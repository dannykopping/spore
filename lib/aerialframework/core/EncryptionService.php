<?php
	import('aerialframework.encryption.Encrypted');
	
	class EncryptionService
	{
		private $keyResource;

		public function startSession($encrypted)
		{
			if($encrypted["_explicitType"] != "org.aerialframework.encryption.EncryptedVO")
				throw new Aerial_Encryption_Exception(Aerial_Encryption_Exception::INVALID_KEY_ERROR);

			$e = new Encrypted();
			$e->data = $encrypted["data"];

			$sessionKey = $this->decryptRSA($e->data->data);
			openssl_free_key($this->keyResource);

			if(!$sessionKey || strlen($sessionKey) == 0)
				throw new Aerial_Encryption_Exception(Aerial_Encryption_Exception::INVALID_KEY_ERROR);

			if(!session_start())
				session_start();

			$_SESSION["KEY"] = $sessionKey;
			return true;
		}

		private function decryptRSA($bytes)
		{
			if(!$this->keyResource)
			{
				try
				{
					$fp = fopen(LIB_PATH . DIRECTORY_SEPARATOR . "encryption" ."exchange.key", "r");

					$priv_key = fread($fp, 8192);
					fclose($fp);
					// $passphrase is required if your key is encoded (suggested)
					$this->keyResource = openssl_get_privatekey($priv_key);
					$details = openssl_pkey_get_details($this->keyResource);
				}
				catch(Exception $e)
				{
					throw new Aerial_Encryption_Exception(Aerial_Encryption_Exception::PRIVATE_KEY_INVALID_ERROR);
				}
			}

			if($this->keyResource == null)
				throw new Aerial_Encryption_Exception(Aerial_Encryption_Exception::PRIVATE_KEY_INVALID_ERROR);

			$keyBits = $details["bits"];
			$blockSize = $keyBits / 8;

			$raw = $bytes;

			$raw = substr($raw, 0, strlen($raw) - 1);
			$pieces = explode("|", $raw);

			$decryptedKey = "";

			foreach($pieces as $piece)
			{
				$piece = $this->hex2bin($piece);

				try
				{
					openssl_private_decrypt($piece, $decrypted, $this->keyResource);
				}
				catch(Exception $e)
				{
					throw new Aerial_Encryption_Exception(Aerial_Encryption_Exception::DECRYPTION_ERROR);
				}

				$decryptedKey .= $decrypted;
			}

			return $decryptedKey;
		}

		private function hex2bin($str)
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
	}
?>