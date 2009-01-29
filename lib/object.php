<?php

abstract class Object
{
  function to_s()
  {
    $class = get_class($this);
    throw new Exception("$class::to_s() is unimplemented.");
  }
}

?>
