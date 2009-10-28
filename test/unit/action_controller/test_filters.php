<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../test/test_app/config/boot.php';

class Test_ActionController_Filters extends Unit_TestCase
{
  function test_before()
  {
    $ctrl = new TestFiltersController('before');
    $ctrl->process(new ActionController_TestRequest(array(':controller' => 'test_filters', ':action' => 'index')),
      new ActionController_AbstractResponse());
    
    $this->assert_equal($ctrl->var_a, 'a');
    $this->assert_null($ctrl->var_c, 'c was never executed, since b returned false');
    $this->assert_null($ctrl->var_index, 'action was never processes, since a before_filter returned false');
  }
  
  function test_after()
  {
    $ctrl = new TestFiltersController('after');
    $ctrl->process(new ActionController_TestRequest(array(':controller' => 'test_filters', ':action' => 'index')),
      new ActionController_AbstractResponse());
    
    $this->assert_equal($ctrl->var_a, 'a');
    $this->assert_equal($ctrl->var_c, 'c', "c was executed, since returning false doesn't affect after filters");
  }
  
  function test_skip()
  {
    $ctrl = new TestFiltersController('skip');
    $ctrl->process(new ActionController_TestRequest(array(':controller' => 'test_filters', ':action' => 'index')),
      new ActionController_AbstractResponse());
    
    $this->assert_equal($ctrl->var_a, 'a');
    $this->assert_equal($ctrl->var_c, 'c', 'c was executed, since b has been skipped');
    $this->assert_true($ctrl->var_index);
    $this->assert_equal($ctrl->var_d, 'd');
    $this->assert_null($ctrl->var_e, 'e has been skipped');
  }
}

new Test_ActionController_Filters();

?>
