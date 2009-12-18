<?php

if($_SERVER['argc'] < 3){
	echo "Specify your Twitter account name and the password\n";
	exit;
}
$user = $_SERVER['argv'][1];
$passwd = $_SERVER['argv'][2];

require_once dirname(__FILE__).'/Tuitter.php';

try {
	$tuitter = new Tuitter($user, $passwd);

	if(!$tuitter->isFollowing('nao58')){
		$tuitter->follow('nao58');
		$tuitter->sendMessage('This is my first tweet by Tuitter, @nao58 !');
	}else{
		$tl = $tuitter->getHomeTL();
		foreach($tl as $tweet){
			echo $tweet->user->screen_name."\n";
			echo $tweet->text."\n\n";
		}
	}
}catch(Exception $e){
	echo "Error: ".$e->getMessage()."\n";
}
