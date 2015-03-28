<?php



class Feed 
{
	public $url;
	public $updateDate;
	public $description;
	public $number = 0;

	public function __get($property) {
		$value = $this->{$property};
		if('updateDate' === $property ) {
			$value =  date("Y-m-d H:i:s",strtotime($this->{$property}));
		}

		return $value;
	}
	
}