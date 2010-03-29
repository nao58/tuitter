<?php

require_once dirname(__FILE__).'/Tuitter.php';

class TuitterOAuth extends Tuitter
{
	private $_oauth;

	public function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret)
	{
		$this->_oauth = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
		$this->_oauth->setToken($accessToken, $accessTokenSecret);
	}

	protected function _request($url, $host, $opt=array(), $method='GET', $auth=true, $multipart=false)
	{
		if($method=='GET'){
			$http_method = OAUTH_HTTP_METHOD_GET;
		}else{
			$http_method = OAUTH_HTTP_METHOD_POST;
		}
		$headers = array();
		if($multipart){
      $boundary = '-TuItTr';
      $headers['Content-Type'] = "multipart/form-data; boundary={$boundary}";
			$body = '';
      foreach($opt as $parts){
        $body .= "--{$boundary}\r\n";
        foreach($parts['header'] as $key => $val){
          $body .= "{$key}: {$val}\r\n";
        }
        $body .= "\r\n{$parts['body']}\r\n";
        $body .= "--{$boundary}--\r\n";
      }
			$opt = $body;
		}
		$this->_oauth->fetch('http://'.$host.$url.'.xml', $opt, $http_method, $headers);
		return $this->_oauth->getLastResponse();
	}
}
