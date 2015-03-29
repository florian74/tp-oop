<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 25/03/2015
 * Time: 15:26
 */
include __DIR__ . '/../business/IReader.php';
include __DIR__ . '/../business/RSSReader.php';
include __DIR__ . '/../business/AtomReader.php';
include __DIR__ . '/../business/ReaderManager.php';
include __DIR__ . '/../model/Feed.php';
include __DIR__ . '/../model/Entry.php';
include __DIR__ . '/../DAL/Connection.php';
include __DIR__ . '/../DAL/ArticlesManager.php';


class ReaderManagerTest extends PHPUnit_Framework_TestCase {

    public function testReader() {

        $reader = ReaderManager::getInstance()->getReader("http://www.lemonde.fr/japon/rss_full.xml");
        $this->assertEquals(get_class($reader), "RSSReader");

        $reader = ReaderManager::getInstance()->getReader("https://www.guildwars2.com/fr/feed/");
        $this->assertEquals(get_class($reader), "RSSReader");

        $reader = ReaderManager::getInstance()->getReader("http://feeds.betacie.com/viedemerde");
        $this->assertEquals(get_class($reader), "AtomReader");

        $reader = ReaderManager::getInstance()->getReader("https://linuxfr.org/news.atom");

        $this->assertEquals(get_class($reader), "AtomReader");

    }
}
