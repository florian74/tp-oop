<?php
include __DIR__ . '/../business/IReader.php';
include __DIR__ . '/../business/AtomReader.php';
include __DIR__ . '/../business/RSSReader.php';
include __DIR__ . '/../business/FeedUpdater.php';
include __DIR__ . '/../business/ReaderManager.php';
include __DIR__ . '/../model/Feed.php';
include __DIR__ . '/../model/Entry.php';
include __DIR__ . '/../DAL/connection.php';
include __DIR__ . '/../DAL/ArticlesManager.php';

class FeedUpdaterTest extends PHPUnit_Framework_TestCase {

    public function testAddFeed()
    {
        $updater = FeedUpdater::getInstance();

        $updater->resetDB();

        $updater->addFeed("https://www.guildwars2.com/fr/feed/","jeu video");


        $dbmanager = new ArticlesManager("articles");
        $feedmanager= new ArticlesManager("feeds");
        $this->assertNotNull(count($dbmanager->getAllEntries()));
        $this->assertNotNull(count($feedmanager->getAllEntries()));

    }


    /**
     * @depends testAddFeed
     */
    public function testDeleteFeed() {

        $dbmanager = new ArticlesManager("articles");
        $feedmanager= new ArticlesManager("feeds");

        $updater = FeedUpdater::getInstance();

        $feeds = $feedmanager->getAllEntries();
        $updater->deleteFeed($feeds[0]);

        $this->assertEquals(0, count($dbmanager->getAllEntries()));
        $this->assertEquals(0, count($feedmanager->getAllEntries()));

    }

    /**
     * @depends testDeleteFeed
     */
    public function testUpdateFeed()
    {
        $updater = FeedUpdater::getInstance();

        $updater->resetDB();

        $updater->addFeed("https://www.guildwars2.com/fr/feed/","jeu video");
        echo "\n";
        $updater->addFeed("http://www.lemonde.fr/japon/rss_full.xml","actualite");
      //  $updater->addFeed("http://feeds.betacie.com/viedemerde","humour");
      //  $updater->addFeed("https://linuxfr.org/news.atom","veille");


        $feedmanager= new ArticlesManager("feeds");
        $feeds = $feedmanager->getAllEntries();
        $date = $feeds[0]->updateDate;

        sleep(10);


        $updater->updateAllFeed();

       $feeds = $feedmanager->getAllEntries();

        $this->assertGreaterThan( 8, ( strtotime($feeds[0]->updateDate) - strtotime($date) ));

        $updater->resetDB();



    }

}
