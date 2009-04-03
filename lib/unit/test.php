<?php
error_reporting(E_ALL);

# @package Unit
class Unit_Test
{
  private $count_tests    = 0;
  private $count_success  = 0;
  private $count_failures = 0;
  private $count_errors   = 0;
  private $time;
  private $running_test;
  
  public function __construct()
  {
    $methods = get_class_methods($this);
    
    printf("\nLoaded suite %s\n", get_class($this));
#    echo "Started\n";
    
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
      
      try
      {
        # runs test
        $this->$method();
      }
      catch(Exception $e)
      {
        # an exception was raised
        $this->count_errors += 1;
        
        printf("\nAn exception was raised in %s:\n", $method);
        printf("[%d] %s\n\n", $e->getCode(), $e->getMessage());
        printf("Occured at line %d in file %s\n", $e->getLine(), $e->getFile());
        echo $e->getTraceAsString();
        echo "\n";
      }
    }
    
    # finished
    $this->time = microtime(true) - $this->time;
    
    printf("\nFinished in %f seconds.\n%d tests, %d assertions, %d failures, %d errors\n",
      $this->time, $this->count_tests, $this->count_assertions, $this->count_failures, $this->count_errors);
  }
  
  protected function assert_true($comment, $arg)
  {
    $this->return_assert($comment, $arg === true, array('got' => $arg, 'expected' => true));
  }
  
  protected function assert_false($comment, $arg)
  {
    $this->return_assert($comment, $arg === false, array('got' => $arg, 'expected' => false));
  }
  
  protected function assert_equal($comment, $test, $expect)
  {
    if (is_array($test) and !is_hash($test))
    {
      array_sort_recursive($test);
      array_sort_recursive($expect);
    }
    $success = (is_array($test) or is_object($test)) ? ($test == $expect) : ($test === $expect);
    $this->return_assert($comment, $success, array('got' => $test, 'expected' => $expect));
  }
  
  protected function assert_not_equal($comment, $test, $expect)
  {
    $success = (is_array($test) or is_object($test)) ? ($test != $expect) : ($test !== $expect);
    $this->return_assert($comment, $success, array('got' => $test, 'expected' => $expect));
  }
  
  protected function assert_instance_of($comment, $object, $classname)
  {
    $this->return_assert($comment, $object instanceof $classname,
      array('got' => get_class($object), 'expected' => $classname));
  }
  
  protected function assert_type($comment, $var, $type)
  {
    $this->return_assert($comment, gettype($var) === $type,
      array('got' => gettype($var), 'expected' => $type));
  }
  
  
  private function return_assert($comment, $test, array $vars)
  {
    $this->count_assertions += 1;
    if (!$test)
    {
      # failure
      $this->count_failures += 1;
      printf("\n%s failed: %s\n", $this->running_test, $comment);
      printf("  expected: %s\n", print_r($vars['expected'], true));
      printf("       got: %s\n", print_r($vars['got'], true));
    }
  }
}

?>
