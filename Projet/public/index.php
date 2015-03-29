<?php



require  __DIR__. '/../vendor/slim/slim/Slim/Slim.php';
require  __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../business/IReader.php';
include __DIR__ . '/../business/AtomReader.php';
include __DIR__ . '/../model/Feed.php';
include __DIR__ . '/../model/Entry.php';
include __DIR__ . '/../business/RSSReader.php';
include __DIR__ . '/../business/FeedUpdater.php';
include __DIR__ . '/../DAL/connection.php';
include __DIR__ . '/../DAL/ArticlesManager.php';
include __DIR__ . '/../view/AffichageArticles.php';
include __DIR__ . '/../view/DetailArticle.php';
require __DIR__ . '/../vendor/twig/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();




\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/read/:nb', function ( $nb) {

	$loader = new Twig_Loader_Filesystem( __DIR__ . '/../view');
	$twig = new Twig_Environment($loader);

	$article = FeedUpdater::getInstance()->getSpecificEntries("number", $nb);

	echo $twig->render('article.html', array("article" => $article[0] ));
});

$app->get('/hello', function () { echo 'hello'; });

$app->get('/app',function() {

	$loader = new Twig_Loader_Filesystem( __DIR__ . '/../view');
	$twig = new Twig_Environment($loader);

	$FeedUpdater = FeedUpdater::getInstance();


	echo $twig->render('mainView.html', array("articles" => $FeedUpdater->getAllArticle() ));

	//$aff = new AffichageArticles();
	//$aff->listingArticle(" ");

});

$app->put('/update/:url/:description', function($url , $description) {
	$FeedUpdater = FeedUpdater::getInstance();

	$FeedUpdater->addFeed($url, $description);

});

$app->get('/nav',function(){
	$loader = new Twig_Loader_Filesystem( __DIR__ . '/../view');
	$twig = new Twig_Environment($loader);

	$FeedUpdater = FeedUpdater::getInstance();


	echo $twig->render('navigation.html', array("articles" => $FeedUpdater->getAllArticle() ));
});
$app->run();

