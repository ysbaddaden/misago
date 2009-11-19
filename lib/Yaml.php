<?php

# YAML serializing.
# 
#   # encode:
#   $data = array(...);
#   $yaml_string = Yaml::encode($data);
#   
#   # decode:
#   $yaml_string = file_get_contents('accounts.yml');
#   $accounts = Yaml::decode($yaml_string);
# 
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
