<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/lib/unit_test.php";
require_once "$location/test/test_app/config/boot.php";

# cleanup
`cd $location/test/test_app; MISAGO_ENV=test script/db/drop`;
`cd $location/test/test_app; MISAGO_ENV=test script/db/create`;
`cd $location/test/test_app; MISAGO_ENV=test script/db/migrate`;

# fake objects
class Post extends ActiveRecord_Base {}

class Product extends ActiveRecord_Base
{
  
}

class Test_ActiveRecord_Base extends Unit_Test
{
  function test_no_such_table()
  {
    try
    {
      new Post();
      $test = false;
    }
    catch(ActiveRecord_StatementInvalid $e) {
      $test = true;
    }
    $this->assert_true("Must throw an ActiveRecord_StatementInvalid exception (no such table)", $test);
  }
  
  function test_new()
  {
    $product = new Product();
    $this->assert_equal('Product must be an instance of Product', get_class($product), 'Product');
    $this->assert_instance_of('Product must be an instance of ActiveRecord_Base',   $product, 'ActiveRecord_Base');
    $this->assert_instance_of('Product must be an instance of ActiveRecord_Record', $product, 'ActiveRecord_Record');
  }
  
  function test_create()
  {
    $product = new Product();
    $product->name  = 'azerty';
    $product->price = 9.95;
    $test = $product->create();
    
    $this->assert_true("Must have created product 'azerty'", $test);
    $this->assert_true("Must have set product.id", isset($product->id));
    $this->assert_type("product.id must be an integer (typecast)", $product->id, 'integer');
  }
}

new Test_ActiveRecord_Base();

?>
