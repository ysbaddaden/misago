<?php

class #{Class} extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->db->new_table('#{table}');
    
    #$t->add_column('string', 'title');
    
    $t->add_timestamps();
    $t->create();
  }
  
  function down()
  {
    $this->db->drop_table('#{table}');
  }
}

?>
