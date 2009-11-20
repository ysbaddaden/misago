<?php

class CreateProduct extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->db->new_table('products');
    
    $t->add_column('name',  'string', array('null' => false, 'limit' => 100));
    $t->add_column('price', 'float',  array('null' => false));
    $t->add_timestamps();
    
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('products');
  }
}

?>
