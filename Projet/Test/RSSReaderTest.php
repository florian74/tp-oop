<?php
include __DIR__ . '/../business/IReader.php';
include __DIR__ . '/../business/RSSReader.php';
include __DIR__ . '/../model/Feed.php';
include __DIR__ . '/../model/Entry.php';
include __DIR__ . '/../DAL/connection.php';
include __DIR__ . '/../DAL/ArticlesManager.php';


class RSSReaderTest extends PHPUnit_Framework_TestCase {


    public function testXMLRead()
    {
        $xmlstr = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Japon : Toute l'actualité sur Le Monde.fr.</title>
    <description>Japon - Découvrez gratuitement tous les articles, les vidéos et les infographies de la rubrique Japon sur Le Monde.fr.</description>
    <copyright>Copyright Le Monde.fr</copyright>
    <link>http://www.lemonde.fr/japon/rss_full.xml</link>
    <atom:link href="http://www.lemonde.fr/japon/rss_full.xml" rel="self" type="application/rss+xml"/>
    <pubDate>Sun, 22 Mar 2015 16:18:42 +0100</pubDate>
    <image>
      <url>http://www.lemonde.fr/medias/web/img/export/logo_lmfr_90x20_export.png</url>
      <title>Japon : Toute l'actualité sur Le Monde.fr.</title>
      <link>http://www.lemonde.fr/japon/rss_full.xml</link>
    </image>
    <item>
      <link>http://www.lemonde.fr/economie/article/2015/03/18/toyota-va-augmenter-les-salaires-de-3-2_4595629_3234.html</link>
      <title>Les grands groupes japonais augmentent les salaires</title>
      <description>Toyota va par exemple procéder à la plus forte augmentation depuis 2002. Ces hausses surviennent alors que l'économie japonaise donne toujours des signes de faiblesse et peine à sortir de la déflation.</description>
      <pubDate>Wed, 18 Mar 2015 07:05:55 GMT</pubDate>
      <guid isPermaLink="true">http://www.lemonde.fr/tiny/4595629/</guid>
      <enclosure url="http://s1.lemde.fr/image/2015/03/18/534x267/4595628_3_868a_en-augmentant-ainsi-les-salaires-le-numero-un_b9bf6371016c93330e9c90771eb748ea.jpg" type="image/jpeg" length="24919"/>
    </item>
    <item>
      <link>http://www.lemonde.fr/economie/article/2015/03/17/le-japon-reconnait-ne-pas-etre-vraiment-sorti-de-la-deflation_4595340_3234.html</link>
      <title>Le Japon reconnaît ne pas être vraiment sorti de la déflation</title>
      <description>La Banque du Japon (BoJ) a reconnu, mardi 17 mars, n’avoir pas réussi à redresser la courbe d’évolution des prix. Elle n’envisage toutefois pas d’accroître son programme de soutien à l’économie et table sur une hausse des salaires dans les entreprises.</description>
      <pubDate>Tue, 17 Mar 2015 15:12:40 GMT</pubDate>
      <guid isPermaLink="true">http://www.lemonde.fr/tiny/4595340/</guid>
      <enclosure url="http://s2.lemde.fr/image/2015/03/17/534x267/4595338_3_2006_malheureusement-nous-n-avons-pas-encore-pu_29f4f6144d42fdc4946e9fdb472e52fe.jpg" type="image/jpeg" length="18623"/>
    </item>
     <item>
      <link>http://www.lemonde.fr/planete/article/2015/03/17/tokyo-s-engage-a-fermer-des-reacteurs-nucleaires_4595109_3244.html</link>
      <title>Tokyo s'engage à fermer des réacteurs nucléaires</title>
      <description>Trois réacteurs nucléaires japonais vont être désactivés en raison de leur vétusté et du coût trop élevé de leur mise en conformité avec les nouvelles normes de sécurité fixées après le désastre de Fukushima.</description>
      <pubDate>Tue, 17 Mar 2015 13:08:16 GMT</pubDate>
      <guid isPermaLink="true">http://www.lemonde.fr/tiny/4595109/</guid>
      <enclosure url="http://s1.lemde.fr/image/2013/08/21/534x267/3464297_3_7eb1_la-centrale-de-fukushima-le-11-mars-2013_9b94a925ec85c9886ba3aad55458c2cd.jpg" type="image/jpeg" length="51734"/>
    </item>
 </channel>
</rss>
XML;

        $feed = simplexml_load_string($xmlstr);

        $RSSReader = new RSSReader();

        $articles = $RSSReader->update($feed, strtotime("03/15/2000"),"uselessForTheTest");


        $this->assertEquals("Wed, 18 Mar 2015 07:05:55 GMT",$articles[0]->publicationDate);
        $this->assertEquals("Les grands groupes japonais augmentent les salaires",$articles[0]->title);
        $this->assertEquals("Toyota va par exemple procéder à la plus forte augmentation depuis 2002. Ces hausses surviennent alors que l'économie japonaise donne toujours des signes de faiblesse et peine à sortir de la déflation.",$articles[0]->content);
        $this->assertEquals("http://s1.lemde.fr/image/2015/03/18/534x267/4595628_3_868a_en-augmentant-ainsi-les-salaires-le-numero-un_b9bf6371016c93330e9c90771eb748ea.jpg",$articles[0]->extra);

        $this->assertEquals("Tue, 17 Mar 2015 15:12:40 GMT",$articles[1]->publicationDate);
        $this->assertEquals("Le Japon reconnaît ne pas être vraiment sorti de la déflation",$articles[1]->title);
        $this->assertEquals("La Banque du Japon (BoJ) a reconnu, mardi 17 mars, n’avoir pas réussi à redresser la courbe d’évolution des prix. Elle n’envisage toutefois pas d’accroître son programme de soutien à l’économie et table sur une hausse des salaires dans les entreprises.",$articles[1]->content);
        $this->assertEquals("http://s2.lemde.fr/image/2015/03/17/534x267/4595338_3_2006_malheureusement-nous-n-avons-pas-encore-pu_29f4f6144d42fdc4946e9fdb472e52fe.jpg",$articles[1]->extra);

        $this->assertEquals("Tue, 17 Mar 2015 13:08:16 GMT",$articles[2]->publicationDate);
        $this->assertEquals("Tokyo s'engage à fermer des réacteurs nucléaires",$articles[2]->title);
        $this->assertEquals("Trois réacteurs nucléaires japonais vont être désactivés en raison de leur vétusté et du coût trop élevé de leur mise en conformité avec les nouvelles normes de sécurité fixées après le désastre de Fukushima.",$articles[2]->content);
        $this->assertEquals("http://s1.lemde.fr/image/2013/08/21/534x267/3464297_3_7eb1_la-centrale-de-fukushima-le-11-mars-2013_9b94a925ec85c9886ba3aad55458c2cd.jpg",$articles[2]->extra);

        //vérification suivant la date demandé :
        $articles = $RSSReader->update($feed, strtotime("03/17/2015 14:00"),"uselessForTheTest");
        $this->assertEquals(2, count($articles));

        $articles = $RSSReader->update($feed, strtotime("03/17/2015 16:00"),"uselessForTheTest");
        $this->assertEquals(1, count($articles));

    }
    public function testProtocolSupport() {

        $RSSReader = new RSSReader();

        //http :
        $articles = $RSSReader->read_url_until_date("http://www.lemonde.fr/japon/rss_full.xml",strtotime("15 mars 2015"));
        $this->assertNotEquals(0, count($articles));

        //https :
        $articles = $RSSReader->read_url_until_date("https://www.guildwars2.com/fr/feed/",strtotime("15 mars 2015"));
        $this->assertNotEquals(0, count($articles));

    }

}
