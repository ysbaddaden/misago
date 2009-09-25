<?php

class Unit_Assertions_ModelAssertions extends Unit_Test
{
  # Ensures ActiveRecord is valid, otherwise displays list of errors.
  function assert_valid($record, $comment='')
  {
    $this->count_assertions += 1;
    
    if (!$record->is_valid())
    {
      $this->count_failures += 1;
      printf("\n".Terminal::colorize("%s failed:", 'RED')." %s\n", $this->running_test, $comment);
      printf(Terminal::colorize("    errors:", 'BOLD')." %s\n", print_r($record->errors->full_messages(), true));
    }
  }
}

?>
