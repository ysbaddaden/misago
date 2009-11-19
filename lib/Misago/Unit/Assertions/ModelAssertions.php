<?php
namespace Misago\Unit\Assertions;
use Misago\Terminal;

class ModelAssertions extends \Misago\Unit\Test
{
  # Ensures +ActiveRecord+ is valid, otherwise displays list of errors.
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
  
  # Ensures +ActiveRecord+ is invalid.
  function assert_invalid($record, $comment='')
  {
    $this->count_assertions += 1;
    
    if ($record->is_valid())
    {
      $this->count_failures += 1;
      printf("\n".Terminal::colorize("%s failed:", 'RED')." %s\n", $this->running_test, $comment);
      printf(Terminal::colorize("  expected:", 'BOLD')." invalid record\n");
    }
  }
}

?>
