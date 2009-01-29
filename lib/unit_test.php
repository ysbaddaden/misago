<?php
error_reporting(E_ALL);
require_once dirname(__FILE__).'/object.php';

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
    
    printf("Loaded suite %s\n", get_class($this));
    echo "Started\n";
    
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
    
    printf("\nFinished in %f seconds.\n\n", $this->time);
    printf("%d tests, %d assertions, %d failures, %d errors\n",
      $this->count_tests, $this->count_assertions, $this->count_failures, $this->count_errors);
  }
  
  
  protected function assert_true($comment, $arg)
  {
    $this->return_assert($comment, $arg === true);
  }
  
  protected function assert_false($comment, $arg)
  {
    $this->return_assert($comment, $arg === false);
  }
  
  protected function assert_equal($comment, $arg1, $arg2)
  {
    $test = is_array($arg1) ? $this->compare_arrays($arg1, $arg2) : ($arg1 === $arg2);
    $this->return_assert($comment, $test);
  }
  
  protected function assert_not_equal($comment, $arg1, $arg2)
  {
    $test = is_array($arg1) ? !$this->compare_arrays($arg1, $arg2) : ($arg1 !== $arg2);
    $this->return_assert($comment, $test);
  }
  
  private function return_assert($comment, $test)
  {
    $this->count_assertions += 1;
    if (!$test)
    {
      # failure
      $this->count_failures += 1;
      printf("\n%s failed: %s\n", $this->running_test, $comment);
    }
  }
  
  private function compare_arrays($arr1, $arr2)
  {
    if (!is_array($arr1)
      or !is_array($arr2)
      or (count($arr1) != count($arr2)))
    {
      return false;
    }
    
    foreach($arr1 as $k => $v)
    {
      if (!isset($arr2[$k])) {
        return false;
      }
      elseif (is_array($v)
        and (!is_array($arr2[$k]) or $this->compare_arrays($v, $arr2[$k]) === false))
      {
        return false;
      }
      elseif ($arr2[$k] !== $v) {
        return false;
      }
    }
    return true;
  }
}

?>
