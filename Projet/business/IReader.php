<?php

interface IReader
{
	public function read();

	// paramètre :
	// - url du feed
	// - date de dernière mise à jour / null si on veux tout depuis le début
	public function read_url_until_date( $url , $date);
	
	
}