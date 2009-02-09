<?php

class CreateProduct extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('products');
    
    $t->add_column('string', 'name',  array('null' => false, 'limit' => 100));
    $t->add_column('float',  'price', array('null' => false, 'signed' => false));
    
    $t->add_timestamps();
    $t->create();
  }
  
  function down()
  {
    $this->db->drop_table('products');
  }
}

?>
