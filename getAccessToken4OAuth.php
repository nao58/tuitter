<?php

define('TWITTER_OAUTH_PATH', '');

if($_SERVER['argc'] < 3){
	echo "Specify your consumer-key and consumer-secret.\n";
	exit(1);
}

$consKey = $_SERVER['argv'][1];
$consSecret = $_SERVER['argv'][2];

$oauth = new OAuth($consKey, $consSecret);
$oauth->enableDebug();
$accessTokenInfo = $oauth->getAccessToken("http://api.twitter.com/oauth/access_token", "2869329", "2869329");
print_r($accessTokenInfo);
exit;

$reqTokenInfo = $oauth->getRequestToken("http://twitter.com/oauth/request_token");
if(!empty($reqTokenInfo)){
	/*
	$prms = array(
		'oauth_consumer_key' => oauth_urlencode($consSecret),
		'oauth_token' => oauth_urlencode($reqTokenInfo['oauth_token']),
		'oauth_signature_method' => 'HMAC-SHA1',
		'oauth_signature' => oauth_urlencode($reqTokenInfo['oauth_token_secret']),
		'oauth_timestamp' => time()
	);
	*/
	$auth = "http://twitter.com/oauth/authorize?oauth_token=".$reqTokenInfo['oauth_token'];
	echo "$auth\n";
}
