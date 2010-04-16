<?php

# Transforms all links and email addresses to clickable links.
# 
# - +link+ - limits what should be linked (either one of all, email_addresses or urls).
# 
# :namespace: Misago\ActionView\Helpers\TextHelper
function auto_link($text, $link='all', $href_options=array())
{
  if ($link == 'all' or $link == 'urls')
  {
    $text = preg_replace_callback('/(?:http|https|ftp|sftp|ssh):\/\/[^ ]+/', function($match) use($href_options) {
      return link_to($match[0], null, $href_options);
    }, $text);
  }
  if ($link == 'all' or $link == 'email_addresses')
  {
    $text = preg_replace_callback('/[^@ ]+\@[^ ]+/', function($match) use($href_options) {
      return mail_to($match[0], null, $href_options);
    }, $text);
  }
  return $text;
}

# Extracts an excerpt from text that matches the first instance of phrase.
# 
# :namespace: Misago\ActionView\Helpers\TextHelper
function excerpt($text, $phrase, $radius=100, $omission='...')
{
  $pos = strpos($text, $phrase);
  if ($pos === false) {
    return null;
  }

  $pos2 = $pos + strlen($phrase);
  $str = ($pos - $radius <= 0) ? substr($text, 0, $pos2) :
    $omission.substr($text, $pos - $radius, $radius + strlen($phrase));
  $str .= ($pos2 + $radius < strlen($text)) ?
    substr($text, $pos2, $radius).$omission : substr($text, $pos2);
  return $str;
}

# Highlights some phrases in text using the MARK tag.
# 
# :namespace: Misago\ActionView\Helpers\TextHelper
function highlight($text, $phrases, $highlighter='<mark>\1</mark>')
{
  if (is_array($phrases))
  {
    foreach($phrases as $i => $phrase) {
      $phrase[$i] = preg_quote($phrase);
    }
    $phrases = implode('|', $phrases);
  }
  return preg_replace('/('.$phrases.')/', $highlighter, $text);
}

# Pluralizes a word depending on +count+.
# 
# :namespace: Misago\ActionView\Helpers\TextHelper
function pluralize($count, $singular, $plural=null)
{
  if ($count != 1) {
    return ($plural === null) ? Misago\ActiveSupport\String::pluralize($singular) : $plural;
  }
  return $singular;
}

# Simple formatting of text. Two linefeeds make for a paragraph,
# and a single linefeed makes for a line break.
# 
# :namespace: Misago\ActionView\Helpers\TextHelper
function simple_format($text)
{
  $text = str_replace("\r", "", trim($text));
  $text = preg_replace("/\n\n+/", "</p><p>", $text);
  $text = preg_replace("/\n/", "<br/>", $text);
  return "<p>$text</p>";
}

# Truncates a string if it's longer than length, and appends a truncate string to it.
# 
# :namespace: Misago\ActionView\Helpers\TextHelper
function truncate($text, $length=30, $truncate_string='...')
{
  if (strlen($text) > $length) {
    return substr($text, 0, $length - strlen($truncate_string)).$truncate_string;
  }
  return $text;
}

# Formats text using the Markdown syntax.
# 
# See http://daringfireball.net/projects/markdown/ for documentation.
# 
# Requires PHP Markdown or PHP Markdown Extra by Michel Fortin:
# http://michelf.com/projects/php-markdown/
# 
# You must strip all functions from +markdown.php+, keep defines,
# rename the +Markdown_Parser+ class to +Markdown+, and eventually
# save it as +vendor/Markdown.php+.
# 
# :namespace: Misago\ActionView\Helpers\TextHelper
function markdown($text)
{
	$parser = new Markdown();
	return $parser->transform($text);
}

# Formats text using the Textile syntax.
# 
# Download Textile from http://textile.thresholdstate.com/ and copy
# +classTextile.php+ to +vendor/Textile.php+
# 
# :namespace: Misago\ActionView\Helpers\TextHelper
function textilize($text)
{
  $parser = new Textile();
  return $parser->TextileThis($text);  
}

?>
