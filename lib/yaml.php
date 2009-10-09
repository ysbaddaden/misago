<?php

class Yaml
{
  static function decode($input)
  {
    $spyc = new Spyc();
    return $spyc->load($input);
  }
  
  static function encode($array)
  {
    $spyc = new Spyc();
    return $spyc->dump($array, false, false);
  }
}

?>
