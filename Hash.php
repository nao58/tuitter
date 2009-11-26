<?php

class Tuitter_Hash extends Tuitter_XmlResult
{
	private $_hash;
	private $_cdata;

	protected function _startElement($parser, $tag, $attr)
	{}

	protected function _endElement($parser, $tag)
	{
		$tag = strtolower($tag);
		if($tag != 'hash'){
			$this->_hash[$tag] = $this->_cdata;
		}
		$this->_cdata = null;
	}

	protected function _cData($parser, $data)
	{
		$this->_cdata .= $data;
	}

	public function __get($key)
	{
		if(isset($this->_hash[$key])){
			return $this->_hash[$key];
		}
	}

	public function __put($key, $val){
		$this->_hash[$key] = $val;
	}
}
