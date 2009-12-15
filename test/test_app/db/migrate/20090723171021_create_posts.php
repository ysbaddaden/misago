<?php

class CreatePost extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->connection->new_table('posts');
    $t->add_column('title', 'string', array('null' => false));
    $t->add_column('body',  'text');
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('posts');
  }
}

?>
