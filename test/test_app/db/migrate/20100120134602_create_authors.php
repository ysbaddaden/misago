<?php

class CreateAuthor extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->connection->new_table('authors');
    $t->add_column('name', 'string', array('null' => false));
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('authors');
  }
}

?>
