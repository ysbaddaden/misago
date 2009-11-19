<?php
namespace Misago\Unit;
use Misago\Terminal;

error_reporting(E_ALL | E_STRICT);

class Test
{
  private $count_assertions = 0;
  private $count_tests    = 0;
  private $count_success  = 0;
  private $count_failures = 0;
  private $count_errors   = 0;
  private $time;
  private $running_test;
  
  function __construct()
  {
    $this->logger = \Misago\Logger::singleton();
    $this->run_tests();
  }
  
  protected function run_tests()
  {
    $methods = get_class_methods($this);
    
    # started
    printf("\nLoaded suite %s\n", Terminal::colorize(get_class($this), 'BOLD'));
    $this->setup();
    
    $this->time = microtime(true);
    foreach($methods as $method)
    {
      if (strpos($method, 'test_') !== 0)
      {
        # not a test!
        continue;
      }
      
      $this->running_test = $method;
      $this->count_tests += 1;
      echo ".";
      
      $this->logger->info("------- $method:\n");
      
      $this->run_test($method);
    }
    
    # finished
    $this->unsetup();
    $this->logger->info("-------\n");
    printf("\nFinished in %f seconds.\n", microtime(true) - $this->time);
    
    $text  = sprintf("%d tests, %d assertions, %d failures, %d errors",
      $this->count_tests, $this->count_assertions, $this->count_failures, $this->count_errors);
    echo Terminal::colorize($text, ($this->count_failures + $this->count_errors) ? 'RED' : 'GREEN')."\n";
  }
  
  protected function setup()
  {
    
  }
  
  protected function unsetup()
  {
    
  }
  
  protected function run_test($method)
  {
    try {
      $this->$method();
    }
    catch(Exception $e)
    {
      # an exception was raised
      $this->count_errors += 1;
      
      printf("\n".Terminal::colorize("An exception was raised", 'RED')." in %s:\n", $method);
      printf("[%d] %s\n\n", $e->getCode(), $e->getMessage());
      printf("Occured at line %d in file %s\n", $e->getLine(), $e->getFile());
      echo $e->getTraceAsString();
      echo "\n";
    }
  }
  
  protected function assert_true($arg, $comment='')
  {
    $this->return_assert($comment, $arg === true, array('got' => $arg, 'expected' => true));
  }
  
  protected function assert_false($arg, $comment='')
  {
    $this->return_assert($comment, $arg === false, array('got' => $arg, 'expected' => false));
  }
  
  protected function assert_null($arg, $comment='')
  {
    $this->return_assert($comment, $arg === null, array('got' => $arg, 'expected' => null));
  }
  
  protected function assert_equal($test, $expect, $comment='')
  {
    if (is_array($test) and !is_hash($test))
    {
      array_sort_recursive($test);
      array_sort_recursive($expect);
    }
    $success = (is_array($test) or is_object($test)) ? ($test == $expect) : ($test === $expect);
    $this->return_assert($comment, $success, array('got' => $test, 'expected' => $expect));
  }
  
  protected function assert_not_equal($test, $expect, $comment='')
  {
    $success = (is_array($test) or is_object($test)) ? ($test != $expect) : ($test !== $expect);
    $this->return_assert($comment, $success, array('got' => $test, 'expected' => $expect));
  }
  
  protected function assert_instance_of($object, $classname, $comment='')
  {
    $this->return_assert($comment, $object instanceof $classname,
      array('got' => get_class($object), 'expected' => $classname));
  }
  
  protected function assert_type($var, $type, $comment='')
  {
    $this->return_assert($comment, gettype($var) === $type,
      array('got' => gettype($var), 'expected' => $type));
  }
  
  protected function assert_match($pattern, $text, $comment='')
  {
    $this->assert_true((bool)preg_match($pattern, $text), $comment);
  }
  
  protected function assert_no_match($pattern, $text, $comment='')
  {
    $this->assert_false((bool)preg_match($pattern, $text), $comment);
  }
  
  private function return_assert($comment, $test, array $vars)
  {
    $this->count_assertions += 1;
    if (!$test)
    {
      switch(gettype($vars['expected']))
      {
        case 'NULL': $str_expected = 'null'; break;
        case 'boolean': $str_expected = $vars['expected'] ? 'true' : 'false'; break;
        default: $str_expected = print_r($vars['expected'], true);
      }
      
      switch(gettype($vars['got']))
      {
        case 'NULL': $str_got = 'null'; break;
        case 'boolean': $str_got = $vars['got'] ? 'true' : 'false'; break;
        default: $str_got = print_r($vars['got'], true);
      }
      
      # failure
      $this->count_failures += 1;
      printf("\n".Terminal::colorize("%s failed:", 'RED')." %s\n", $this->running_test, $comment);
      printf(Terminal::colorize("  expected:", 'BOLD')." %s\n", $str_expected);
      printf(Terminal::colorize("       got:", 'BOLD')." %s\n", $str_got);
    }
  }
}

?>
