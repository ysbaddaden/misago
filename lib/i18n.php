<?php

# Shortcut for I18n::translate();
function t($str, $ctx=null, $options=null)
{
  $options['ctx'] = $ctx;
  return I18n::translate($str, &$options);
}

# Handles translations of strings.
# 
# TODO: I18n::available_locales();
# TODO: Implement interpolation. ie. t("foo {{bar}}", array('bar' => 'baz'))
# TODO: Implement pluralization. ie. t(array("post", "posts"), array('count' => 1))
class I18n
{
  static private $locale       = 'en';
  static private $translations = array();
  
  # Starts the I18n translator.
  static function startup()
  {
    if (cfg::is_set('i18n_default_locale')) {
      self::$locale = cfg::get('i18n_default_locale');
    }
    self::load_translations(self::$locale);
  }
  
  # Returns the list of available locales.
  static function available_locales()
  {
    // ...
  }
  
  # Sets the current locale, and loads associated translations.
  static function locale($locale=null)
  {
    self::$locale = $locale;
    self::load_translations(self::$locale);
  }
  
  # Finds the translation for a string, in a particular (or global) context.
  static function translate($str, $options=null)
  {
    $ctx = isset($options['ctx']) ? $options['ctx'] : null;
    $key = empty($ctx) ? $str : "$ctx.$str";
    
    if (isset(self::$translations[self::$locale][$key])) {
      return self::$translations[self::$locale][$key];
    }
    else {
      trigger_error("Missing translation for \"$str\"".(($str !== null) ? " in context '$ctx'" : '').".", E_USER_NOTICE);
    }
    return $str;
  }
  
  
  static private function load_translations($locale)
  {
    if (isset(self::$translations[$locale])) {
      return;
    }
    
    $translations = apc_fetch(TMP."/cache/locales.$locale.php", $success);
    if ($success === false)
    {
      # misago's translations
      $contents     = file_get_contents(MISAGO."/lib/locales/$locale.yml");
      $translations = Yaml::decode($contents);

      # app's translations
      $locale_file = ROOT."/config/locales/$locale.yml";
      if (file_exists($locale_file))
      {
        $contents      = file_get_contents($locale_file);
        $_translations = Yaml::decode($contents);
        $translations  = hash_merge_recursive($translations, $_translations);
      }
      
      $translations = self::flatten_translation_keys($translations[$locale]);
      apc_store(TMP."/cache/locales.$locale.php", $translations, strtotime('+1 day'));
    }
    
    self::$translations[$locale] = $translations;
  }
  
  static private function & flatten_translation_keys($ary, $parent='')
  {
    static $hash = array();
    
    foreach($ary as $k => $v)
    {
      if (is_array($v)) {
        self::flatten_translation_keys($v, "$parent$k.");
      }
      else {
        $hash["$parent$k"] = $v;
      }
    }
    
    return $hash;
  } 
}

?>
