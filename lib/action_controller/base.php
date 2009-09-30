<?php

# TODO: after_filters(), is_xml_http_request().
# TODO: ActiveRecord_AbstractResponse.
abstract class ActionController_Base extends Object
{
  public $helpers = ':all';
  
  public $name;
  public $action;
  public $params;
  public $view_path;
  public $flash;
	
  protected $mapping          = array();
  protected $already_rendered = false;
  protected $skip_view        = false;
  
  function __construct()
  {
    $this->name      = get_class($this);
    $this->params    = array_merge($_GET, $_POST);
    $this->view_path = String::underscore(str_replace('Controller', '', get_class($this)));
    
    if (get_magic_quotes_gpc()) {
      sanitize_magic_quotes($this->params);
    }
    
    $this->flash    = new ActionController_Flash();
    $this->response = new ActionController_AbstractResponse();
  }
  
  # @private
  function execute($mapping)
  {
    if (!is_array($mapping))
    {
      $this->action  = $mapping;
      $this->mapping = array(
        ':method' => 'GET',
        ':controller' => str_replace('_controller', '', String::underscore($this->name)),
        ':action' => $mapping,
        ':format' => 'html',
      );
    }
    else
    {
      $this->mapping =& $mapping;
      $this->action  = $this->mapping[':action'];
      $this->format  = empty($this->mapping[':format']) ? 'html' : $this->mapping[':format'];
      
      $params = array_diff_key($this->mapping, array(
        ':controller' => '',
        ':action' => '',
        ':method' => '',
        ':format' => '',
      ));
      $this->params = array_merge($this->params, $params);
    }
    
    if (DEBUG == 1)
    {
      $time = microtime(true);
      $date = date('Y-m-d H:i:s T');
      
      misago_log(sprintf("\n\nHTTP REQUEST: {$this->mapping[':method']} ".
        get_class($this)."::".$this->action." [%s]\n", $date));
    }
    
    # some helpers
    require_once(ROOT.'/app/helpers/application_helper.php');
    require_once(ROOT."/app/helpers/{$this->mapping[':controller']}_helper.php");
    
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
  
  # Returns a response that has no content (merely headers).
  # 
  # Examples:
  # 
  #   head(array('status' => 201, 'location' => show_post_url($this->post));
  #   head(403);
  # 
  function head($status_or_headers)
  {
    $headers = is_array($status_or_headers) ? $status_or_headers : array('status' => $status_or_headers);
    $this->response->headers = array_merge($this->response->headers, $headers);
    $this->response->send();
    $this->already_rendered = true;
  }
  
  # Renders a view or exports a resource.
  # 
  # =Render a view:
  # 
  # Renders the view associated to an action, using the current request's
  # format (html by default) or the one specified.
  # 
  #   # renders the current action:
  #   render();
  # 
  #   # renders a specific action:
  #   render('edit'); => edit.html.tpl
  #   
  #   # renders a specific action using a specific format:
  #   render(array('action' => 'edit', 'format' => 'xml')); => edit.xml.tpl
  # 
  # =Render a template
  # 
  # Instead of rendering an action, you may specify the template directly.
  # 
  #   render('posts/show');
  # 
  # =Layouts
  # 
  # By default controller's layout (eg: layouts/posts.html.tpl) is used,
  # and falls back to the generic default layout (layouts/default.html.tpl).
  # 
  #   # uses a particular layout:
  #   render(array('action' => 'create', 'layout' => 'admin'));
  #     => renders create.html.tpl inside admin.html.tpl
  # 
  #   # no layout at all:
  #   render(array('layout' => false));
  #   
  #   # using a particular format:
  #   render(array('action' => 'index', 'layout' => 'feeds', 'format' => 'rss'));
  #     => renders index.rss.tpl inside feeds.rss.tpl
  # 
  # =Export a resource in a particular file format:
  # 
  # Being able to talk in XML or JSON to your server is great. Being able
  # to easily export your data to these formats is even better.
  # 
  #   render(array('xml' => $this->user));
  #   # => exports $this->user as XML
  #   
  #   render(array('json' => $this->products));
  #   # => exports $this->user as JSON
  # 
  # Note: no layout is rendered when using XML or JSON exports.
  # 
  # =Headers
  # 
  # You may set the status and location headers:
  # 
  #   render(array('json' => $this->products->errors, 'status' => '412'));
  #   render(array('xml'  => $this->product, 'status' => '201',
  #     'location' => show_product_url($this->product->id)));
  # 
  # =Available options:
  # 
  # - action: render the view associated to the current action.
  # - format: use this particular format.
  # - json: export resource as JSON.
  # - layout: use a particular layout (false for no layout).
  # - locals: pass some variables to be available in template's scope.
  # - location: set HTTP location header.
  # - status: set HTTP status header.
  # - template: renders a template, from template root path (eg: 'errors/404').
  # - text: render some text, with no processing --useful for pushing cached html.
  # - xml: export resource as XML.
  # 
  # TODO: render(:partial => 'xx/yy') => app/views/xx/_yy.html.tpl (no layout)
  # TODO: render(:file => '/xx/yy/zz.html.tpl') => /xx/yy/zz.html.tpl (no layout)
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
      $this->response->redirect($options['location'], isset($options['status']) ? $options['status'] : 302);
    }
    elseif (isset($options['status'])) {
      $this->response->status($options['status']);
    }
    
    if (array_key_exists('xml', $options))
    {
      $this->response->content_type('application/xml');
      $this->response->body = '<?xml version="1.0"?>'.
        (is_string($options['xml']) ? $options['xml'] : $options['xml']->to_xml());
    }
    elseif (array_key_exists('json', $options))
    {
      $this->response->content_type('application/json');
      $this->response->body = is_string($options['json']) ? $options['json'] : $options['json']->to_json();
    }
    elseif (array_key_exists('text', $options)) {
      $this->response->body = $options['text'];
    }
    else
    {
      if (!isset($options['template']))
      {
        $action = isset($options['action']) ? $options['action'] : $this->action;
        $options['template'] = $this->view_path.'/'.$action;
      }
      $view = new ActionView_Base($this);
      
      $this->response->content_type_from_format($options['format']);
      $this->response->body = $view->render($options);
    }
    
    $this->response->send();
    $this->already_rendered = true;
  }
  
  # Renders a view or exports a resource, returned as a string.
  # See render() for documentation.
  function render_to_string($options=null)
  {
    ob_start();
    $this->render($options);
    return ob_get_clean();
  }
  
  # Redirects to another URL.
  # 
  #   redirect_to('/path');
  #   redirect_to(articles_path());
  #   redirect_to(show_article_url($account->id));
  #   redirect_to(array(':controller' => 'contact', ':action' => 'thanks'));
  # 
  # By default a '302 Moved' header status in sent, but you may customize it:
  # 
  #   redirect_to('/posts/45.xml', 301); # found
  #   redirect_to('/posts/45.xml', 201); # created
  protected function redirect_to($url, $status=302)
  {
    if (is_array($url))
    {
      $url['path_only'] = false;
      $url = url_for($url);
    }
    else
    {
      $url = (string)$url;
      if (!strpos($url, '://')) {
        $url = cfg::get('base_path').$url;
      }
    }
    
    if (DEBUG < 2) {
      $this->response->redirect($url, $status);
    }
    else
    {
      $status_text = $this->response->status($status);
      echo "<p style=\"text-align:center\"><a href=\"$url\" style=\"font-weight:bold\">Redirect to: $url</a> [status: $status $status_text]</p>";
    }
    exit;
  }
  
  # Returns current user IP (REMOTE_ADDR), trying to bypass proxies (HTTP_X_FORWARDED_FOR & HTTP_CLIENT_IP).
  protected function remote_ip()
  {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
      return $_SERVER['HTTP_CLIENT_IP'];
    }
    return $_SERVER['REMOTE_ADDR'];
  }
  
  # Sends a Cache-Control header for HTTP caching.
  # 
  # Defaults to private, telling proxies not to cache anything,
  # which allows for some privacy of content.
  # 
  # Examples:
  # 
  #   expires_in(3600)
  #   expires_in('+1 hour', array('private' => false))
  # 
  protected function expires_in($seconds, $options=array())
  {
    $cache_control = array(
      'max-age' => is_integer($seconds) ? $seconds : strtotime($seconds),
      'private' => true,
    );
    foreach($options as $k => $v)
    {
      if (!$v) {
        continue;
      }
      $cache_control[] = ($v === true) ? $k : "$k=$v";
    }
    $this->response->headers['Cache-Control'] = implode(', ', $cache_control);
  }
  
  # Sends a Cache-Control header with 'no-cache' to disallow or
  # cancel HTTP caching of current request.
  protected function expires_now()
  {
    $this->response->headers['Cache-Control'] = 'no-cache, no-store';
  }
  
  protected function before_filters() {}
#  protected function after_filters()  {}
}

?>
