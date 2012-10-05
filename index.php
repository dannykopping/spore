<?php
	require_once "vendor/autoload.php";

    require_once dirname(__FILE__) . "/config/config.php";
    require_once dirname(__FILE__) . "/lib/rest/rest.php";

	use Slim\Slim;

	@session_start();

    $app = new Slim();

    // options
    $app->config("debug", Configuration::get("debug"));

    $controller = RESTController::getInstance();
    $controller->setApp($app);

    // by default, allow any role to access all API operations
    $controller->setAuthCallback(function($roles) use ($app)
    {
        // if no roles are defined, allow
        if(empty($roles))
            return true;

        // if an operation has an "@authorize debug" annotation,
        // and DEBUG MODE is enabled, allow
        return Configuration::get("debug") === true && in_array("debug", $roles);
    });

    // get and require all PHP services located in the PHP_SERVICES directory
    $classes = $controller->getAllPHPServices();

    // auto-routing must always be declared AFTER authorization callback
    $controller->addAutoRouting($classes);

    // run Slim!
    $app->run();

	class Bob
	{
		/**
		 * @var bob
		 */
		public $bob;

		/**
		 * @param Bob
		 */
		public function bob()
		{

		}
	}
?>