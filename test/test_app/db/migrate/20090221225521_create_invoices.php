<?php

class CreateInvoice extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->connection->new_table('invoices');
    
    $t->add_column('order_id', 'integer');
    $t->add_column('name',     'string');
    $t->add_column('address',  'string');
    
    $t->add_timestamps();
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('invoices');
  }
}

?>
