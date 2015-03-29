<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 22/03/2015
 * Time: 21:58
 */
include __DIR__ . '/../business/IReader.php';
include __DIR__ . '/../business/AtomReader.php';
include __DIR__ . '/../model/Feed.php';
include __DIR__ . '/../model/Entry.php';
include __DIR__ . '/../DAL/Connection.php';
include __DIR__ . '/../DAL/ArticlesManager.php';

class AtomReaderTest extends PHPUnit_Framework_TestCase
{


    public function testXMLRead()
    {

        // test d'un feed inspiré de vdm

        $xmlstr = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" media="screen" href="/~files/atom-premium.xsl"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:feedpress="https://feed.press/xmlns">
<feedpress:locale>fr</feedpress:locale>
<title>Vie de merde</title>
<subtitle>Ma vie c'est de la merde, et je vous emmerde</subtitle>
<id>http://www.viedemerde.fr/</id>
<author>
    <name>Vie de merde</name>
    </author>
  <link rel="alternate" type="text/html" href="http://www.viedemerde.fr"/>
  <link rel="self" href="http://feeds.betacie.com/viedemerde"/>
  <link rel="hub" href="http://feedpress.superfeedr.com/"/>
  <updated>2015-03-22T22:05:20+01:00</updated>
  <entry xmlns="http://www.w3.org/2005/Atom">
    <title type="html"><![CDATA[remueménage a une VDM]]></title>
    <id>http://www.viedemerde.fr/inclassable/8517046</id>
    <link rel="alternate" href="http://www.viedemerde.fr/inclassable/8517046"/>
    <published>2015-03-22T15:43:54+01:00</published>
    <updated>2015-03-22T15:43:54+01:00</updated>
    <author>
      <name><![CDATA[remueménage]]></name>
    </author>
    <content type="html"><![CDATA[<p>Aujourd'hui, ménage de printemps chez ma sœur. Je n'ai pas osé lui demander comment elle a pu coincer une de ses culottes dans sa hotte de cuisine. VDM</p>]]></content>
  </entry>
  <entry xmlns="http://www.w3.org/2005/Atom">
    <title type="html"><![CDATA[cocoo2 a une VDM]]></title>
    <id>http://www.viedemerde.fr/enfants/8516801</id>
    <link rel="alternate" href="http://www.viedemerde.fr/enfants/8516801"/>
    <published>2015-03-22T09:30:08+01:00</published>
    <updated>2015-03-22T09:30:08+01:00</updated>
    <author>
      <name><![CDATA[cocoo2]]></name>
    </author>
    <content type="html"><![CDATA[<p>Aujourd'hui, ma fille m'a demandé d'arrêter d'être si incisif et méchant dans les SMS que je lui envoie. Je mets des points à la fin de mes phrases, ça la "perturbe grave". VDM</p>]]></content>
  </entry>
  <entry xmlns="http://www.w3.org/2005/Atom">
    <title type="html"><![CDATA[MaraCous a une VDM]]></title>
    <id>http://www.viedemerde.fr/inclassable/8516652</id>
    <link rel="alternate" href="http://www.viedemerde.fr/inclassable/8516652"/>
    <published>2015-03-21T23:17:02+01:00</published>
    <updated>2015-03-21T23:17:02+01:00</updated>
    <author>
      <name><![CDATA[MaraCous]]></name>
    </author>
    <content type="html"><![CDATA[<p>Aujourd'hui, je croise une touriste dans la rue qui me demande si je parle anglais. Toute contente à l'idée de pratiquer un peu la langue de Shakespeare, je lui réponds oui. Elle voulait juste savoir si on voyait du sang au niveau de ses fesses, car ses règles sont "very strong" en ce moment. VDM</p>]]></content>
  </entry>
  </feed>
XML;

     $feed = simplexml_load_string($xmlstr);

     $AtomReader = new AtomReader();

     $articles = $AtomReader->update($feed, strtotime("03/15/2000"), "uselessForTheTest");

     // nombre d'articles lu
     $this->assertEquals(3, count($articles));

     // vérification de données
     $this->assertEquals("remueménage",$articles[0]->author);
     $this->assertEquals("2015-03-22T15:43:54+01:00",$articles[0]->publicationDate);
     $this->assertEquals("2015-03-22T15:43:54+01:00",$articles[0]->updateDate);
     $this->assertEquals("<p>Aujourd'hui, ménage de printemps chez ma sœur. Je n'ai pas osé lui demander comment elle a pu coincer une de ses culottes dans sa hotte de cuisine. VDM</p>",$articles[0]->content);

     $this->assertEquals("cocoo2",$articles[1]->author);
     $this->assertEquals("2015-03-22T09:30:08+01:00",$articles[1]->publicationDate);
     $this->assertEquals("2015-03-22T09:30:08+01:00",$articles[1]->updateDate);
     $this->assertEquals("<p>Aujourd'hui, ma fille m'a demandé d'arrêter d'être si incisif et méchant dans les SMS que je lui envoie. Je mets des points à la fin de mes phrases, ça la \"perturbe grave\". VDM</p>",$articles[1]->content);

     $this->assertEquals("MaraCous",$articles[2]->author);
     $this->assertEquals("2015-03-21T23:17:02+01:00",$articles[2]->publicationDate);
     $this->assertEquals("2015-03-21T23:17:02+01:00",$articles[2]->updateDate);
     $this->assertEquals("<p>Aujourd'hui, je croise une touriste dans la rue qui me demande si je parle anglais. Toute contente à l'idée de pratiquer un peu la langue de Shakespeare, je lui réponds oui. Elle voulait juste savoir si on voyait du sang au niveau de ses fesses, car ses règles sont \"very strong\" en ce moment. VDM</p>",$articles[2]->content);

     //vérification suivant la date demandé :
     $articles = $AtomReader->update($feed, strtotime("03/22/2015 00:00"), "uselessForTheTest");
     $this->assertEquals(2, count($articles));

     $articles = $AtomReader->update($feed, strtotime("03/22/2015 12:00"), "uselessForTheTest");
     $this->assertEquals(1, count($articles));
    }

    public function testProtocolSupport() {

        $AtomReader = new AtomReader();

        //http :
        $articles = $AtomReader->read_url_until_date("http://feeds.betacie.com/viedemerde",strtotime("20 mars 2015"));
        $this->assertNotEquals(0, count($articles));

        //https :
        $articles = $AtomReader->read_url_until_date("https://linuxfr.org/news.atom",strtotime("20 mars 2015"));
        $this->assertNotEquals(0, count($articles));


    }


}
?>