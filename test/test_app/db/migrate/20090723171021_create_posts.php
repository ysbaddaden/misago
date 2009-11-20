<?php

class CreatePost extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->db->new_table('posts');
    $t->add_column('title', 'string', array('null' => false));
    $t->add_column('body',  'text');
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('posts');
  }
}

?>
