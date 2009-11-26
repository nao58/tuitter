<?php

interface Tuitter_Cache_Interface
{
	public function get($key);
	public function put($key, $data);
}
