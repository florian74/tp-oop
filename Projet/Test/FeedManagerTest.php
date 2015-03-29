<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 25/03/2015
 * Time: 02:07
 */
include __DIR__ . '/../business/IReader.php';
include __DIR__ . '/../business/AtomReader.php';
include __DIR__ . '/../business/RSSReader.php';
include __DIR__ . '/../model/Feed.php';
include __DIR__ . '/../model/Entry.php';
include __DIR__ . '/../DAL/Connection.php';
include __DIR__ . '/../DAL/ArticlesManager.php';

class FeedManagerTest extends PHPUnit_Framework_TestCase {
    public function testOneAndMultiInsert()
    {

        $ArticlesManager = new ArticlesManager("feeds");
        $ArticlesManager->clean();


        $feeds = $ArticlesManager->getAllEntries();
        $count = count($feeds);

        $feed = new Feed();
        $feed->url = 0;
        $feed->description="atom";
        $feed->updateDate= date("Y-m-d H:i:s");




        $ArticlesManager->insertEntry($feed);

        $feeds = $ArticlesManager->getAllEntries();
        $this->AssertEquals($count + 1 ,count($feeds) );

        //should fail -> two times inserting the same article
        try {
            $ArticlesManager->insertEntry($feed);
        }
        catch (Exception $e)
        {

        }

        $feeds = $ArticlesManager->getAllEntries();
        $this->AssertEquals($count + 1 ,count($feeds) );

        $feeds = array();

        $add = 5;
        for ($i=0; $i < $add ; $i++) {
            $feed = new Feed();
            $feed->description = 0;
            $feed->url = $i+1;
            $feed->updateDate = date("Y-m-d H:i:s");

            $feeds[] = $feed;
        }

        $ArticlesManager->insertEntries($feeds);

        $feeds =  $ArticlesManager->getAllEntries();
        $this->AssertEquals($count + 1 + $add ,count($feeds) );




    }



    /**
     * @depends testOneAndMultiInsert
     */
    public function testDelete()
    {

        $ArticlesManager = new ArticlesManager("feeds");

        $feeds = $ArticlesManager->getAllEntries();
        $count = count($feeds);

        $feed = new Feed();
        $feed->url = 0;
        $feed->description="atom";
        $feed->updateDate= date("Y-m-d H:i:s");

        $ArticlesManager->Delete("url", $feed->url);
        $feeds = $ArticlesManager->getAllEntries();
        $this->assertEquals($count-1, count($feeds));


        $feeds = array();

        $add = 5;
        for ($i=0; $i < $add ; $i++) {
            $feed = new Feed();
            $feed->description = 0;
            $feed->url = $i+1;
            $feed->updateDate = date("Y-m-d H:i:s");

            $feeds[] = $feed;
        }

        $ArticlesManager->deleteEntries( $feeds);
        $feeds = $ArticlesManager->getAllEntries();
        $this->assertEquals($count-1-$add, count($feeds));



        $feeds = $ArticlesManager->getAllEntries();
        $this->assertEquals(0, count($feeds));

    }

    public function testMerge() {
        $ArticlesManager = new ArticlesManager("feeds");

        $feed = new Feed();
        $feed->url = 0;
        $feed->description="atom";
        $feed->updateDate= date("Y-m-d H:i:s");


        $ArticlesManager->insertEntry($feed);

        $feeds = $ArticlesManager->getAllEntries();

        $this->assertEquals($feeds[0]->url, 0);
        $this->assertEquals($feeds[0]->description, "atom");

        $feed->description="oop";


        $ArticlesManager->mergeEntry($feed);
        $feeds = $ArticlesManager->getAllEntries();


        $this->assertEquals($feeds[0]->description,"oop");


        $feed->url="coucou";

        $ArticlesManager->mergeWithPKChange($feed,0);
        $feeds = $ArticlesManager->getAllEntries();
        $this->assertEquals($feeds[0]->url,"coucou");




    }


}
