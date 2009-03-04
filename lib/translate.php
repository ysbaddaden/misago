<?php

class translate
{
  static private $lang         = 'en';
  static private $translations = array();
  
  static function startup()
  {
#    if (config::is_set('translate', 'default_language')) {
#      self::$lang = config::get('translate', 'default_language');
#    }
    self::load_translations();
  }
  
  # TODO: Add support for in context translations.
  static function find_translation($str)
  {
    if (isset(self::$translations[self::$lang][$str]))
    {
      return self::$translations[self::$lang][$str];
    }
    else
    {
      trigger_error("Missing translation for \"$str\".", E_USER_NOTICE);
      return $str;
    }
  }
  
  # OPTIMIZE: Cache parsed YAML file in memory (throught APC).
  # IMPROVE: Check if locale has been loaded, if not include it (whatever the cache says).
  # FIXME: PHP's array_merge_recursive is broken and won't overwrite any value (creating arrays instead).
  static private function load_translations()
  {
    self::$translations = Yaml::decode(file_get_contents(MISAGO.'/lib/locales/'.self::$lang.".yml"));
    
    $file = ROOT."/config/locales/".self::$lang.".yml";
    if (file_exists($file)) {
      self::$translations = hash_merge_recursive(self::$translations, Yaml::decode(file_get_contents($file)));
    }

    print_r(self::$translations);
  }
}

translate::startup();

function t($str)
{
  return translate::find_translation($str);
  
  /*
  # TODO: Move this part to String::replace_vars();
  if (!empty($vars))
  {
    $keys   = array_keys($vars);
    $values = array_values($vars);
    foreach($keys as $i => $k) {
      $keys[$i] = "{{{$keys[$i]}}}";
    }
    return str_replace($keys, $values, $str);
  }
  return $str;
  */
}

?>
