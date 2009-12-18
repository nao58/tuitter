<?php

Tuitter::load('Filter/Interface.php');

class Tuitter_Filter_Mentioned implements Tuitter_Filter_Interface
{
	private $_exp;

	public function __construct()
	{
		$tags = func_get_args();
		if(!$tags) trigger_error('Missing arguments.');
		$esc = array();
		foreach($tags as $tag){
			$esc[] = preg_quote($tag);
		}
		$this->_exp = '/@('.implode('|', $esc).')\W/i';
	}

	public function check(Tuitter_Tweet $tweet)
	{
		return (preg_match($this->_exp, $tweet->text.' ') ? true : false);
	}
}
