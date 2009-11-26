<?php

class Tuitter_Tweets extends Tuitter_XmlResult implements Iterator
{
	private $_tweets = array();
	private $_tweet;
	private $_user;
	private $_cdata;

	public function getMaxId()
	{
		if($this->_tweets){
			return $this->_tweets[0]->id;
		}
	}

	public function reverse()
	{
		$this->_tweets = array_reverse($this->_tweets);
	}

	protected function _startElement($parser, $tag, $attr)
	{
		$tag = strtolower($tag);
		if($tag=='status'){
			$this->_tweet = new Tuitter_Tweet($this->_tuitter);
		}else if($tag=='user'){
			$this->_user = new Tuitter_User($this->_tuitter);
		}
		$this->_cdata = '';
	}

	protected function _endElement($parser, $tag)
	{
		$tag = strtolower($tag);
		switch($tag){
			case 'statuses':
				break;
			case 'status':
				$this->_tweets[] = $this->_tweet;
				break;
			case 'user':
				$this->_tweet->user = $this->_user;
				$this->_user = null;
				break;
			default:
				if($this->_user){
					$this->_user->$tag = $this->_cdata;
				}else if($this->_tweet){
					$this->_tweet->$tag = $this->_cdata;
				}
		}
	}

	protected function _cData($parser, $data)
	{
		$this->_cdata .= $data;
	}

	public function rewind()
	{
		reset($this->_tweets);
	}

	public function current()
	{
		return current($this->_tweets);
	}

	public function key()
	{
		return key($this->_tweets);
	}

	public function next()
	{
		return next($this->_tweets);
	}

	public function valid()
	{
		return ($this->current() !== false);
	}
}
