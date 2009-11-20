<?php

class AddingNullableFieldToProduct extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $this->db->add_column('products', 'in_stock',    'boolean');
    $this->db->add_column('products', 'description', 'text');
    return true;
  }
  
  function down()
  {
    $this->db->drop_column('products', 'in_stock');
    $this->db->drop_column('products', 'description');
    return true;
  }
}

?>
