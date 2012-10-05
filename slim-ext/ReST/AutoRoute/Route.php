<?php
	namespace ReST\AutoRoute;

	use ReST\AutoRoute\Util\RouteDescriptor;

	/**
     *    A Value-Object that defines what a "route" is
     *
     * @property string     uri
     * @property array      arguments
     * @property array      methods
     * @property array      callback
     * @property array      auth
     */
    class Route
    {
        private $_uri;
        private $_arguments;
        private $_authorizedUsers;
        private $_methods;
        private $_callback;

        private $_descriptor;

        public function __construct(RouteDescriptor $descriptor)
        {
            $this->_descriptor = $descriptor;
        }

        /**
         * Setter super method to manipulate variables as they are assigned a value
         *
         * @param $name
         * @param $value
         */
        public function __set($name, $value)
        {
            switch ($name)
            {
                case "methods":
                case "auth":
                    if (empty($value))
                        break;

                    foreach ($value as &$item)
                    {
                        // if the item in the array is a string, trim the extraneous whitespace from it
                        if (is_string($item) && !empty($value))
                            $item = trim($item);
                    }

                    $this->{"_" . $name} = $value;

                    break;
                default:
                    $this->{"_" . $name} = is_string($value) ? trim($value) : $value;
                    break;
            }
        }

        /**
         *    Getter super method to retrieve a variable
         *
         * @param $name
         * @return mixed
         */
        public function __get($name)
        {
            return $this->{"_" . $name};
        }

        public function __toString()
        {
            return $this->getUri() . "\n" .
                print_r($this->getArguments(), true) . "\n" .
                print_r($this->getMethods(), true) . "\n";
        }


        // GETTERS & SETTERS

        public function setArguments($arguments)
        {
            $this->_arguments = $arguments;
        }

        public function getArguments()
        {
            return $this->_arguments;
        }

        public function setMethods($methods)
        {
            $this->_methods = $methods;
        }

        public function getMethods()
        {
            return $this->_methods;
        }

        public function setUri($uri)
        {
            $this->_uri = $uri;
        }

        public function getUri()
        {
            return $this->_uri;
        }

        public function setCallback($callback)
        {
            $this->_callback = $callback;
        }

        public function getCallback()
        {
            return $this->_callback;
        }

        public function setAuthorizedUsers($authorizedUsers)
        {
            $this->_authorizedUsers = $authorizedUsers;
        }

        public function getAuthorizedUsers()
        {
            return $this->_authorizedUsers;
        }

        public function getDescriptor()
        {
            return $this->_descriptor;
        }
    }

?>