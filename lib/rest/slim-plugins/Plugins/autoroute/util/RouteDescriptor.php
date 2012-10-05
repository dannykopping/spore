<?php
    class RouteDescriptor
    {
        private $_annotations;
        private $_method;
        private $_reflectionMethod;

        /**
         * @param array $annotations
         * @param MethodElement $method
         */
        public function __construct($annotations, MethodElement $method)
        {
            $this->_annotations = $annotations;
            $this->_reflectionMethod = $method->getReflectionObject();
            $this->_method = $method;
        }

        public function setAnnotations($annotations)
        {
            $this->_annotations = $annotations;
        }

        public function getAnnotations()
        {
            return $this->_annotations;
        }

        public function setReflectionMethod($reflectionMethod)
        {
            $this->_reflectionMethod = $reflectionMethod;
        }

        public function getReflectionMethod()
        {
            return $this->_reflectionMethod;
        }

        public function setMethod($method)
        {
            $this->_method = $method;
        }

        public function getMethod()
        {
            return $this->_method;
        }
    }
?>