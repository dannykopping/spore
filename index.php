<?php
	require_once "vendor/autoload.php";
	require_once "Spore/Spore.php";

	use Slim\Slim;
	use Spore\ReST\Data\Middleware\Response;
	use Spore\Spore;
	use Spore\ReST\Controller;
	use Spore\Config\Configuration;

	@session_start();

	\Spore\Spore::registerAutoloader();
    $app = new Spore();

    // options
    $app->config("debug", Configuration::get("debug"));

    $controller = Controller::getInstance();
    $controller->setApp($app);

    // by default, allow any role to access all API operations
    $controller->setAuthCallback(function($roles) use ($app)
    {
        // if no roles are defined, allow
        if(empty($roles))
            return true;

        // if an operation has an "@auth debug" annotation,
        // and DEBUG MODE is enabled, allow
        return Configuration::get("debug") === true && in_array("debug", $roles);
    });

    // get and require all PHP services (@see the 'services' config setting)
    $classes = $controller->getAllPHPServices();

    // auto-routing must always be declared AFTER authorization callback
    $controller->addAutoRouting($classes);
    $app->add(new Response());

    // run Slim!
    $app->run();
?>