<?php

# Shortcut for I18n::translate();
# 
# Examples:
# 
#   t($string, $context);
#   t($string, array('context' => $context));
#   t("foor {{bar}}", array('context' => $context, 'bar' => 'baz'));
# 
function t($str, $options=null)
{
  if (!is_array($options) and !empty($options)) {
    $options = array('context' => $options);
  }
  return I18n::translate($str, $options);
}

# Localizes certain objects like dates.
function l($obj, $options=array())
{
  return I18n::localize($obj, $options);
}

# Handles translation of strings.
# 
# =Translating a string
# 
#   I18n::translate('my string')
#   t('my other string')
# 
# =Context
# 
# You may specify a particular context to search the translation in.
# Either as passing the 'context' option, or by prepending the
# string in the following way: +context.string+
# 
# You may have subcontexts, like: +active_record.error.messages.empty+
# 
# =Interpolation
# 
# Any other option, plus the +count+ option, will be used for interpolating
# variables in the string.
# 
# Example:
# 
#   t('{{user_name}} sent you a message', array('user_name' => 'James'));
#   # => James sent you a message
# 
# =Pluralization [todo, specification unfinished]
# 
# There must multiple translations in your YAML file for a single message.
# In fact as many translations as there are possible results to the above
# computing. Eventually, you need to pass a +count+ interpolation variable,
# that will determine which translation to use.
# 
# Depending on your locale, a computing is done to determine which translation
# must be selected based on the +count+ variable. The algorythm is the same
# than the one used by +gettext+.
# 
# Examples:
# 
#   en:
#     x_minutes:
#       0: "a minute"
#       1: "{{count}} minutes"
#     there_are_x_messages:
#       "There is {{count}} message"
#       "There are {{count}} messages"
#   
#   t('x_minutes', array('count' => 1));   # => 'a minute'
#   t('x_minutes', array('count' => 12));  # => '12 minutes'
#   t('there_are_x_messages', array('count' => 0));   # => There are 0 messages
#   t('there_are_x_messages', array('count' => 1));   # => There is 1 message
#   t('there_are_x_messages', array('count' => 29));  # => There are 29 messages
# 
# IMPROVE: Add possibility to separate translations in a directory structure (ie. use context as subpath).
class I18n
{
  static public  $locale       = 'en';
  static private $translations = array();
  
  # Initializes the I18n translator.
  static function initialize()
  {
    if (cfg_isset('i18n_default_locale')) {
      self::$locale = cfg_get('i18n_default_locale');
    }
    self::load_translations(self::$locale);
  }
  
  # Returns the list of available locales.
  static function available_locales()
  {
    // ...
  }
  
  # Sets or returns the current locale.
  # On setting, loads associated translation strings.
  static function locale($locale=null)
  {
    if ($locale !== null)
    {
      self::$locale = $locale;
      self::load_translations(self::$locale);
    }
    return self::$locale;
  }
  
  # Finds the translation for a string. Returns the string unstranslated
  # if no translation is found.
  # 
  # FIXME: Missing interpolation when translation isn't found. eg: t('this is my {{name}}', array('name' => $name)).
  static function translate($str, $options=null)
  {
    $translation = self::do_translate($str, $options);
    return ($translation !== null) ? $translation : $str;
  }
  
  # Same as <tt>translate</tt>, but returns null if no translation is found.
  static function do_translate($str, $options=array())
  {
    $ctx = isset($options['context']) ? $options['context'] : null;
    $key = empty($ctx) ? $str : "$ctx.$str";
    
    if (isset(self::$translations[self::$locale][$key]))
    {
      # translation exists
      if (is_array(self::$translations[self::$locale][$key]))
      {
        # plural
        $n = self::plural($options['count']);
        $translation = self::$translations[self::$locale][$key][$n];
      }
      else
      {
        # singular
        $translation = self::$translations[self::$locale][$key];
      }
      
      if (!empty($options))
      {
        # interpolation
        $vars = array();
        foreach($options as $k => $v) {
          $vars['{{'.$k.'}}'] = $v;
        }
        return strtr($translation, $vars);
      }
      
      return $translation;
    }
    return null;
  }
  
  # Localizes certain objects like dates.
  static function localize($obj, $options=array())
  {
    switch(get_class($obj))
    {
      case 'ActiveSupport_Datetime':
      case 'ActiveSupport_Date': return $obj->format(self::translate(isset($options['format']) ? $options['format'] : 'default', array('context' => 'date.formats')));
      case 'ActiveSupport_Time': return $obj->format(self::translate(isset($options['format']) ? $options['format'] : 'default', array('context' => 'time.formats')));
      case 'Time': return $obj->format(self::translate($obj->type, array('context' => 'localize')));
      default: return (string)$obj;
    }
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
      
      if (!empty($translations[$locale])) {
        $translations = self::flatten_translation_keys($translations[$locale]);
      }
      apc_store(TMP."/cache/locales.$locale.php", $translations, strtotime('+1 day'));
    }
    
    self::$translations[$locale] = $translations;
  }
  
  static private function & flatten_translation_keys($ary, $parent='')
  {
    static $hash = array();
    
    # plural translation is an array
    if (!is_hash($ary))
    {
      $hash[rtrim($parent, '.')] = $ary;
      return $hash;
    }
    
    # we flatten hashes
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
  
  static private function plural($n)
  {
    switch(self::$locale)
    {
      case 'fr': return ($n > 1)  ? 1 : 0;
      case 'en': return ($n != 1) ? 1 : 0;
    }
  }
}

?>
