<?php

# TODO: after_filters().
abstract class ActionController_Base extends Object
{
  public $helpers = ':all';
  
  public $name;
  public $action;
  public $params;
  public $flash;
	
  protected $mapping          = array();
  protected $already_rendered = false;
  protected $skip_view        = false;
  
  
  function __construct()
  {
    $this->name   = get_class($this);
    $this->params = array_merge($_GET, $_POST);
    
    if (get_magic_quotes_gpc()) {
      sanitize_magic_quotes($this->params);
    }
    
    $this->flash = new ActionController_Flash();
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
  # Render a view:
  # 
  #   $this->render();
  #   $this->render('edit');
  #   # => renders edit.html.tpl
  #   
  #   $this->render(array('action' => 'edit', 'format' => 'xml'));
  #   # => renders edit.xml.tpl
  # 
  # Render a view inside a layout:
  # 
  #   $this->render(array('action' => 'create', 'layout' => 'admin'));
  #   # => renders create.html.tpl inside admin.html.tpl
  # 
  # Export a resource in a particular file format:
  # 
  #   $this->render(array('xml' => $this->user));
  #   # => exports $this->user as XML
  #   
  #   $this->render(array('json' => $this->products));
  #   # => exports $this->user as JSON
  # 
  # Advanced uses (eg. webservices):
  # 
  #   $this->render(array('xml' => $this->product, 'status' => '201',
  #     'location' => show_product_url($this->product->id)));
  #   $this->render(array('json' => $this->products->errors, 'status' => '412'));
  # 
  # Available options:
  # 
  # - action: render the view associated to this action.
  # - format: use this particular format.
  # - json: export resource as JSON.
  # - layout: use a particular layout.
  # - locals: pass some variables to be available in template's scope.
  # - location: set HTTP location header.
  # - status: set HTTP status header.
  # - text: render some text, with no processing --useful for pushing cached html.
  # - xml: export resource as XML.
  # 
  function render($options=null)
  {
    if (!is_array($options)) {
      $options = array('action' => ($options === null) ? $this->action : $options);
    }
    if (!isset($options['format'])) {
      $options['format'] = empty($this->format) ? 'html' : $this->format;
    }
    
    if (isset($options['location'])) {
      HTTP::redirect($options['location']);
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
  
  # Redirects to another page.
  # 
  #   redirect_to('/path');
  #   redirect_to(articles_path());
  #   redirect_to(show_article_url($account->id));
  # 
  # By default a '302 Moved' header status in sent, but you may customize it:
  # 
  #   redirect_to('/posts/45.xml', 301); # found
  #   redirect_to('/posts/45.xml', 201); # created
  # 
  protected function redirect_to($options, $status=302)
  {
    if (is_array($options))
    {
      $options['path_only'] = false;
      $url = url_for($options);
    }
    else
    {
      $url = (string)$options;
      if (!strpos($url, '://')) {
        $url = cfg::get('base_path').$url;
      }
    }
    HTTP::redirect($url, 302);
  }
    
  protected function before_filters() {}
#  protected function after_filters()  {}
}

?>
