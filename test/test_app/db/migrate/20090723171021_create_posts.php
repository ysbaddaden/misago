<?php

class CreatePost extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->connection->new_table('posts');
    $t->add_column('title', 'string', array('null' => false));
    $t->add_column('body',  'text');
    $t->add_column('comment_count',  'integer');
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('posts');
  }
}

?>
