<?php
require __DIR__.'/TagHelper_ns.php';

# Renders an HTML tag.
# 
# Inline tags:
# 
#   tag('hr')
#   tag('a', array('href' => 'http://www.toto.com/'))
# 
# Content tags:
# 
#   tag('article', $content)
#   tag('div', $content, array('class' => 'article'))
# 
# :namespace: Misago\ActionView\Helpers\TagHelper
function tag($name, $content=null, $attributes=null)
{
  if (is_array($content))
  {
    $attributes = $content;
    $content    = null;
    $inline_tag = true;
  }
  else {
    $inline_tag = ($content === null and $attributes === null);
  }
  
  $attributes = Misago\ActionView\Helpers\TagHelper\parse_attributes($attributes);
  
  if ($inline_tag) {
    return "<$name$attributes/>";
  }
  return "<$name$attributes>$content</$name>";
}

# Renders a CDATA section.
# 
# :namespace: Misago\ActionView\Helpers\TagHelper
function cdata_section($content)
{
  return "<![CDATA[$content]]>";
}

?>
