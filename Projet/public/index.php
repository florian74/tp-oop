<?php

require  __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../business/IReader.php';
include __DIR__ . '/../business/AtomReader.php';
include __DIR__ . '/../model/Feed.php';
include __DIR__ . '/../model/Entry.php';
include __DIR__ . '/../business/RSSReader.php';
include __DIR__ . '/../DAL/connection.php';
include __DIR__ . '/../DAL/ArticlesManager.php';

$app = new \Slim\Slim();

$app->get('/hello', function () { echo 'hello'; });

$app->get('/app/',function() {
	
	$oop = new AtomReader;
	//$oop = new RSSReader();
	$oop->read();

	$ArticlesM = new ArticlesManager();
	$ArticlesM->getAllEntries();
	$ArticlesM->insertEntry($oop);
	
});

$app->run();

