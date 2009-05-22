<?php

class CreateMonitoring extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('monitorings');
    
    # validates_presence_of
    $t->add_column('string', 'title');
    $t->add_column('string', 'description');
    
    # validates_length_of
    $t->add_column('string',   'length_string',  array('limit' => 20));
    $t->add_column('string',   'length_string2', array('limit' => 20));
    $t->add_column('string',   'length_is',      array('limit' => 40));
    $t->add_column('integer',  'length_minmax');
    $t->add_column('integer',  'length_within');
    $t->add_column('date',     'length_date');
    $t->add_column('datetime', 'length_datetime');
    $t->add_column('time',     'length_time');
    
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('monitorings');
  }
}

?>
