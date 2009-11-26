<?php

class Tuitter_User extends Tuitter_XmlResult
{
	private $_user;
	private $_tweet;

	public function getStatus()
	{
		return $this->_tuitter->getUserStatus($this->id);
	}

	public function follow()
	{
		return $this->_tuitter->follow($this->id);
	}

	public function unfollow()
	{
		return $this->_tuitter->unfollow($this->id);
	}

	public function sendDM($text)
	{
		return $this->_tuitter->sendDM($this->id, $text);
	}

	public function isFollowing()
	{
		return $this->_tuitter->existsFriendship($this->id);
	}

	protected function _startElement($parser, $tag, $attr)
	{
		$tag = strtolower($tag);
		if($tag=='status'){
			$this->_tweet = new Tuitter_Tweet($this->_tuitter);
		}
		$this->_cdata = '';
	}

	protected function _endElement($parser, $tag)
	{
		$tag = strtolower($tag);
		switch($tag){
			case 'user':
				break;
			case 'status':
				$this->_user['status'] = $this->_tweet;
				$this->_tweet = null;
				break;
			default:
				if($this->_tweet){
					$this->_tweet->$tag = $this->_cdata;
				}else{
					$this->_user[$tag] = $this->_cdata;
				}
		}
	}

	protected function _cData($parser, $data)
	{
		$this->_cdata .= $data;
	}

	public function __set($key, $val)
	{
		$this->_user[$key] = $val;
	}

	public function __get($key)
	{
		if(isset($this->_user[$key])){
			return $this->_user[$key];
		}
	}
}
