<?php
    /**
     *    Defines a class element with several parsed MethodElement instances
     */
    class ClassElement extends AbstractElement
    {
        public $name;
        private $methods;

        /**
         * @param MethodElement $method        Add a parsed method to this class
         */
        public function addMethod(MethodElement $method)
        {
            if (empty($this->methods))
                $this->methods = array();

            $this->methods[] = $method;
        }

        /**
         * Get an array of MethodElement instances
         *
         * @return array[MethodElement]
         */
        public function getMethods()
        {
            return $this->methods;
        }
    }
