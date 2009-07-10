<?php
/**
 * 
 */
abstract class ActionController_Base extends Object
{
  public    $name;
  public    $action;
  public    $params;
	
  protected $mapping = array();
  protected $already_rendered = false;
  protected $skip_view = false;
  
  public    $helpers   = ':all';
  
  function __construct()
  {
    $this->name   = get_class($this);
    $this->params = array_merge($_GET, $_POST);
    
    if (get_magic_quotes_gpc()) {
      sanitize_magic_quotes($this->params);
    }
  }
  
  function execute($mapping)
  {
    if (!is_array($mapping))
    {
      $this->action  = $mapping;
      $this->mapping = array(
        ':method' => 'GET',
        ':controller' => String::underscore($this->name),
        ':action' => $mapping,
        ':format' => 'html',
      );
    }
    else
    {
      $this->mapping =& $mapping;
      $this->action  = $this->mapping[':action'];
      
      $params = array_diff_key($this->mapping, array(
        ':controller' => '',
        ':action' => '',
        ':method' => '',
        ':format' => '',
      ));
      $this->params = array_merge($this->params, $params);
      
      $this->format = empty($this->mapping[':format']) ?
        'html' : $this->mapping[':format'];
    }
    
    if (DEBUG == 1)
    {
      $time = microtime(true);
      $date = date('Y-m-d H:i:s T');
      
      misago_log(sprintf("\n\nHTTP REQUEST: {$this->mapping[':method']} ".
        get_class($this)."::".$this->action." [%s]\n", $date));
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
  
  # Renders a view or exports a resource.
  # 
  # Renders a view:
  # 
  #   $this->render();
  #   $this->render('edit');
  #   $this->render(array('action' => 'edit', 'format' => 'xml'));
  # 
  # Exports a resource in a particular file format:
  # 
  #   $this->render(array('xml' => $this->user));
  #   $this->render(array('json' => $this->products));
  # 
  # Available options:
  # 
  # - status: HTTP status to send.
  # - format: use this particular format.
  # - action: render the view associated to this action.
  # - layout: use a particular layout.
  # - locals: pass some variables to be available in template's scope.
  # - text:   render some text, with no processing --useful for pushing cached html.
  # 
  function render($options=null)
  {
    if (!is_array($options)) {
      $options = array('action' => ($options === null) ? $this->action : $options);
    }
    if (!isset($options['format'])) {
      $options['format'] = empty($this->format) ? 'html' : $this->format;
    }
    
    if (isset($options['status'])) {
      HTTP::status($options['status']);
    }
    
    if (array_key_exists('xml', $options))
    {
      HTTP::content_type('xml');
      echo '<?xml version="1.0"?>';
      echo is_string($options['xml']) ? $options['xml'] : $options['xml']->to_xml();
    }
    elseif (array_key_exists('json', $options))
    {
      HTTP::content_type('json');
      echo is_string($options['json']) ? $options['json'] : $options['json']->to_json();
    }
    elseif (array_key_exists('text', $options)) {
      echo $options['text'];
    }
    else
    {
      if ($options['format'] != 'html') {
        HTTP::content_type($options['format']);
      }
      if (!isset($options['action'])) {
        $options['action'] = $this->action;
      }
      $view = new ActionView_Base($this);
      echo $view->render($options);
    }
    
    $this->already_rendered = true;
  }
  
  # Renders a view or exports a resource, returned as a string.
  function render_string($options=null)
  {
    ob_start();
    $this->render($options);
    return ob_get_clean();
  }
  
  protected function before_filters() {}
#  protected function after_filters()  {}
  
  # Redirects to another URL, passing a text message to it.
  # 
  # Example:
  # 
  #   $this->flash("Post has been published.", show_post_path($this->post->id), 201);
  # 
  # And in your view:
  # 
  #   <?= Session::flash() ?\>
  #
  protected function flash($message, $url, $code=302)
  {
    Session::flash($message);
    HTTP::redirect($url, $code);
  }
}

?>
