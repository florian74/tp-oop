<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 25/03/2015
 * Time: 15:05
 */

//détermine quel reader doit être utilisé pour un flux
class ReaderManager {

    private static $_instance = null;

    private $RSSReader;
    private $AtomReader;

    private function ReaderManager()
    {
        $this->RSSReader = new RSSReader();
        $this->AtomReader = new AtomReader();
    }

    public function getReader($url)
    {
        $ch = curl_init();

        // Configuration de l'URL et d'autres options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // accéder aux sites https, ça enlève de la sécurité, mais au moins on a les infos
        // la solution propre nécéssite d'utiliser le certificat de son navigateur et comporte une partie à faire
        // à la main.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $xml =  curl_exec($ch);

        $feed = simplexml_load_string($xml);

        $res = $this->AtomReader;

        if ($feed->getName() === "rss")
            $res = $this->RSSReader;


        return $res;

    }

    public static function getInstance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new ReaderManager();
        }

        return self::$_instance;
    }


}