<?php

class CreateInvoice extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('invoices');
    
    $t->add_column('integer', 'order_id');
    $t->add_column('string', 'name');
    $t->add_column('string', 'address');
    
    $t->add_timestamps();
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('invoices');
  }
}

?>
