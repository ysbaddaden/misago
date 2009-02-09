<?php
/**
 * Handles exceptions for ActiveRecord classes.
 * 
 * @package ActiveRecord
 */
class ActiveRecord_Exception extends MisagoException
{
  const CantConnect        = 1;
  const CantSelectDatabase = 2;
  const NoSuchTable        = 3;
  
  const IrreversibleMigration = 10;
}

?>
