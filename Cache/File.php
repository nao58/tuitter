<?php

Tuitter::load('Cache/Interface.php');

class Tuitter_Cache_File implements Tuitter_Cache_Interface
{
	private $_dir;

	public function __construct($dir)
	{
		$this->_dir = realpath($dir);
		if(!($this->_dir)){
			throw new Exception("The cache directory '{$dir}' does not exists.");
		}
	}

	public function get($key)
	{
		$file = $this->_dir.DIRECTORY_SEPARATOR.$key;
		if(file_exists($file)){
			return gzinflate(file_get_contents($file));
		}
	}

	public function put($key, $data)
	{
		$file = $this->_dir.DIRECTORY_SEPARATOR.$key;
		return file_put_contents($file, gzdeflate($data));
	}
}
