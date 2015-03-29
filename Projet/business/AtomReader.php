<?php



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
		//
		// NB: simpleXML echoue dans la récupération de deux childrens d'un élément si les balises portent le même nom, simpleXML n'associe pas les bonnes valeurs, du coup
		// on ne peut récupérer qu'un des auteurs, tans pis pour les autres.

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

		$feed = simplexml_load_string($xml);
		//$feed = new SimpleXMLElement($url,NULL,true);

		return $this->update($feed,$date, $url);


	}

	//affecte la valeur au champ key de la variable article
	private  function setReadValue($reflector, $key, $article, $value)
	{

		if ( isset($this->propertyMap[$key])) {
			$prop = $this->propertyMap[$key];
			$property = $reflector->getProperty($prop);
			$property->setValue($article, $value);

		}
		return $article;

	}



	public function read()
	{
		$this->read_url("http://feeds.betacie.com/viedemerde");
	}


	
	public function update($feed, $date, $url)
	{
		$reflector = new ReflectionClass('Entry');

		$result = array();


		// articles
		foreach($feed->entry as $Entry) {

			// afficher que les dates utiles
			if ($date != null && strtotime($Entry->updated) < $date)
				break;

			//on génère un nouvel articles
			$article = new Entry();
			$article->alreadyRead = 0;
			$article->feed = $url;

			// cas généraux -> on a l'info directement sans sous balises
			foreach($Entry as $key => $value) {


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



		return $result;

	}
	
	
	
}