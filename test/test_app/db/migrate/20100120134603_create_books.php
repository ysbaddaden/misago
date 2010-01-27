<?php

class CreateBook extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->connection->new_table('books');
    $t->add_column('title',     'string');
    $t->add_column('published', 'boolean');
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('books');
  }
}

?>
