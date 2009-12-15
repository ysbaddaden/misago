<?php

class CreateProject extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->connection->new_table('projects');
    $t->add_column('title', 'string', array('limit' => 60));
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('projects');
  }
}

?>
