<?php

class CreateError extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->db->new_table('errors');
    
    $t->add_column('title', 'string');
    $t->add_column('subtitle', 'string');
    $t->add_column('domain', 'string');
    
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('errors');
  }
}

?>
