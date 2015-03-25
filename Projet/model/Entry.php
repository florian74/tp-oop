<?php

class Entry 
{
	public $title;
	public $id;
	public $link;
	public $publicationDate;
	public $updateDate;
	public $author;
	public $content;
	public $comment;
	public $extra;
	public $alreadyRead = 0;
	public $feed;

	public function Entry()
	{
		$this->publicationDate= date("Y-m-d H:i:s");
		$this->updateDate= date("Y-m-d H:i:s");
	}
	//retourne les dates de manière à ce qu'elles soient dans un format exploitable par la BDD
	public function __get($property)
	{
		$value = $this->{$property};
		if('publicationDate' === $property ) {
			$value =  date("Y-m-d H:i:s",strtotime($this->{$property}));
		}
		if('updateDate' === $property ) {
			$value =  date("Y-m-d H:i:s",strtotime($this->{$property}));
		}

		return $value;
	}
	
}