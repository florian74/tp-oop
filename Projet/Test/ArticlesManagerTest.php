<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 24/03/2015
 * Time: 19:28
 */
include __DIR__ . '/../business/IReader.php';
include __DIR__ . '/../business/AtomReader.php';
include __DIR__ . '/../business/RSSReader.php';
include __DIR__ . '/../model/Feed.php';
include __DIR__ . '/../model/Entry.php';
include __DIR__ . '/../DAL/connection.php';
include __DIR__ . '/../DAL/ArticlesManager.php';


class ArticlesManagerTest extends PHPUnit_Framework_TestCase {

    public function testOneAndMultiInsert()
    {

       // $ArticlesManager = new ArticlesManager();
        $ArticlesManager = new ArticlesManager("articles");
        $ArticlesManager->clean();


        $articles = $ArticlesManager->getAllEntries();
        $count = count($articles);

        $article = new Entry();
        $article->alreadyRead = 0;
        $article->id = 0;
        $article->author="test";
        $article->extra=0;
        $article->publicationDate= date("Y-m-d H:i:s");
        $article->updateDate =  date("Y-m-d H:i:s");



        $ArticlesManager->insertEntry($article);

        $articles = $ArticlesManager->getAllEntries();
        $this->AssertEquals($count + 1 ,count($articles) );

        //should fail -> two times inserting the same article
        try {
            $ArticlesManager->insertEntry($article);
        }
        catch (Exception $e)
        {

        }

        $articles = $ArticlesManager->getAllEntries();
        $this->AssertEquals($count + 1 ,count($articles) );

        $articles = array();

        $add = 5;
        for ($i=0; $i < $add ; $i++) {
            $article = new Entry();
            $article->alreadyRead = 0;
            $article->id = $i+1;
            $article->author = "test";
            $article->extra = 0;
            $article->publicationDate = date("Y-m-d H:i:s");
            $article->updateDate = date("Y-m-d H:i:s");

            $articles[] = $article;
        }

        $ArticlesManager->insertEntries($articles);

        $articles =  $ArticlesManager->getAllEntries();
        $this->AssertEquals($count + 1 + $add ,count($articles) );


        $AtomReader = new AtomReader();

        //http :
        $articles = $AtomReader->read_url_until_date("http://feeds.betacie.com/viedemerde",strtotime("20 mars 2015"));
        $ArticlesManager->insertEntries($articles);

        //https :
        $articles = $AtomReader->read_url_until_date("http://linuxfr.org/news.atom",strtotime("20 mars 2015"));
        $ArticlesManager->insertEntries($articles);

    }

    /**
     * @depends testOneAndMultiInsert
     */
    public function testDelete()
    {
        //$ArticlesManager = new ArticlesManager();
        $ArticlesManager = new ArticlesManager("articles");

        $articles = $ArticlesManager->getAllEntries();
        $count = count($articles);

        $article = new Entry();
        $article->alreadyRead = 0;
        $article->id = 0;
        $article->author="test";
        $article->extra=0;
        $article->publicationDate= date("Y-m-d H:i:s");
        $article->updateDate =  date("Y-m-d H:i:s");

        $ArticlesManager->Delete("id", $article->id);
        $articles = $ArticlesManager->getAllEntries();
        $this->assertEquals($count-1, count($articles));

        $articles = array();

        $add = 5;
        for ($i=0 ; $i < $add ; $i++) {
            $article = new Entry();
            $article->alreadyRead = 0;
            $article->id = $i+1;
            $article->author = "test";
            $article->extra = 0;
            $article->publicationDate = date("Y-m-d H:i:s");
            $article->updateDate = date("Y-m-d H:i:s");

            $articles[] = $article;
        }

        $ArticlesManager->deleteEntries( $articles);
        $articles = $ArticlesManager->getAllEntries();
        $this->assertEquals($count-1-$add, count($articles));


        $ArticlesManager->delete("feed", "http://feeds.betacie.com/viedemerde");
        $ArticlesManager->delete("feed","http://linuxfr.org/news.atom");
        $articles = $ArticlesManager->getAllEntries();
        $this->assertEquals(0, count($articles));

    }

    /**
     * @depends testDelete
     */
    public function testCoExistenceAtomRSS()
    {
       // $ArticlesManager = new ArticlesManager();
        $ArticlesManager = new ArticlesManager("articles");

        $RSSReader = new RSSReader();
        $AtomReader = new AtomReader();

        //http :
        $articles = $RSSReader->read_url_until_date("http://www.lemonde.fr/japon/rss_full.xml",strtotime("15 mars 2015"));
        $ArticlesManager->insertEntries($articles);
        $count = count($articles);
        $articles = $ArticlesManager->getAllEntries();
        $this->assertEquals($count, count($articles));

        //https :
        $articles = $RSSReader->read_url_until_date("https://www.guildwars2.com/fr/feed/",strtotime("15 mars 2015"));
        $ArticlesManager->insertEntries($articles);

        $count = $count + count($articles);
        $articles = $ArticlesManager->getAllEntries();
        $this->assertEquals($count, count($articles));

        //http :
        $articles = $AtomReader->read_url_until_date("http://feeds.betacie.com/viedemerde",strtotime("20 mars 2015"));
        $ArticlesManager->insertEntries($articles);
        $count = $count + count($articles);
        $articles = $ArticlesManager->getAllEntries();
        $this->assertEquals($count, count($articles));

        //https :
        $articles = $AtomReader->read_url_until_date("http://linuxfr.org/news.atom",strtotime("20 mars 2015"));
        $ArticlesManager->insertEntries($articles);
        $count = $count + count($articles);
        $articles = $ArticlesManager->getAllEntries();
        $this->assertEquals($count, count($articles));

        $ArticlesManager->delete("feed","http://feeds.betacie.com/viedemerde");
        $ArticlesManager->delete("feed","http://linuxfr.org/news.atom");
        $articles = $ArticlesManager->getAllEntries();
        $this->assertNotEquals(0, count($articles));

        $ArticlesManager->delete("feed","https://www.guildwars2.com/fr/feed/");
        $ArticlesManager->delete("feed","http://www.lemonde.fr/japon/rss_full.xml");
        $articles = $ArticlesManager->getAllEntries();
        $this->assertEquals(0, count($articles));



    }

    /**
     * @depends testCoExistenceAtomRSS
     */
    public function testMerge() {
        $ArticlesManager = new ArticlesManager("articles");
        $article = new Entry();
        $article->alreadyRead = 0;
        $article->id = 0;
        $article->author="test";
        $article->extra=0;
        $article->publicationDate= date("Y-m-d H:i:s");
        $article->updateDate =  date("Y-m-d H:i:s");

        $ArticlesManager->insertEntry($article);

        $article->author="test2";
        $article->extra="bonus";

        $ArticlesManager->mergeEntry($article);
        $articles = $ArticlesManager->getAllEntries();

        $this->assertEquals($articles[0]->author, "test2");
        $this->assertEquals($articles[0]->extra, "bonus");




    }


}
