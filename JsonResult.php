<?php

abstract class Tuitter_JsonResult
{
	protected $_tuitter;

	public function __construct(&$tuitter, $json=null)
	{
		$this->_tuitter = &$tuitter;
		if($json){
			$data = json_decode($json);
			$this->_getData($data);
		}
	}

	abstract protected function _getData($data);
}
