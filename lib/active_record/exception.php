<?php
/**
 * Handles exceptions for ActiveRecord classes.
 * 
 * @package ActiveRecord
 */
class ActiveRecord_Exception extends MisagoException
{
  const NoSuchTable = 1;
  const IrreversibleMigration = 2;
}

?>
