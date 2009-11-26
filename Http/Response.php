<?php

class Tuitter_Http_Response
{
	private $_status;
	private $_response;
	private $_statusDesc;
	private $_headers;
	private $_body;

	public function __construct($r)
	{
		$this->_response = $r;

		$lines = explode("\r\n", $r);

		$sts = array_shift($lines);
		if(preg_match('/^HTTP\/[0-9]\.[0-9]\s([0-9]+)\s?(.*)$/', $sts, $m)){
			$this->_status = $m[1];
			$this->_statusDesc = $m[2];
		}
		
		$headers = array();
		$line = array_shift($lines);
		while($line){
			if(preg_match('/^(.*?): (.*)$/', $line, $m))
				$headers[$m[1]] = $m[2];
			$line = array_shift($lines);
		}
		$this->_headers = $headers;

		$this->_body = implode("\r\n", $lines);
	}

	public function getResponse()
	{
		return $this->_response;
	}

	public function getStatus()
	{
		return $this->_status;
	}

	public function getHeader($key)
	{
		if(isset($this->_headers[$key])){
			return $this->_headers[$key];
		}
	}

	public function getBody()
	{
		return $this->_body;
	}
}
