<?php

class CreateBasket extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->db->new_table('baskets');
    $t->add_column('order_id',   'integer');
    $t->add_column('product_id', 'integer');
    $t->add_timestamps();
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('baskets');
  }
}

?>
