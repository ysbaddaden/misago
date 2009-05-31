<?php

class CreateProject extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('projects');
    $t->add_column('title', 'string', array('limit' => 60));
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('projects');
  }
}

?>
