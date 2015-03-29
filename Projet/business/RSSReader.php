<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 22/03/2015
 * Time: 12:16
 */



class RSSReader implements  IReader{


    public $propertyMap;

    function  RSSReader()
    {
        date_default_timezone_set("Zulu");

        $this->propertyMap = array(
            'guid' => 'id',
            "title" => "title",
            "link" => "link",
            "pubDate" => "publicationDate",
            "updated" => "updateDate",
            "description" => "content",
            "comments" => "comment",
            "enclosure[url]" => "extra"
        );
    }

    public function read()
    {
        $date = strtotime("16 mars");
        $url = "https://www.guildwars2.com/fr/feed/";

        $url = "http://www.lemonde.fr/japon/rss_full.xml";

        return $this->read_url_until_date($url, $date );
    }

    public function read_url_until_date($url, $date)
    {
        $ch = curl_init();

        // Configuration de l'URL et d'autres options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // accéder aux sites https, ça enlève de la sécurité, mais au moins on a les infos
        // la solution propre nécéssite d'utiliser le certificat de son navigateur et comporte une partie à faire
        // à la main.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


        // Récupération de l'URL et affichage sur le naviguateur
        $xml =  curl_exec($ch);

        // Fermeture de la session cURL
        curl_close($ch);


        $feed = simplexml_load_string($xml);


        return $this->update($feed,$date,$url);

    }

    //affecte la valeur au champ key de la variable article
    private  function setReadValue($reflector, $key, $article, $value)
    {
        if ( isset($this->propertyMap[$key])) {
            $property = $reflector->getProperty($this->propertyMap[$key]);
            $property->setValue($article, $value);

        }
        return $article;

    }

    public function update($feed,$date, $url)
    {
        $reflector = new ReflectionClass('Entry');

        $result = array();


        // articles
        foreach($feed->channel as $Channel) {




            foreach($Channel->item as $item)
            {

                // afficher que les dates utiles
                if ($date != null && strtotime($item->pubDate) < $date)
                    break;

                //on génère un nouvel article
                $article = new Entry();
                $article->alreadyRead = 0;
                $article->feed = $url;

                foreach($item->children() as $key => $value) {



                    $this->setReadValue($reflector,$key,$article,$value);

                    foreach($item->{$key}->children() as $sub_key => $sub_value) {
                        $this->setReadValue($reflector, $key . "/" . $sub_key, $article, $sub_value);
                    }

                    foreach($item->{$key}->attributes() as $sub_key => $sub_value) {
                        $this->setReadValue($reflector, $key . "[" . $sub_key . "]", $article, $sub_value);
                    }
                }

                // on sauvegarde l'article
                $result[] = $article;
            }





        }

        return $result;
    }

}