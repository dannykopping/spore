<?php
	require_once "vendor/autoload.php";

	use Spore\Spore;
	use Slim\Extras\Views\Twig;

	$twigView             = new \Slim\Extras\Views\Twig();
	Twig::$twigExtensions = array(
		'Twig_Extensions_Slim',
	);

	$app = new Spore(array(
						  'view' => $twigView
					 ));

	$app->get("/", function ()
	{
		return array("message" => "Hello World from Spore");
	});

	$app->run();