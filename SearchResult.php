<?php

class Tuitter_SearchResult
{
	private $_tuitter;
	private $_data;

	public function __construct(&$tuitter, $data)
	{
		$this->_tuitter = $tuitter;
		$this->_data = $data;
	}

	public function __get($key)
	{
		if(isset($this->_data->$key)){
			return $this->_data->$key;
		}
	}
}
