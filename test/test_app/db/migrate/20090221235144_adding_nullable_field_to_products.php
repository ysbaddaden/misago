<?php

class AddingNullableFieldToProduct extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $this->connection->add_column('products', 'in_stock',    'boolean');
    $this->connection->add_column('products', 'description', 'text');
    return true;
  }
  
  function down()
  {
    $this->connection->drop_column('products', 'in_stock');
    $this->connection->drop_column('products', 'description');
    return true;
  }
}

?>
