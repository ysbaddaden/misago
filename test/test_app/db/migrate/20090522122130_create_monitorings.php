<?php

class CreateMonitoring extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('monitorings');
    
    # validates_presence_of
    $t->add_column('title',       'string');
    $t->add_column('description', 'string');
    
    # validates_format_of
    $t->add_column('email',  'string', array('null' => false));
    $t->add_column('email2', 'string');
    
    # validates_length_of
    $t->add_column('length_string',   'string', array('limit' => 20));
    $t->add_column('length_string2',  'string', array('limit' => 20));
    $t->add_column('length_is',       'string', array('limit' => 40));
    $t->add_column('length_is2',      'string', array('limit' => 50));
    $t->add_column('length_minmax',   'integer');
    $t->add_column('length_within',   'integer');
    $t->add_column('length_date',     'date');
    $t->add_column('length_datetime', 'datetime');
    $t->add_column('length_time',     'time');
    
    # validates_inclusion_of
    $t->add_column('inclusion_string',  'string');
    $t->add_column('inclusion_integer', 'integer');
    
    # validates_exclusion_of
    $t->add_column('exclusion_string',  'string');
    $t->add_column('exclusion_integer', 'integer');
    
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('monitorings');
  }
}

?>
