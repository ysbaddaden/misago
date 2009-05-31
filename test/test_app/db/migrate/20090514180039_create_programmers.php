<?php

class CreateProgrammer extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('programmers');
    $t->add_column('name', 'string', array('limit' => 60));
    if ($t->create())
    {
      $t = $this->db->new_table('programmers_projects', array('id' => false));
      $t->add_column('programmer_id', 'integer');
      $t->add_column('project_id', 'integer');
      return $t->create();
    }
    return false;
  }
  
  function down()
  {
    if ($this->db->drop_table('programmers')) {
      return $this->db->drop_table('programmers_projects');
    }
  }
}

?>
