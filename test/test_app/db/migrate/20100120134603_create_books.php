<?php

class CreateBook extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->connection->new_table('books');
    $t->add_column('title', 'integer');
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('books');
  }
}

?>
