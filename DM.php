<?php

class Tuitter_DM extends Tuitter_XmlResult
{
	private $_dm;
	private $_sender;
	private $_recipient;

	protected function _startElement($parser, $tag, $attr)
	{
		$tag = strtolower($tag);
		if($tag=='sender'){
			$this->_sender = new Tuitter_User($this->_tuitter);
		}else if($tag=='recipient'){
			$this->_recipient = new Tuitter_User($this->_tuitter);
		}
		$this->_cdata = '';
	}

	protected function _endElement($parser, $tag)
	{
		$tag = strtolower($tag);
		switch($tag){
			case 'direct_message':
				break;
			case 'sender':
				$this->_dm['sender'] = $this->_sender;
				$this->_sender = null;
				break;
			case 'recipient':
				$this->_dm['recipient'] = $this->_recipient;
				$this->_recipient = null;
				break;
			default:
				if($this->_sender){
					$this->_sender->$tag = $this->_cdata;
				}else if($this->_recipient){
					$this->_recipient->$tag = $this->_cdata;
				}else{
					$this->_dm[$tag] = $this->_cdata;
				}
		}
	}

	protected function _cData($parser, $data)
	{
		$this->_cdata .= $data;
	}

	public function __set($key, $val)
	{
		$this->_dm[$key] = $val;
	}

	public function __get($key)
	{
		if(isset($this->_dm[$key])){
			return $this->_dm[$key];
		}
	}
}
