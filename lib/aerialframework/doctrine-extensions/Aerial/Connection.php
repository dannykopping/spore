<?php
	class Aerial_Connection extends Doctrine_Connection_Common
	{
		/**
		 * @var array $modules                      an array containing all modules
		 *              transaction                 Doctrine_Transaction driver, handles savepoint and transaction isolation abstraction
		 *
		 *              expression                  Doctrine_Expression_Driver, handles expression abstraction
		 *
		 *              dataDict                    Doctrine_DataDict driver, handles datatype abstraction
		 *
		 *              export                      Doctrine_Export driver, handles db structure modification abstraction (contains
		 *                                          methods such as alterTable, createConstraint etc.)
		 *              import                      Doctrine_Import driver, handles db schema reading
		 *
		 *              sequence                    Doctrine_Sequence driver, handles sequential id generation and retrieval
		 *
		 *              unitOfWork                  Doctrine_Connection_UnitOfWork handles many orm functionalities such as object
		 *                                          deletion and saving
		 *
		 *              formatter                   Doctrine_Formatter handles data formatting, quoting and escaping
		 *
		 * @see Doctrine_Connection::__get()
		 * @see Doctrine_DataDict
		 * @see Doctrine_Expression_Driver
		 * @see Doctrine_Export
		 * @see Doctrine_Transaction
		 * @see Doctrine_Sequence
		 * @see Doctrine_Connection_UnitOfWork
		 * @see Doctrine_Formatter
		 */
		private $modules = array('transaction' => false,
								 'expression'  => false,
								 'dataDict'    => false,
								 'export'      => false,
								 'import'      => false,
								 'sequence'    => false,
								 'unitOfWork'  => false,
								 'formatter'   => false,
								 'util'        => false,
								 );

		public function connect()
		{
			if ($this->isConnected) {
				return false;
			}

			$event = new Doctrine_Event($this, Doctrine_Event::CONN_CONNECT);

			$this->getListener()->preConnect($event);

			$e     = explode(':', $this->options['dsn']);
			$this->driverName = $e[0];

			$found = false;

			if (extension_loaded('pdo')) {
				if (in_array($e[0], self::getAvailableDrivers())) {
					try {
						$this->dbh = new PDO($this->options['dsn'], $this->options['username'],
										 (!$this->options['password'] ? '':$this->options['password']), $this->options['other']);

						$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					} catch (PDOException $e) {
						switch($e->getCode())
						{
							case 2002:						// unresponsive server (server daemon has died)
								$info = $this->getManager()->parsePdoDsn($this->options["dsn"]);
								throw new Aerial_Exception(Aerial_Exception::SERVER_UNRESPONSIVE,
											array("server" => $info["host"],
													"port" => $info["port"] == null ? "default" : $info["port"]),
										$e);
								break;
							case 1045:
								throw new Aerial_Exception(Aerial_Exception::CONNECTION,
										array("username" => $this->options['username'],
												"password" => preg_replace("%.%", "*", $this->options['password'])),
										$e);
								break;
							default:
								throw new Aerial_Exception(Aerial_Exception::UNKNOWN,
										array("message" => $e->getMessage(), "code" => $e->getCode()),
										$e);
								break;
						}
					}
					$found = true;
				}
			}

			if ( ! $found) {
				$class = 'Doctrine_Adapter_' . ucwords($e[0]);

				if (class_exists($class)) {
					$this->dbh = new $class($this->options['dsn'], $this->options['username'], $this->options['password'], $this->options);
				} else {
					throw new Doctrine_Connection_Exception("Couldn't locate driver named " . $e[0]);
				}
			}

			// attach the pending attributes to adapter
			foreach($this->pendingAttributes as $attr => $value) {
				// some drivers don't support setting this so we just skip it
				if ($attr == Doctrine_Core::ATTR_DRIVER_NAME) {
					continue;
				}
				$this->dbh->setAttribute($attr, $value);
			}

			$this->isConnected = true;

			$this->getListener()->postConnect($event);
			return true;
		}

		/**
		 * returns the name of the connected database
		 *
		 * @return string
		 */
		public function getDatabaseName()
		{
			return $this->fetchOne('SELECT DATABASE()');
		}

		/**
		 * rethrowException
		 *
		 * @throws Doctrine_Connection_Exception
		 */
		public function rethrowException(Exception $e, $invoker, $query = null)
		{
			$event = new Doctrine_Event($this, Doctrine_Event::CONN_ERROR);

			$this->getListener()->preError($event);

			$name = 'Doctrine_Connection_' . ucwords($this->driverName) . '_Exception';

			$message = $e->getMessage();
			if ($query) {
				$message .= sprintf('. Failing Query: "%s"', $query);
			}

			$exc  = new $name($message, (int) $e->getCode());
			if ( ! isset($e->errorInfo) || ! is_array($e->errorInfo)) {
				$e->errorInfo = array(null, null, null, null);
			}
			$exc->processErrorInfo($e->errorInfo);

			if ($this->getAttribute(Doctrine_Core::ATTR_THROW_EXCEPTIONS)) {

				$shortError = "";
				switch($e->errorInfo[1])
				{
					case 1452:			// integrity constraint violation - simplify MySQL's cryptic message 
						$shortError = Aerial_Exception::parseIntegrityConstraint($e->errorInfo[2]);
						break;
					default:
						$shortError = $e->errorInfo[2];
						break;
				}

				throw new Aerial_Exception($exc->getPortableMessage(),
						array("PDO Error Code" => $exc->getCode(),
								"Database (".$this->driverName.") Error Code" => $e->errorInfo[1],
								"Short Error" => $shortError,
								"Full Error" => $exc->getMessage()),
						$exc);
			}

			$this->getListener()->postError($event);
		}

		/**
		 * __get
		 * lazy loads given module and returns it
		 *
		 * @see Doctrine_DataDict
		 * @see Doctrine_Expression_Driver
		 * @see Doctrine_Export
		 * @see Doctrine_Transaction
		 * @see Doctrine_Connection::$modules       all availible modules
		 * @param string $name                      the name of the module to get
		 * @throws Doctrine_Connection_Exception    if trying to get an unknown module
		 * @return Doctrine_Connection_Module       connection module
		 */
		public function __get($name)
		{
			if (isset($this->properties[$name])) {
				return $this->properties[$name];
			}

			if(!$this->getDriverName())
			{
				$e     = explode(':', $this->options['dsn']);
				$this->driverName = $e[0];
			}

			if ( ! isset($this->modules[$name])) {
				throw new Doctrine_Connection_Exception('Unknown module / property ' . $name);
			}
			if ($this->modules[$name] === false) {
				switch ($name) {
					case 'unitOfWork':
						$this->modules[$name] = new Doctrine_Connection_UnitOfWork($this);
						break;
					case 'formatter':
						$this->modules[$name] = new Doctrine_Formatter($this);
						break;
					default:
						$class = 'Doctrine_' . ucwords($name) . '_' . ucwords($this->getDriverName());
						$this->modules[$name] = new $class($this);
					}
			}

			return $this->modules[$name];
		}
	}
?>