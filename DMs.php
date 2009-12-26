<?php

class Tuitter_DMs extends Tuitter_XmlResult implements Iterator
{
	private $_dms = array();
	private $_dm;
	private $_sender;
	private $_recipient;
	private $_cdata;

	public function getMaxId()
	{
		if($this->_dms){
			return $this->_dms[0]->id;
		}
	}

	public function reverse()
	{
		$this->_dms = array_reverse($this->_dms);
	}

	protected function _startElement($parser, $tag, $attr)
	{
		$tag = strtolower($tag);
		if($tag=='direct_message'){
			$this->_dm = new Tuitter_DM($this->_tuitter);
		}else if($tag=='sender'){
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
			case 'direct-messages':
				break;
			case 'direct_message':
				$this->_dms[] = $this->_dm;
				break;
			case 'sender':
				$this->_dm->sender = $this->_sender;
				$this->_sender = null;
				break;
			case 'recipient':
				$this->_dm->recipient = $this->_recipient;
				$this->_recipient = null;
				break;
			default:
				if($this->_sender){
					$this->_sender->$tag = $this->_cdata;
				}else if($this->_recipient){
					$this->_recipient->$tag = $this->_cdata;
				}else if($this->_dm){
					$this->_dm->$tag = $this->_cdata;
				}
		}
	}

	protected function _cData($parser, $data)
	{
		$this->_cdata .= $data;
	}

	public function rewind()
	{
		reset($this->_dms);
	}

	public function current()
	{
		return current($this->_dms);
	}

	public function key()
	{
		return key($this->_dms);
	}

	public function next()
	{
		return next($this->_dms);
	}

	public function valid()
	{
		return ($this->current() !== false);
	}
}
