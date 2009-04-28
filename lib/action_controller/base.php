<?php
/**
 * 
 * @package ActionController
 */
abstract class ActionController_Base extends Object
{
  public    $name;
  public    $action;
  public    $params;

  protected $mapping;
  protected $already_rendered = false;
  protected $skip_view = false;
  
  protected $helpers   = ':all';
  
  function __construct(array $mapping=null)
  {
    $this->name   = get_class($this);
    $this->params = array_merge($_GET, $_POST);
    
    if (get_magic_quotes_gpc()) {
      sanitize_magic_quotes($this->params);
    }
    
    if ($mapping !== null)
    {
      $this->mapping =& $mapping;
      
      $params = array_diff_key($this->mapping, array(
        ':controller' => '',
        ':action' => '',
        ':method' => '',
        ':format' => '',
      ));
      $this->params = array_merge($this->params, $params);
    }
  }
  
  function execute($action=null)
  {
    $this->action = ($action === null) ? $this->mapping[':action'] : $action;
    
    if (DEBUG == 1)
    {
      $time = microtime(true);
      $date = date('Y-m-d H:i:s T');
      misago_log(sprintf("\n\nHTTP REQUEST: {$this->mapping[':method']} {$_SERVER['REQUEST_URI']} [%s]\n", $date));
    }
    
    $this->before_filters();
    $this->{$this->action}();
    
    if (!$this->already_rendered
      and !$this->skip_view)
    {
      $this->render($this->action);
    }
    
#    $this->after_filters();
    
    if (DEBUG == 1)
    {
      $time = microtime(true) - $time;
      misago_log(sprintf("End of HTTP request ; Elapsed time: %.02fms", $time));
    }
  }
  
  function render($action=null, array $options=array())
  {
    $options['action'] = ($action === null) ? $this->action : $action;
    
    if (!isset($options['format']))
    {
      $options['format'] = empty($this->mapping[':format']) ?
        'html' : $this->mapping[':format'];
    }
    
    $view = new ActionView_Base($this);
    echo $view->render($options);
    
    $this->already_rendered = true;
  }
  
  protected function before_filters() {}
#  protected function after_filters()  {}
}

?>
