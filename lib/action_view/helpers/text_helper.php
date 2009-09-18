<?php

class ActionView_Helpers_TextHelper_NS
{
  # @private
  static function preg_replace_urls($match)
  {
    return link_to($match[0]);
  }
  
  # @private
  static function preg_replace_email_addresses($match)
  {
    return mail_to($match[0]);
  }
}

# Transforms all links and email addresses to clickable links.
# 
# - link limits what should be linked: all, email_addresses, urls.
# - callback permits to use a function for preg_replace_callback that will replace link's text.
# 
# @namespace ActionView_Helpers_TextHelper
function auto_link($text, $link='all'/*, $href_options=null, $callback=null*/)
{
  if ($link == 'all' or $link == 'urls')
  {
    $text = preg_replace_callback('/(?:http|https|ftp|sftp|ssh):\/\/[^ ]+/',
      array('ActionView_Helpers_TextHelper_NS', 'preg_replace_urls'), $text);
  }
  if ($link == 'all' or $link == 'email_addresses')
  {
    $text = preg_replace_callback('/[^@ ]+\@[^ ]+/',
      array('ActionView_Helpers_TextHelper_NS', 'preg_replace_email_addresses'), $text);
  }
  return $text;
}

# Extracts an excerpt from text that matches the first instance of phrase.
# TODO: excerpt()
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

function pluralize($count, $singular, $plural=null)
{
  if ($count != 1) {
    return ($plural === null) ? String::pluralize($singular) : $plural;
  }
  return $singular;
}

# Simple formatting of text. Two linefeeds make for a paragraph,
# and a single linefeed makes for line break.
function simple_format($text)
{
  $text = str_replace("\r", "", trim($text));
  $text = preg_replace("/\n\n+/", "</p><p>", $text);
  $text = preg_replace("/\n/", "<br/>", $text);
  return "<p>$text</p>";
}

# Truncates a string if it's longer than length, and appends a truncate string to it.
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
# You must strip all functions from markdown.php, keep defines
# and rename the Markdown_Parser class to Markdown. Eventually
# save it as vendor/markdown.php
function markdown($text)
{
	$parser = new Markdown();
	return $parser->transform($text);
}

# Formats text using the Textile syntax.
# 
# Download Textile from http://textile.thresholdstate.com/
# and copy classTextile.php to vendor/textile.php
function textilize($text)
{
  $parser = new Textile();
  return $parser->TextileThis($text);  
}

?>
