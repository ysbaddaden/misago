<?php
require_once __DIR__.'/../../unit.php';
require_once 'TestFiltersController.php';
use Misago\ActionController;

class Test_ActionController_Filters extends Misago\Unit\TestCase
{
  function test_before()
  {
    $ctrl = new TestBeforeFiltersController();
    $ctrl->process(new ActionController\TestRequest(array(':controller' => 'test_filters', ':action' => 'index')), new ActionController\AbstractResponse());
    $this->assert_equal($ctrl->var_a, 'a');
    $this->assert_null($ctrl->var_c, 'c was never executed, since b returned false');
    $this->assert_null($ctrl->var_action, 'action was never processes, since a before_filter returned false');
    
    $ctrl = new TestBeforeFiltersController();
    $ctrl->process(new ActionController\TestRequest(array(':controller' => 'test_filters', ':action' => 'show')), new ActionController\AbstractResponse());
    $this->assert_null($ctrl->var_a, "no 'a' filter for show action");
  }
  
  function test_after()
  {
    $ctrl = new TestAfterFiltersController();
    $ctrl->process(new ActionController\TestRequest(array(':controller' => 'test_filters', ':action' => 'index')), new ActionController\AbstractResponse());
    $this->assert_equal($ctrl->var_a, 'a');
    $this->assert_equal($ctrl->var_c, 'c', "c was executed, since returning false doesn't affect after filters");
    
    $ctrl = new TestAfterFiltersController();
    $ctrl->process(new ActionController\TestRequest(array(':controller' => 'test_filters', ':action' => 'show')), new ActionController\AbstractResponse());
    $this->assert_null($ctrl->var_a, "'a' filter is only for index action");
  }
  
  function test_skip()
  {
    $ctrl = new TestSkipFiltersController();
    $ctrl->process(new ActionController\TestRequest(array(':controller' => 'test_filters', ':action' => 'index')),
      new ActionController\AbstractResponse());
    
    $this->assert_equal($ctrl->var_a, 'a');
    $this->assert_equal($ctrl->var_c, 'c', 'c was executed, since b has been skipped');
    $this->assert_equal($ctrl->var_action, 'index');
    $this->assert_equal($ctrl->var_d, 'd');
    $this->assert_null($ctrl->var_e, 'e has been skipped');
  }
}

?>
