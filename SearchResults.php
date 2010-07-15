<?php

class Tuitter_SearchResults extends Tuitter_JsonResult implements Iterator
{
	protected $_res = array();

	public function getMaxId()
	{
		if($this->_res){
			return $this->_res[0]->id;
		}
	}

	protected function _getData($data)
	{
		foreach($data->results as $d){
			$this->_res[] = new Tuitter_SearchResult($this->_tuitter, $d);
		}
	}

	public function rewind()
	{
		reset($this->_res);
	}

	public function current()
	{
		return current($this->_res);
	}

	public function key()
	{
		return key($this->_res);
	}

	public function next()
	{
		return next($this->_res);
	}

	public function valid()
	{
		return ($this->current() !== false);
	}
}
