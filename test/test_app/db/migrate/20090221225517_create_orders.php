<?php

class CreateOrder extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->db->new_table('orders');
    $t->add_timestamps();
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('orders');
  }
}

?>
