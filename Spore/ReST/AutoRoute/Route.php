<?php
	namespace Spore\ReST\AutoRoute;

	use Spore\ReST\AutoRoute\Util\RouteDescriptor;
	use Exception;

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
        private $_name;
        private $_uri;
        private $_arguments;
        private $_authorizedUsers;
        private $_methods;
		private $_template;
		private $_render;
		private $_callback;

        private $_descriptor;

        public function __construct(RouteDescriptor $descriptor)
        {
            $this->_descriptor = $descriptor;
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

		public function setTemplate($template)
		{
			$this->_template = $template;
		}

		public function getTemplate()
		{
			return $this->_template;
		}

		public function setRender($render)
		{
			$normalizedValue = strtolower($render);
			$acceptable = array("always", "nonxhr", "nonajax", "non-xhr", "non-ajax", "never");

			if(!in_array($normalizedValue, $acceptable))
				throw new Exception("$render is not a valid option for the @".AutoRouter::RENDER." annotation");

			$this->_render = $normalizedValue;
		}

		public function getRender()
		{
			return $this->_render;
		}

		public function setName($name)
		{
			$this->_name = trim($name);
		}

		public function getName()
		{
			return $this->_name;
		}
	}

?>