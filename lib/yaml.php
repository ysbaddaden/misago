<?php

class Yaml
{
  static function decode($yaml)
  {
    $spyc = new Spyc();
    return $spyc->load($input);
  }
  
  static function encode($string)
  {
    $spyc = new Spyc();
    return $spyc->dump($array, false, false);
  }
}

?>
