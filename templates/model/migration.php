<?php

class #{Class} extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('#{table}');
    
    #$t->add_column('string', 'title');
    
    $t->add_timestamps();
    return $t->create();
  }
  
  function down()
  {
    return $this->db->drop_table('#{table}');
  }
}

?>
