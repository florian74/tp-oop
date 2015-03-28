<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 25/03/2015
 * Time: 16:43
 */

class FeedUpdater {

    private static $_instance = null;

    private $feedsDB;
    private $articlesDB;

    private function FeedUpdater()
    {
        date_default_timezone_set("Zulu");
        $this->feedsDB = new ArticlesManager("feeds");
        $this->articlesDB = new ArticlesManager("articles");
    }

    public static function getInstance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new FeedUpdater();
        }

        return self::$_instance;
    }


    public function updateAllFeed()
    {

        $feeds = $this->feedsDB->getAllEntries();

        foreach($feeds as $feed)
        {
            //recupération du reader
            $reader = ReaderManager::getInstance()->getReader($feed->url);

            //updates
            $articles = $reader->read_url_until_date($feed->url, $feed->updateDate);
            $feed->updateDate = date("Y-m-d H:i:s");

            //sauvegardes
            $this->articlesDB->insertEntries($articles);
            $this->feedsDB->mergeEntry($feed);

        }

    }

    public function getAllArticle()
    {
        return $this->articlesDB->getAllEntries();
    }

    public function getSpecificEntries($param, $value)
    {
        return $this->articlesDB->getEntriesByProperty($param,$value);
    }

    public function getEntryFromNumber($value)
    {
        $db = new ArticlesManager("feeds");
        var_dump($db->propertyMap);
        $feed = $db->getEntriesByProperty("number", $value);

        return $this->articlesDB->getEntriesByProperty("feed",$feed[0]->url);
    }

    public function deleteFeed( $feed)
    {
        $this->articlesDB->Delete("feed" , $feed->url);
        $this->feedsDB->DeleteEntry($feed);
    }

    public function addFeed( $url , $description )
    {

        $feed = new Feed();
        $feed->url = $url;
        $feed->description = $description;
        $feed->updateDate = date("Y-m-d H:i:s",strtotime("-2 months"));

        $this->feedsDB->insertEntry($feed);

        //recupération du reader
        $reader = ReaderManager::getInstance()->getReader($feed->url);

        //updates
        $articles = $reader->read_url_until_date($feed->url, $feed->updateDate);

        $this->articlesDB->insertEntries($articles);


    }

    public function resetDB()
    {
        $this->articlesDB->clean();
        $this->feedsDB->clean();
    }

}