<?php

class AddingNullableFieldToProduct extends ActiveRecord_Migration
{
  function up()
  {
    $this->db->add_column('products', 'boolean', 'in_stock');
    return true;
  }
  
  function down()
  {
    $this->db->drop_column('products', 'in_stock');
    return true;
  }
}

?>
