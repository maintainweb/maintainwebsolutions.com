<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
////	File:
////		xml.php
////	Actions:
////		1) generate xml
////		2) parse xml
////	Account:
////		Added on June 3rd 2010
////	Version:
////		1.0
////
////	Written by Matthew Praetzel. Copyright (c) 2010 Matthew Praetzel.
////////////////////////////////////////////////////////////////////////////////////////////////////

/****************************************Commence Script*******************************************/

if(!class_exists('ternXML')) {
//
class ternXML {
	
	var $init = false;
	var $a = array(
		'root'		=>	'root',
		'data'		=>	array(),
		'default'	=>	'item',
		'cdata'		=>	array()
	);
	var $xml;
	var $parsed = array();
	var $open = array();
	var $index = 0;
	var $root = false;
	
	function compile($a) {
		$this->a = array_merge($this->a,$a);
		$this->init = true;
		
		$this->head();
		$this->body();
		
		return $this->xml;
	}
	function head() {
		$this->xml .= '<?xml version="1.0" encoding="utf-8"?>';
	}
	function body() {
		$this->generate($this->a['data']);
	}
	function generate($a) {
	
		if(is_array($a)) {
			foreach($a as $k => $this->item) {
				
				//set id
				$this->id = uniqid();
				
				//set attributes
				$this->set_attributes();
				//fix offset by array key 'value'
				if(is_array($this->item) and isset($this->item['value'])) {
					$this->item = $this->item['value'];
				}
				
				//add cdata
				if(((!is_array($this->a['cdata']) and $this->a['cdata']) or in_array($k,$this->a['cdata'])) and !is_array($this->item)) {
					$this->item = '<![CDATA['.$this->item.']]>';
				}
				
				//add to array
				$c = count($this->open);
				$this->open[$c] = array();
				$this->open[$c] = array(
					'id'		=>	$this->id,
					'name'		=>	$k,
					//'depth'		=>	$this->parent_is_a_list() ? $this->get_parent_value('depth') : count($this->open)-1,
					'index'		=>	0,
					'is_list'	=>	$this->is_a_list(),
					'count'		=>	count($this->item),
					'item'		=>	$this->item,
					'parent'	=>	$this->open[$c-1]
				);
				
				//add xml
				if($this->is_a_list()) {
					$this->generate($this->item);
				}
				elseif(is_array($this->item)) {
					$this->open_item();
					$this->generate($this->item);
					$this->close_item();
				}
				else {
					$this->open_item();
					$this->item();
					$this->close_item();
				}
				
			}
		}

	}
	function open_item() {
		$this->add_indent(1);
		$this->xml .= '<';
		$this->xml .= $this->parent_is_a_list() ? $this->get_parent_value('name') : $this->get_item_value('name');
		$this->last = $this->open[count($this->open)-1];
		$this->add_attributes();
		$this->xml .= '>';
		$this->increment_index();
	}
	function item() {
		$this->xml .= $this->item;
	}
	function close_item() {
	
		
		
		$this->add_indent(0);
		$this->xml .= '</';
		if($this->parent_is_a_list()) {
			$this->xml .= $this->get_parent_value('name');
		}
		else {
			$this->xml .= $this->get_item_value('name');
		}
		$this->xml .= '>';
		
		if($this->parent_is_a_list() and $this->get_parent_value('index') == $this->get_parent_value('count')) {
			array_pop($this->open);
		}
		//if(!$this->parent_is_a_list()) {
			array_pop($this->open);
		//}

	}
	function add_attributes() {
		if(is_array($this->attr)) {
			foreach((array)$this->attr as $k => $v) {
				$this->xml .= ' '.$k.'="'.$v.'"';
			}
		}
	}
	function set_attributes() {
		if(is_array($this->item['attributes'])) {
			$this->attr = $this->item['attributes'];
			unset($this->item['attributes']);
		}
		else {
			
			$this->attr = false;
		}
	}
	function index_in_parent() {
		if($this->get_parent_value('is_list')) {
			return $this->get_parent_value['index'];
		}
		return 0;
	}
	function increment_index() {
		if($this->get_parent_value('is_list')) {
			$this->open[count($this->open)-2]['index']++;
		}
	}
	function get_parent_value($v) {
		return $this->open[count($this->open)-2][$v];
	}
	function get_item_value($v) {
		return $this->open[count($this->open)-1][$v];
	}
	function get_item() {
		return $this->open[count($this->open)-1][$v];
	}
	function is_a_list() {
		if(is_array($this->item)) {
			$a = is_array($this->item['attributes']) ? $this->item['value'] : $this->item;
			if(count($a) == 0) {
				return false;
			}
			foreach($a as $k => $v) {
				if(!is_numeric($k)) {
					return false;
				}
			}
			return true;
		}
		return false;
	}
	function parent_is_a_list() {
		if($this->get_parent_value('is_list')) {
			return true;
		}
		return false;
	}
	function add_indent($b=0) {
		
		if(!$b and $this->get_parent_value('is_list') and $this->last['parent']['id'] == $this->get_item_value('id')) {
			$this->indent();
			return;
		}
		elseif(!$b and $this->last['id'] == $this->get_item_value('id')) {
			return;
		}
		$this->indent();
		
	}
	function indent() {
		$this->xml .= "\n";
		for($i=0;$i<count($this->open)-1;$i++) {
			if(!$this->open[$i]['is_list']) {
				$this->xml .= "\t";
			}
		}
	}


	function parse($x,$v=true) {
		$this->value = $v;
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($this->parser,XML_OPTION_TARGET_ENCODING,'utf-8');
		xml_parser_set_option($this->parser,XML_OPTION_SKIP_WHITE,1);
		xml_set_object($this->parser,$this);
		xml_set_element_handler($this->parser,'parse_open_item','parse_close_item');
		xml_set_character_data_handler($this->parser,'parse_item');
		xml_parse($this->parser,$x,true);
		xml_parser_free($this->parser);
		return $this->parsed;
	}
	function parse_open_item($p,$n,$a) {
		$this->name = $n;
		$this->set_parsed_attributes($a);
		$this->set_parsed_parent();
		$this->set_parsed_offset();
		$this->open_parsed_item();
	}
	function set_parsed_attributes($a) {
		$this->attr = count($a) > 0 ? $a : false;
	}
	function set_parsed_parent() {
		$this->parsed_parent = '';
		for($i=0;$i<count($this->open);$i++) {
			if($this->open[$i]['is_list'] and is_array($this->open[$i+1])) {
				$this->parsed_parent .= $this->open[$i]['value'] ? '["'.$this->open[$i]['name'].'"]["value"]' : '["'.$this->open[$i]['name'].'"]';
				$this->parsed_parent .= $this->open[$i+1]['value'] ? '["'.$this->open[$i+1]['name'].'"]['.$this->open[$i]['index'].']["value"]' : '["'.$this->open[$i+1]['name'].'"]['.$this->open[$i]['index'].']';
				$i++;
			}
			else {
				$this->parsed_parent .=  $this->open[$i]['value'] ? '["'.$this->open[$i]['name'].'"]["value"]' : '["'.$this->open[$i]['name'].'"]';
			}
		}
	}
	function open_parsed_item() {
		if($this->parsed_place_exists() and $this->is_parsed_list()) {
			$this->increment_parent_index();
		}
		elseif($this->parsed_place_exists()) {
			$this->fix_parsed_place();
		}
		else {
			$this->reset_parsed_index();
		}
		$this->open[] = array('name'=>$this->name,'index'=>0,'is_list'=>false,'value'=>$this->attr ? true : false);
		$this->set_parsed_offset();
		
		if($this->attr) {
			eval('$this->parsed'.$this->parsed_offset.' = array("attributes"=>$this->attr,"value"=>"");');
		}
		else {
			eval('$this->parsed'.$this->parsed_offset.' = "";');
		}
	}
	function increment_parent_index() {
		$this->open[count($this->open)-1]['index']++;
	}
	function parsed_place_exists() {
		eval('$i = isset($this->parsed'.$this->parsed_offset.');');
		return $i;
	}
	function fix_parsed_place() {
		$this->open[count($this->open)-1]['is_list'] = true;
		eval('$this->last_parsed = $this->parsed'.$this->parsed_offset.';');
		eval('$this->parsed'.$this->parsed_offset.' = array($this->last_parsed);');
		$this->increment_parent_index();
		$this->is_parsed_list = true;
	}
	function is_parsed_list() {
		if(is_array($this->open[count($this->open)-1]) and $this->open[count($this->open)-1]['is_list']) {
			$this->is_parsed_list = true;
			return true;
		}
		$this->is_parsed_list = false;
		return false;
	}
	function set_parsed_offset() {
		if($this->is_parsed_list) {
			$this->parsed_offset = $this->parsed_offset.'['.$this->get_parsed_index().']';
		}
		else {
			$this->parsed_offset = $this->parsed_parent.'["'.$this->name.'"]';
		}
		$this->is_parsed_list = false;
	}
	function get_parsed_index() {
		return $this->open[count($this->open)-2]['index'];
	}
	function reset_parsed_index() {
		if(is_array($this->open[count($this->open)-1])) {
			$this->open[count($this->open)-1]['index'] = 0;
			$this->open[count($this->open)-1]['is_list'] = false;
		}
	}
	function parse_item($p,$v) {
		$this->parsed_value = strval(ltrim(rtrim($v,"\t\r\n"),"\t\r\n"));
		if($this->parsed_value === '' or (empty($this->parsed_value) and $this->parsed_value !== 0 and $this->parsed_value !== '0') or preg_match("/^[\s]+$/",$this->parsed_value)) {
			return;
		}
		if($this->attr) {
			eval('$this->parsed'.$this->parsed_offset.'["value"] .= "$this->parsed_value";');
		}
		else {
			eval('$this->parsed'.$this->parsed_offset.' .= "$this->parsed_value";');
		}
	}
	function parse_close_item($p,$n) {
		array_pop($this->open);
	}

}

}
	
/****************************************Terminate Script******************************************/
?>