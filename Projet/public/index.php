<?php

require  __DIR__ . '/../vendor/autoload.php';

include __DIR__ . '/../business/AtomReader.php';
include __DIR__ . '/../business/RSSReader.php';


$app = new \Slim\Slim();

$app->get('/hello', function () { echo 'hello'; });

$app->get('/app/',function() {
	
	//$oop = new AtomReader;
	$oop = new RSSReader();
	$oop->read();
	
	
});

$app->run();

