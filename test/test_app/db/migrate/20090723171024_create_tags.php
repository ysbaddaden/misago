<?php

class CreateTag extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->connection->new_table('tags');
    $t->add_column('post_id', 'integer', array('null' => false));
    $t->add_column('tag',     'text',    array('null' => false));
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('tags');
  }
}

?>
