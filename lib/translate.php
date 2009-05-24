<?php

Translate::startup();

function t($str, $ctx=null) {
  return Translate::find_translation($str, $ctx);
}

# TODO: Enable configuration of default language (currently forced to 'en').
class Translate
{
  static private $lang         = 'en';
  static private $translations = array();
  
  static function startup()
  {
#    if (config::is_set('translate', 'default_language')) {
#      self::$lang = config::get('translate', 'default_language');
#    }
    self::load_translations(self::$lang);
  }
  
  # Finds the translation for a string, in a particular (or global) context.
  static function find_translation($str, $ctx=null)
  {
    $id = ($ctx !== null) ? "$ctx.$str" : $str;
    
    if (isset(self::$translations[self::$lang][$id])) {
      return self::$translations[self::$lang][$id];
    }
    else {
      trigger_error("Missing translation for \"$str\"".(($str !== null) ? " in context '$ctx'" : '').".", E_USER_NOTICE);
    }
    return $str;
  }
  
  static function load_translations($lang)
  {
    if (isset(self::$translations[$lang])) {
      return;
    }
    
    $translations = apc_fetch(TMP."/cache/locales.$lang.php", $success);
    if ($success === false)
    {
      # misago's translations
      $contents     = file_get_contents(MISAGO."/lib/locales/$lang.yml");
      $translations = Yaml::decode($contents);

      # app's translations
      $lang_file = ROOT."/config/locales/$lang.yml";
      if (file_exists($lang_file))
      {
        $contents      = file_get_contents($lang_file);
        $_translations = Yaml::decode($contents);
        $translations  = hash_merge_recursive($translations, $_translations);
      }
      
      $translations = self::flatten_translations_hash($translations[$lang]);
      apc_store(TMP."/cache/locales.$lang.php", $translations, strtotime('+1 day'));
    }
    
    self::$translations[$lang] = $translations;
  }
  
  static private function & flatten_translations_hash($ary, $parent='')
  {
    static $hash = array();
    
    foreach($ary as $k => $v)
    {
      if (is_array($v)) {
        self::flatten_translations_hash($v, "$parent$k.");
      }
      else {
        $hash["$parent$k"] = $v;
      }
    }
    
    return $hash;
  } 
}

?>
