<?php

class CreateOrder extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->connection->new_table('orders');
    $t->add_timestamps();
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('orders');
  }
}

?>
