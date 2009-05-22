<?php

class CreateMonitoring extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('monitorings');
    $t->add_column('string', 'title');
    $t->add_column('string', 'description');
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('monitorings');
  }
}

?>
