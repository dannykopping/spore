<?php
	require_once "vendor/autoload.php";

	use Spore\Spore;

    $app = new Spore();
	$app->setAuthCallback(function($roles) use ($app)
	{
		if(empty($roles))
            return true;

        // implement some logic here to return true or false based on a role name
		return in_array("debug", $roles);
	});

    $app->run();