<?php
    /**
     *    Defines an annotation defined in a DocBlock
     */
    class AnnotationElement extends AbstractElement
    {
        /**
         * @var    string    The name of the annotation (e.g. @param)
         */
        public $name;

        /**
         * @var array    The values associated with this annotation
         */
        public $values = array();

        /**
         * @var    MethodElement    The associated MethodElement instance to which this annotation belongs
         */
        private $method;

        /**
         * @param $method    MethodElement    The associated MethodElement instance to which this annotation belongs
         */
        public function __construct($method)
        {
            $this->method = $method;
        }

        /**
         * @return MethodElement    The associated MethodElement instance to which this annotation belongs
         */
        public function getMethod()
        {
            return $this->method;
        }

        /**
         * @override
         *
         * @param Reflector $reflectionObject
         * @throws Exception
         */
        public function setReflectionObject(Reflector $reflectionObject)
        {
            throw new Exception("Annotations do not have corresponding Reflection objects");
        }

        /**
         * @override
         *
         * @throws Exception
         */
        public function getReflectionObject()
        {
            throw new Exception("Annotations do not have corresponding Reflection objects");
        }
    }
