<?php

# :namespace: Misago\ActiveSupport\Yaml
function yaml_decode($input)
{
  $spyc = new \Spyc();
  return $spyc->load($input);
}

# :namespace: Misago\ActiveSupport\Yaml
function yaml_encode($array)
{
  $spyc = new \Spyc();
  return $spyc->dump($array, false, false);
}

?>
