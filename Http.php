<?php

class Tuitter_Http
{
	static private $_cache;

	public static function send(Tuitter_Http_Request $req, $method='GET')
	{
		$host = $req->getHost();
		$port = $req->getPort();
		$url = $req->getUrl();
		$prms = $req->getPrms();
		$headers = $req->getHeaders();
		$body = '';

		if($prms){
			if($method=='GET'){
				ksort($prms);
				$url .= '?'.http_build_query($prms);
			}else if($method=='POST'){
				if($req->isMultipart()){
					$boundary = '-TuItTr';
					$headers['Content-Type'] = "multipart/form-data; boundary={$boundary}";
					foreach($prms as $parts){
						$body .= "--{$boundary}\r\n";
						foreach($parts['header'] as $key => $val){
							$body .= "{$key}: {$val}\r\n";
						}
						$body .= "\r\n{$parts['body']}\r\n";
					}
					$body .= "--{$boundary}--\r\n";
				}else{
					$body = http_build_query($prms);
				}
				$headers['Content-Length'] = strlen($body);
			}
		}

		$cacheKey = sha1($req->getUser().'@'.$url);
		if($method=='GET'){
			if($cache = self::getCache($cacheKey, $req)){
				if($etag = $cache->getHeader('ETag')){
					$headers['If-None-Match'] = $etag;
				}
			}
		}

		$r[] = "{$method} {$url} HTTP/1.1";
		$r[] = "HOST: {$host}";
		foreach($headers as $key => $val){
			$r[] = "$key: $val";
		}
		$r[] = '';

		if(($fp = fsockopen($host, $port))){
			fputs($fp, implode("\r\n", $r)."\r\n".$body);
			$response = '';
			while(!feof($fp)){
				$response .= fgets($fp, 4096);
			}
			fclose($fp);
			$res = new Tuitter_Http_Response($response);
		}

		if($method=='GET'){
			$res = self::putCache($cacheKey, $cache, $res);
		}

		return $res;
	}

	public static function setCache($cache)
	{
		self::$_cache = $cache;
	}

	public static function getCache($key, Tuitter_Http_Request $req)
	{
		if($cache = self::$_cache){
			$cacheKey = $key.'.cache';
			if($r = self::$_cache->get($cacheKey)){
				$res = new Tuitter_Http_Response($r);
				return $res;
			}
		}
	}

	public static function putCache($key, $cached, Tuitter_Http_Response $res)
	{
		if($cache = self::$_cache){
			$sts = $res->getStatus();
			if($sts==304){
				return $cached;
			}else if($sts==200){
				$cacheKey = $key.'.cache';
				$cache->put($cacheKey, $res->getResponse());
			}
		}
		return $res;
	}
}
