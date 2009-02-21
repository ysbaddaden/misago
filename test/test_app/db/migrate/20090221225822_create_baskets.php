<?php

class CreateBasket extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('baskets');
    
    $t->add_column('string', 'order_id');
    $t->add_column('string', 'product_id');
    
    $t->add_timestamps();
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('baskets');
  }
}

?>
