<?php

# Passes temporary variables to the very next request.
# 
# For instance in your controller:
#
#   if ($this->article->save())
#   {
#     $this->flash['notice'] = 'Article was successfully created.';
#     redirect_to(show_article_path($this->article->id));
#   }
#
# Then in your view:
#
#   <\? if (isset($this->flash['notice'])): ?\>
#     <div class="notice"><\?= $this->flash['notice'] ?\></div>
#   <\? endif; ?\>
# 
# Please remember that variables will be forgotten at the end of the
# following request. Variables are only accessible at request n+1, not
# at request n+2, n+3, etc.
# 
class ActionController_Flash extends ArrayObject
{
  private $hash = array();
  
  function __construct()
  {
    if (isset($_SESSION['flash']))
    {
      if (is_array($_SESSION['flash'])) {
        $hash = $_SESSION['flash'];
      }
      unset($_SESSION['flash']);
    }
    parent::__construct(isset($hash) ? $hash : array());
  }
  
  function __destruct()
  {
    if (isset($_SESSION))
    {
      if (!empty($this->hash)) {
        $_SESSION['flash'] = $this->hash;
      }
      else {
        unset($_SESSION['flash']);
      }
    }
    elseif (!empty($this->hash)) {
      trigger_error("Warning: flash variables were not saved, because session wasn't started.", E_USER_WARNING);
    }
  }
  
  # :nodoc:
  function offsetSet($index, $value)
  {
    $this->hash[$index] = $value;
    parent::offsetSet($index, $value);
  }
  
  # Discards either one variable or the whole set.
  function discard($index=null)
  {
    if ($index === null)
    {
      $this->hash = array();
      $this->exchangeArray(array());
    }
    else
    {
      unset($this->hash[$index]);
      $this->offsetUnset($index);
    }
  }
}

?>
