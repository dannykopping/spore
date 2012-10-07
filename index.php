<?php
	require_once "vendor/autoload.php";

	use Spore\Spore;

	$app = new Spore();
	$app->get("/", function ()
	{
		return array("message" => "Hello World from Spore");
	});

	$app->run();