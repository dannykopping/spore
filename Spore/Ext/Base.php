<?php
	namespace Spore\Ext;

	use Slim\Slim;

	/**
	 *
	 */
	abstract class Base
	{
		protected $slimInstance;

        protected $args;

		public function __construct(Slim &$slimInstance, $args=null)
		{
			$this->slimInstance =& $slimInstance;

            if(!empty($args))
                $this->args = $args;
		}

		/**
		 * @return Slim
		 */
		protected function getSlimInstance()
		{
			return $this->slimInstance;
		}
	}
