<?php
	require_once "vendor/autoload.php";
	require_once "Spore/Spore.php";

	use Slim\Slim;
	use Spore\Spore;
	use Spore\ReST\Controller;
	use Spore\Config\Configuration;

	\Spore\Spore::registerAutoloader();
    $app = new Spore();
	$app->setAuthCallback(function($roles) use ($app)
	{
		if(empty($roles))
            return true;

        // implement some logic here to return true or false based on a role name
		return true;
	});

    // run Slim!
    $app->run();
?>