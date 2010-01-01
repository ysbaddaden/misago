<?php
namespace Misago\Unit\Assertions;
use Misago\Terminal;

abstract class ModelAssertions extends \Test\Unit\TestCase
{
  # Ensures +ActiveRecord+ is valid, otherwise displays list of errors.
  function assert_valid($record, $message='')
  {
    $is_valid = $record->is_valid();
    $message  = $this->build_message($message, "Record is invalid:\n%s",
      "\n  ".implode("\n  ", $record->errors->full_messages()));
    
    $this->assert_block($message, function() use($is_valid) {
      return $is_valid;
    });
  }
  
  # Ensures +ActiveRecord+ is invalid.
  function assert_invalid($record, $message='')
  {
    $this->assert_block($message, function() use($record) {
      return !$record->is_valid();
    });
  }
}

?>
