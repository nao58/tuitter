<?php

class Tuitter_IDs extends Tuitter_XmlResult implements Iterator
{
	private $_ids = array();
	private $_cdata;

	protected function _startElement($parser, $tag, $attr)
	{
		$this->_cdata = '';
	}

	protected function _endElement($parser, $tag)
	{
		$tag = strtolower($tag);
		if($tag=='id'){
			$this->_ids[] = new Tuitter_ID($this->_tuitter, $this->_cdata);
		}
	}

	protected function _cData($parser, $data)
	{
		$this->_cdata .= $data;
	}

	public function rewind()
	{
		reset($this->_ids);
	}

	public function current()
	{
		return current($this->_ids);
	}

	public function key()
	{
		return key($this->_ids);
	}

	public function next()
	{
		return next($this->_ids);
	}

	public function valid()
	{
		return ($this->current() !== false);
	}
}
