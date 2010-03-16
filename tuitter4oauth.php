<?php

$conskey = 'YOUR_CONSUMER_KEY';
$conssec = 'YOUR_CONSUMER_SECRET';

$req_url = 'http://api.twitter.com/oauth/request_token';
$authurl = 'http://api.twitter.com/oauth/authorize';
$acc_url = 'http://api.twitter.com/oauth/access_token';

session_start();

if(!isset($_GET['oauth_token']) && $_SESSION['state']==1) $_SESSION['state'] = 0;
try {
  $oauth = new OAuth($conskey,$conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
  if(!isset($_GET['oauth_token']) && !$_SESSION['state']) {
    $request_token_info = $oauth->getRequestToken($req_url);
    $_SESSION['secret'] = $request_token_info['oauth_token_secret'];
    $_SESSION['state'] = 1;
    header('Location: '.$authurl.'?oauth_token='.$request_token_info['oauth_token']);
    exit;
  } else if($_SESSION['state']==1) {
    $oauth->setToken($_GET['oauth_token'],$_SESSION['secret']);
    $access_token_info = $oauth->getAccessToken($acc_url);
    $_SESSION['state'] = 2;
    $_SESSION['token'] = $access_token_info['oauth_token'];
    $_SESSION['secret'] = $access_token_info['oauth_token_secret'];
  } 
	echo "<div><label>token<br/><input type=\"text\" value=\"{$_SESSION['token']}\" style=\"width: 400px;\" /></label></div>";
	echo "<div><label>secret<br/><input type=\"text\" value=\"{$_SESSION['secret']}\" style=\"width: 400px;\" /></label></div>";
} catch(OAuthException $E) {
  print_r($E);
}
