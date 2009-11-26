<?php

class Tuitter_Tweet extends Tuitter_XmlResult
{
	private $_tweet;
	private $_user;
	private $_cdata;

	public function reply($text)
	{
		$text = '@'.$this->user->screen_name.' '.$text;
		return $this->_tuitter->sendMessage($text, array('in_reply_to_status_id' => $this->id));
	}

	public function delete()
	{
		return $this->_tuitter->deleteMessage($this->id);
	}

	protected function _startElement($parser, $tag, $attr)
	{
		$tag = strtolower($tag);
		if($tag=='user'){
			$this->_user = new Tuitter_User($this->_tuitter);
		}
		$this->_cdata = '';
	}

	protected function _endElement($parser, $tag)
	{
		$tag = strtolower($tag);
		switch($tag){
			case 'status':
				break;
			case 'user':
				$this->_tweet['user'] = $this->_user;
				$this->_user = null;
				break;
			default:
				if($this->_user){
					$this->_user->$tag = $this->_cdata;
				}else{
					$this->_tweet[$tag] = $this->_cdata;
				}
		}
	}

	protected function _cData($parser, $data)
	{
		$this->_cdata .= $data;
	}

	public function __set($key, $val)
	{
		$this->_tweet[$key] = $val;
	}

	public function __get($key)
	{
		if(isset($this->_tweet[$key])){
			return $this->_tweet[$key];
		}
	}
}
