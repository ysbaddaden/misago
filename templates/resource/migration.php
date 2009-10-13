<?php

class #{Class} extends ActiveRecord_Migration
{
  function up()
  {
    $t = $this->connection->new_table('#{table}');
    #$t->add_column('title', 'string');
    $t->add_timestamps();
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('#{table}');
  }
}

?>
