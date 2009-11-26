<?php

abstract class Tuitter_XmlResult
{
	protected $_tuitter;

	public function __construct(&$tuitter, $xml=null)
	{
		$this->_tuitter = &$tuitter;
		if($xml !== null){
			$parser = xml_parser_create('UTF-8');
			xml_set_object($parser, $this);
			xml_set_element_handler($parser, '_startElement', '_endElement');
			xml_set_character_data_handler($parser, '_cData');
			xml_parse($parser, $xml, true);
			xml_parser_free($parser);
		}
	}

	abstract protected function _startElement($parser, $tag, $attr);
	abstract protected function _endElement($parser, $tag);
	abstract protected function _cData($parser, $data);
}
