<?php

class Tuitter_Http_Request
{
	private $_host;
	private $_port;
	private $_multipart = false;
	private $_url;
	private $_user;
	private $_pass;
	private $_prms = array();
	private $_headers = array();

	public function __construct($url, $host, $port=80)
	{
		$this->_url = $url;
		$this->_host = $host;
		$this->_port = $port;
	}

	public function setBasicAuth($user, $pass)
	{
		$this->_user = $user;
		$this->_pass = $pass;
	}

	public function setPrms(array $prms)
	{
		$this->_prms = $prms;
	}

	public function setHeaders(array $headers)
	{
		$this->_headers = $headers;
	}

	public function setMultipart($v)
	{
		$this->_multipart = $v;
	}

	public function getUser()
	{
		return $this->_user;
	}

	public function getHost()
	{
		return $this->_host;
	}

	public function getPort()
	{
		return $this->_port;
	}

	public function getUrl()
	{
		return $this->_url;
	}

	public function getPrms()
	{
		return $this->_prms;
	}

	public function getHeaders()
	{
		$h = $this->_headers;
		if($this->_user and $this->_pass){
			$hash = base64_encode($this->_user.":".$this->_pass);
			$h['Authorization'] = "Basic {$hash}";
		}
		return $h;
	}

	public function isMultipart()
	{
		return $this->_multipart;
	}
}
