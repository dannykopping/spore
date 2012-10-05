<?php
	/**
	 *	The basic functionality of a Slim plugin contained herein
	 */
	abstract class Slim_Plugin_Base
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
?>