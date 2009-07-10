<?php

# Generic object, to share methods between all misago's classes.
abstract class Object
{
  function to_s()
  {
#    $class = get_class($this);
#    throw new Exception("$class::to_s() is unimplemented.");
    return $this->__toString();
  }
  
  function to_xml()
  {
    return $this->to_s();
  }

  function to_json()
  {
    return $this->to_s();
  }
}

?>
