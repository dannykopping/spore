<?php
    class RouteAnnotation
    {
        private $_name;
        private $_value;

        public function __construct($name, $value)
        {
            $this->_name = $name;
            $this->_value = $value;
        }

        public function setName($name)
        {
            $this->_name = $name;
        }

        public function getName()
        {
            return $this->_name;
        }

        public function setValue($value)
        {
            $this->_value = $value;
        }

        public function getValue()
        {
            return $this->_value;
        }
    }
?>