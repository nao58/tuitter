<?php

class Tuitter_Account extends Tuitter_XmlResult
{
	private $_account;

	public function updateProf(array $prof)
	{
		$new = $this->_tuitter->updateAccountProf($prof);
		$this->_account = $new->_account;
	}

	public function updateProfColors(array $colors)
	{
		$new = $this->_tuitter->updateAccountColors($colors);
		$this->_account = $new->_account;
	}

	public function updateProfImage($img, $file, $mime)
	{
		$new = $this->_tuitter->updateAccountImage($img, $file, $mime);
		$this->_account = $new->_account;
	}

	public function updateProfBkImage($img, $file, $mime, $tile=false)
	{
		$new = $this->_tuitter->updateAccountBkImage($img, $file, $mime, $tile);
		$this->_account = $new->_account;
	}

	protected function _startElement($parser, $tag, $attr)
	{
		$this->_cdata = '';
	}

	protected function _endElement($parser, $tag)
	{
		$tag = strtolower($tag);
		switch($tag){
			case 'user':
				break;
			default:
				$this->_account[$tag] = $this->_cdata;
		}
	}

	protected function _cData($parser, $data)
	{
		$this->_cdata .= $data;
	}

	public function __set($key, $val)
	{
		$this->_account[$key] = $val;
	}

	public function __get($key)
	{
		if(isset($this->_account[$key])){
			return $this->_account[$key];
		}
	}
}
