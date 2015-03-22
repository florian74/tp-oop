<?php

include __DIR__ . '/IReader.php';
include __DIR__ . '/../model/Feed.php';
include __DIR__ . '/../model/Entry.php';

class AtomReader implements IReader
{
	
	public $propertyMap;

	function  AtomReader()
	{
		date_default_timezone_set("Zulu");


		// Le reader utilise un mapping via le tableau qui suit.
		// Ce tableau précise ce que l'on veux récupérer du flux Atom.
		// Lorsque le flux xml sera lu, les données des champs de l'article Entry seront chargées par réflexivité.
		//
		// Pour un élément type <borne>texte</borne> on utilise un mapping "borne" => "attribut dans la classe Entry"
		//
		// Pour un élément type <borne attr="texte"/> on utilise un mapping "borne[attr]" => "attribut dans la classe Entry"
		//
		// Pour un élément type :
		// <borne>
		//     <sous-borne>texte</sous-borne>
		// </borne>
		// on utilise un mapping "borne/sous-borne" => "attribut dans la classe Entry"
		//
		// on ne peut pas tout récupérer car les flux atoms ne sont pas tous identiques. Toutefois, ces champs seront renseigné dans l'objet de sortie
		// si ils existent dans le flux
		//
		// ce tableau devrait être la seule chose à modifier dans cette classe si on veut ajouter des attributs à la classe Entry.

		$this->propertyMap = array('id' => 'id',
			"title" => "title",
			"link[href]" => "link",
			"published" => "publicationDate",
			"updated" => "updateDate",
			"content" => "content",
			"author/name" => "author"
		);
	}

	private  function read_url($url)
	{
		$date = strtotime("20 mars 2015");
		return $this->read_url_until_date($url, $date );
	}

	// on passe la date limite de modification - la derniere date de maj, ainsi que l'url du feed !!!
	public function read_url_until_date( $url , $date)
	{
		$feed = new SimpleXMLElement($url,NULL,true);


		$reflector = new ReflectionClass('Entry');

		$result = array();


		print "printing ... \n";

		// atribut généraux - useless
		foreach($feed as $key => $value) {

			echo("[".$key ."] ".$value . "<br />");

		}



		// articles
		foreach($feed->entry as $Entry) {

			// afficher que les dates utiles
			if ($date != null && strtotime($Entry->updated) < $date)
				break;

			//on génère un nouvel articles
			$article = new Entry();
			$article->alreadyRead = FALSE;

			// cas généraux -> on a l'info directement sans sous balises
			foreach($Entry as $key => $value) {
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

			// on sauvegarde l'article
			$result[] = $article;
		}


		echo "success";

		return $result;

	}

	//affecte la valeur au champ key de la variable article
	private  function setReadValue($reflector, $key, $article, $value)
	{
		$prop = $this->propertyMap[$key];
		if ( $prop != '') {
			$property = $reflector->getProperty($prop);
			$property->setValue($article, $value);
			echo $value . " ok" . "<br/>";
		}
		return $article;

	}


	public function read()
	{
		$this->read_url("http://feeds.betacie.com/viedemerde");
	}


	
	public function update($feed){}
	
	
	
}