<?php

# TODO: Use a YAML file instead of a PHP class.
class Inflections
{
  static protected $constants = array('wiki');
  
  static protected $singularize_rules = array(
	  '/ses$/' => 's', 
	  '/ies$/' => 'y',
	  '/xes$/' => 'x', 
	  '/s$/'   => ''
  );
  
  static protected $pluralize_rules   = array(
	  '/s$/' => 'ses',
	  '/y$/' => 'ies',
	  '/x$/' => 'xes',
	  '/$/'  => 's'
  );
}

?>
