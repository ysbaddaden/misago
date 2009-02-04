<?php

class #{Class} extends ActiveRecord_Migration
{
  function up()
  {
    $this->db->create_table('#{table}', array(
      
    ));
  }
  
  function down()
  {
    $this->db->drop_table('#{table}');
  }
}

?>
