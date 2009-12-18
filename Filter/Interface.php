<?php

interface Tuitter_Filter_Interface
{
	public function check(Tuitter_Tweet $tweet);
}
