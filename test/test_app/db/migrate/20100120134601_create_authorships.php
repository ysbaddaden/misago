<?php

class CreateAuthorship extends Misago\ActiveRecord\Migration
{
  function up()
  {
    $t = $this->connection->new_table('authorships');
    $t->add_column('author_id', 'integer');
    $t->add_column('book_id',   'integer');
    return $t->create();
  }
  
  function down()
  {
    return $this->connection->drop_table('authorships');
  }
}

?>
