<?php
require ROOT.'/config/initializers/inflections.php';

function is_symbol($str)
{
  return (strpos($str, ':') === 0);
}

/**
 * Extensions for strings.
 * 
 * @package ActiveSupport
 * @subpackage Array
 */
class String extends Inflections
{
	static protected $trans;
	
	/**
	 * Transforms to CamelText (eg: BlogComment).
	 */
	static function camelize($str)
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
	}
	
	/**
	 * Transforms to underscore_text (eg: blog_comment).
	 */
	static function underscore($str)
	{
		return str_replace(' ', '_', strtolower(preg_replace('/(?<=\w)([A-Z])/', ' \1', $str)));
	}
	
	/**
	 * Transforms to camelBacked style (eg: blogComment).
	 */
	static function variablize($str)
	{
		$str = self::camelize($str);
		return strtolower(substr($str, 0, 1)).substr($str, 1);
	}
	
	/**
	 * Singularizes a word.
	 */
	static function singularize($str)
	{
		if (in_array(strtolower($str), self::$constants)) {
			return $str;
		}
		
		foreach(self::$singularize_rules as $rule => $value)
		{
			if (preg_match($rule, $str)) {
				return preg_replace($rule, $value, $str);
		  }
		}
		return $str;
	}
	
	/**
	 *Pluralizes a word.
	 */
	static function pluralize($str)
	{
		if (in_array(strtolower($str), self::$constants)) {
			return $str;
		}
		
		foreach(self::$pluralize_rules as $rule => $value)
		{
			if (preg_match($rule, $str)) {
				return preg_replace($rule, $value, $str);
			}
		}
		return $str;
	}
	
	/**
	 * Transforms a string to an URL style.
	 * 
	 * Changes spaces and non ascii chars to a dash,
	 * but should preserve accented characters).
	 * 
	 * <code>
	 * "This is a title" => "this-is-a-title"
	 * </code>
	 */
	static function slug($str, $strtolower=true)
	{
		if ($strtolower) {
			$str = strtolower($str);
		}
		
		# incompatible with unicode strings!
#		$str = preg_replace('/[^\d\w]+/u','-', $str);
		
		# hack so accented characters are not removed:
#		$charset = Core::charset;
    $charset = 'UTF-8';
		$str = htmlentities($str, ENT_NOQUOTES, $charset);
		$str = preg_replace(array('{([^\d\w\.&;]+|&(lt|gt|amp|nbsp);)}', '{[-]+}'), '-', $str);
		$str = html_entity_decode($str, ENT_NOQUOTES, $charset);
		
    # trims dashes, etc.
		return trim($str, '-.&;');
	}
	
	static function humanize($str)
	{
	  return ucfirst(str_replace('_', ' ', String::underscore($str)));
	}
}

?>
