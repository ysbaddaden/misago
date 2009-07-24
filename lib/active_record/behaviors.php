<?php

# NOTE: Shall it be integrated into ActiveRecord_Associations?
abstract class ActiveRecord_Behaviors extends ActiveRecord_Validations
{
  protected $behaviors = array();
  
  function __construct()
  {
    foreach($this->behaviors as $behavior => $associations)
    {
      $name = String::camelize($behavior);
      $class = "ActiveRecord_Behaviors_{$behavior}_Base";
      
      foreach($associations as $assoc) {
        new $class($this, $this->associations[$assoc]);
      }
    }
  }
}

?>
