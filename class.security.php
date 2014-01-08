<?php

class classMySec {
  function encode($data) {
	if (is_array($data)) return array_map(array($this,'encode'), $data);
	if (is_object($data)) {
	  $tmp = clone $data; // avoid modifing original object
	  foreach ( $data as $k => $var ) $tmp->{$k} = $this->encode($var);
	  return $tmp;
	}
	return htmlentities($data);
  }
	
  function decode($data) {
	if (is_array($data)) return array_map(array($this,'decode'), $data);
	if (is_object($data)) {
	  $tmp = clone $data; // avoid modifing original object
	  foreach ( $data as $k => $var ) $tmp->{$k} = $this->decode($var);
	  return $tmp;
	}
	return html_entity_decode($data);
  }
}

########## Base variables ##########
# SET VAR

$argv = array_map('c_sec_userinput', $argv);
$_GET = array_map('c_sec_userinput', $_GET);
$_POST = array_map('c_sec_userinput', $_POST);
$_COOKIE = array_map('c_sec_userinput', $_COOKIE);
$_REQUEST = array_map('c_sec_userinput', $_REQUEST);

function c_sec_userinput($input){
  if(!isset($input)) return;
  $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
  $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
  if(is_array($input)){
	foreach ($input as $key){
	  if(get_magic_quotes_gpc()) $return[] = str_replace($search, $replace, stripslashes(c_sec_safe($key)));
	  else $return[] = str_replace($search, $replace, c_sec_safe($key));
	}	 
	return $return; 
  }else{
	if(get_magic_quotes_gpc()) return str_replace($search, $replace, stripslashes(c_sec_safe($input)));
	else return str_replace($search, $replace, c_sec_safe($input));
  }
}

function c_sec_safe($str){
  if(!isset($str))return;
  
  $class = new classMySec();
  $decode = $class->decode($str);
	
  if(preg_match('/(<\s*SCRIPT|SCRIPT\s*>)/i', $decode)) return;
  if(preg_match('/(<\s*IFRAME|IFRAME\s*>)/i', $decode)) return;
  if(preg_match('/(UNION|SELECT|CONCAT|DELETE|INSERT|DROP|FROM|WHERE) /i', $decode)) return;
  if(preg_match('/(UNION|SELECT|CONCAT|DELETE|INSERT|DROP|FROM|WHERE)\(/i', $decode)) return;
  if(preg_match('/\/\*/i', $decode)) return;
  if(preg_match('/\-\-/i', $decode)) return;
  return $str;
}

?>