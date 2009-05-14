<?php

class CreateProgrammer extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('programmers');
    $t->add_column('string', 'name', array('limit' => 60));
    if ($t->create())
    {
      $t = $this->db->new_table('programmers_projects', array('id' => false));
      $t->add_column('integer', 'programmer_id');
      $t->add_column('integer', 'project_id');
      return $t->create();
    }
    return false;
  }
  
  function down()
  {
    if ($this->db->drop_table('programmers'))
    {
      return $this->db->drop_table('programmers_projects');
    }
  }
}

?>
