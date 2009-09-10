<?php

class CreateTag extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('tags');
    $t->add_column('post_id', 'integer', array('null' => false));
    $t->add_column('tag',     'text',    array('null' => false));
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('tags');
  }
}

?>
