<?php

abstract class ActionController_Base extends Object
{
  public    $name;
  public    $action;
  public    $params;

  protected $mapping;
  protected $already_rendered = false;
  protected $skip_view = false;
  
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
    
    $this->{$this->action}();
    
    if (!$this->already_rendered
      and !$this->skip_view)
    {
      $this->render($this->action);
    }
  }
  
  function render($action=null, array $options=array())
  {
    $options['action'] = ($action === null) ? $this->action : $action;
    
    if (!isset($options['format'])) {
      $options['format'] = $this->mapping[':format'];
    }
    
    $view = new ActionView_Base($this);
    echo $view->render($options);
    
    $this->already_rendered = true;
  }
}

?>
