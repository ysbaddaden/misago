<?php
namespace Misago;
require 'I18n_shortcuts.php';

# Handles translation of strings.
# 
# =Syntax
# 
# Translating a string is just a key/value pairing system. If you want to
# translate 'this is my name' in french, all you need a YAML file like this:
# 
#   fr:
#     "this is my name": "ceci est mon nom"
# 
# And then to translate it, call <tt>Misago\I18n::translate</tt> or it's
# shorcut '+t()+:
# 
#   $translated_string = t('this is my name');
#   # => "ceci est mon nom"
# 
# ==Files
# 
# Translations are stored in a YAML file under +config/locales/+. For instance
# english translations are in +config/locales/en.yml+, while french ones will be
# in +config/locales/fr.yml+, etc.
# 
# You may also separate translations in multiple YAML file, by storing them
# under +config/locales/$locale/+, so you may have:
# 
#   config/locales/en.yml
#   config/locales/en/posts/index.yml
#   config/locales/en/posts/show.yml
#   config/locales/en/posts/new.yml
#   config/locales/en/posts/edit.yml
# 
# =Context
# 
# You may specify a particular context to search the translation in. Either as
# passing the 'context' option, or by prepending the string in the following
# way: +context.string+
# 
# You may have subcontexts, like: +active_record.error.messages+
# 
# This is reflected in the YAML file as hashes:
# 
#   en:
#     active_record:
#       error:
#         messages:
#           empty: "{{attribute} can't be empty"
# 
#   t('empty', array('context' => 'active_record.error.messages',
#     'attribute' => 'name'))
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
# =Pluralization
# 
# Depending on your locale, a computing is done to determine which translation
# must be selected based on the +count+ variable. The algorythm is the same
# than the one used by +gettext+.
# 
# There must be multiple translations in your YAML file for a single message.
# In fact as many translations as there are possible results to the above
# computing. Eventually, you need to pass a +count+ interpolation variable,
# that will determine which translation to use.
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
#   
#   t('there_are_x_messages', array('count' => 0));   # => There are 0 messages
#   t('there_are_x_messages', array('count' => 1));   # => There is 1 message
#   t('there_are_x_messages', array('count' => 29));  # => There are 29 messages
# 
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
  
  # Finds the translation for a string. Returns the unstranslated string
  # if no translation is found.
  static function translate($str, $options=null)
  {
    $translation = self::do_translate($str, $options);
    return ($translation !== null) ?
      $translation : self::interpolate($str, $options);
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
      
      # interpolation
      return self::interpolate($translation, $options);
    }
    return null;
  }
  
  # Localizes certain objects like dates.
  # 
  # Options:
  # 
  # - +format+ - specify a particular format.
  #
  # Standard formats:
  # 
  # - <tt>Misago\ActiveSupport\Date</tt> - +default+, +short+, +long+, +long_ordinal+, +only_day+
  # - <tt>Misago\ActiveSupport\Time</tt> - +default+, +time+, +short+, +long+, +long_ordinal+, +only_seconds+
  # - <tt>Misago\ActiveSupport\Datetime</tt> - +default+, +time+, +short+, +long+, +long_ordinal+, +only_seconds+
  # 
  # You may have your own formats by adding translations within the following
  # contexts:
  # 
  # - <tt>Misago\ActiveSupport\Date</tt> - +date.formats+
  # - <tt>Misago\ActiveSupport\Time</tt> - +time.formats+
  # - <tt>Misago\ActiveSupport\Datetime</tt> - +time.formats+
  # 
  static function localize($obj, $options=array())
  {
    switch(get_class($obj))
    {
      case 'Misago\ActiveSupport\Datetime':
        return $obj->strftime(self::translate(isset($options['format']) ?
          $options['format'] : 'default', array('context' => 'time.formats')));
      
      case 'Misago\ActiveSupport\Date':
        return $obj->strftime(self::translate(isset($options['format']) ?
          $options['format'] : 'default', array('context' => 'date.formats')));
      
      case 'Misago\ActiveSupport\Time':
        return $obj->strftime(self::translate(isset($options['format']) ?
          $options['format'] : 'time', array('context' => 'time.formats')));
      
      default:
        return (string)$obj;
    }
  }
  
  static private function interpolate($str, $options)
  {
    if (empty($options)) {
      return $str;
    }
    
    $vars = array();
    foreach($options as $k => $v) {
      $vars['{{'.$k.'}}'] = $v;
    }
    return strtr($str, $vars);
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
      $contents     = file_get_contents(MISAGO."/lib/Misago/locales/$locale.yml");
      $translations = yaml_decode($contents);
      
      # app's translations
      $locale_file = ROOT."/config/locales/$locale.yml";
      if (file_exists($locale_file)) {
        self::load_translations_from_file($locale_file, $translations);
      }
      
      $locale_path = ROOT."/config/locales/$locale";
      if (file_exists($locale_path) and is_dir($locale_path))
      {
        $d = opendir($locale_path);
        while(($f = readdir($d)) !== false)
        {
          if (strpos($f, '.yml') === strlen($f)-4) {
            self::load_translations_from_file("$locale_path/$f", $translations);
          }
        }
        closedir($d);
      }
      
      if (!empty($translations[$locale])) {
        $translations = self::flatten_translation_keys($translations[$locale]);
      }
      apc_store(TMP."/cache/locales.$locale.php", $translations, strtotime('+1 day'));
    }
    
    self::$translations[$locale] = $translations;
  }
  
  static private function load_translations_from_file($file, &$translations)
  {
    $contents      = file_get_contents($file);
    $_translations = yaml_decode($contents);
    $translations  = hash_merge_recursive($translations, $_translations);
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
