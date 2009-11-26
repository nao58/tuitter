<?php

class Tuitter_ID
{
	private $_tuitter;
	private $_id;

	public function __construct(&$tuitter, $id)
	{
		$this->_tuitter = $tuitter;
		$this->_id = $id;
	}

	public function getUserStatus()
	{
		return $this->_tuitter->getUserStatus($this->_id);
	}
}
