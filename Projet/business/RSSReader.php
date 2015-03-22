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
            "content" => "content",
            "description" => "description"
        );
    }

    public function read()
    {
        $date = strtotime("20 mars 2015");
        $url = "https://www.guildwars2.com/fr/feed/";

        //$url = "http://www.lemonde.fr/japon/rss_full.xml";







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


        // afficher le contenu de la réponse
        /*
        $lines = array();
        $lines = explode("\n", $xml);

        // display file line by line
        foreach($lines as $line_num => $line) {
            echo "Line # {$line_num} : " . htmlspecialchars($line) . "<br />\n";
        }
        */


        // Fermeture de la session cURL
        curl_close($ch);


        $feed = simplexml_load_string($xml);
       // $feed = new SimpleXMLElement($url,NULL,true);

        $reflector = new ReflectionClass('Entry');

        $result = array();

        // articles
        foreach($feed->channel as $Channel) {


            foreach($Channel as $item)
            {

            }
            // afficher que les dates utiles


            //on génère un nouvel articles
            $article = new Entry();
            $article->alreadyRead = FALSE;

            // cas généraux -> on a l'info directement sans sous balises
           /* foreach($Entry as $key => $value) {
                echo $key . " setting ...." ;


                //test si la propriété est renseignée par l'article
                $article = $this->setReadValue($reflector,$key,$article,$value);



                foreach($Entry->{$key}->children() as $sub_key => $sub_value) {
                    $article = $this->setReadValue($reflector,$key. "/" . $sub_key,$article,$sub_value);
                }

                foreach($Entry->{$key}->attributes() as $sub_key => $sub_value) {
                    $article = $this->setReadValue($reflector,$key . "[" . $sub_key  . "]" ,$article,$sub_value);
                }


            }
*/



            // on sauvegarde l'article
            $result[] = $article;
        }


        echo "success";



        return $result;
    }
}