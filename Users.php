<?php

class Tuitter_Users extends Tuitter_XmlResult implements Iterator
{
	private $_users = array();
	private $_user;
	private $_tweet;
	private $_cdata;
	private $_next_cursor;
	private $_prev_cursor;

	public function intersect($ids)
	{
		$users = array();
		foreach($this->_users as $user){
			if(!isset($ids[$user->id])){
				$users[] = $user;
			}
		}
		$this->_users = $users;
	}

	public function reverse()
	{
		$this->_users = array_reverse($this->_users);
	}

	public function getNextCursor()
	{
		return $this->_next_cursor;
	}

	public function getPrevCursor()
	{
		return $this->_prev_cursor;
	}

	protected function _startElement($parser, $tag, $attr)
	{
		$tag = strtolower($tag);
		if($tag=='user'){
			$this->_user = new Tuitter_User($this->_tuitter);
		}else if($tag=='status'){
			$this->_tweet = new Tuitter_Tweet($this->_tuitter);
		}
		$this->_cdata = '';
	}

	protected function _endElement($parser, $tag)
	{
		$tag = strtolower($tag);
		switch($tag){
			case 'users':
				break;
			case 'user':
				$this->_users[] = $this->_user;
				break;
			case 'status':
				$this->_user->status = $this->_tweet;
				$this->_tweet = null;
				break;
			case 'next_cursor':
				$this->_next_cursor = $this->_cdata;
				break;
			case 'previous_cursor':
				$this->_prev_cursor = $this->_cdata;
				break;
			default:
				if($this->_tweet){
					$this->_tweet->$tag = $this->_cdata;
				}else{
					$this->_user->$tag = $this->_cdata;
				}
		}
	}

	protected function _cData($parser, $data)
	{
		$this->_cdata .= $data;
	}

	public function rewind()
	{
		reset($this->_users);
	}

	public function current()
	{
		return current($this->_users);
	}

	public function key()
	{
		return key($this->_users);
	}

	public function next()
	{
		return next($this->_users);
	}

	public function valid()
	{
		return ($this->current() !== false);
	}
}
