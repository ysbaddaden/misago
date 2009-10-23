<?php

# Actions Controllers are the core logic of your application.
# They process HTTP requests, manipulate your models, pass data
# to views and return HTTP responses.
# 
# A sample controller looks like this:
# 
#   class PostsController extends ActionController_Base
#   {
#     function show()
#     {
#       $this->post = new Post($this->params[':id']);
#     }
#     
#     function create()
#     {
#       $this->post = new Post($this->params['account']);
#       if ($this->post->save()) {
#         $this->redirect_to(show_post_path($this->post));
#       }
#     }
#   }
# 
# =Inheritence
# 
# An ActionController is composed of the following classes:
# 
# - +ActionController_Base+
# - +ActionController_Caching+
# - +ActionController_Rescue+
# 
# And uses some classes as attributes:
# 
# - `$cache`: +ActionController_Cache_Store+
# - `$flash`: +ActionController_Flash+
# - `$request`: +ActionController_AbstractRequest+
# 
# =Renders
# 
# Actions, by default, render a view from `app/views` using the name of
# the controller for the folder, the name of the action for the template,
# and the current format (default to `html`).
# 
# For instance calling `/posts/index` will process `PostsController::index()`
# and render `app/views/posts/show.html.tpl`.
# 
# ==Attributes
# 
# Any attribute your define in your action to your controller, will then be
# available to your view. For instance:
#
#   class PostsController extends ActionController_Base
#   {
#     function show() {
#       $this->post = new Post($this->params[':id']);
#     }
#   }
# 
# Then in your view you can access `$this->post`:
# 
#   <h1><\?= $this->post->title ?\></h1>
# 
abstract class ActionController_Base extends ActionController_Caching
{
  public $helpers = ':all';
  
  # See +ActionController_Flash+.
  public $flash;
  
  # The currently executed action.
  public $action;
  
  # Merged GET and POST parameters plus PATH parameters from route.
  public $params;
  
  # Template folder containing views.
  public $view_path;
	
	# Request data. See +ActionController_AbstractRequest+.
  protected $request;
  
  # Response data. See +ActionController_AbstractResponse+.
  # @private
  protected $response;
  
  # @private
  protected $already_rendered = false;

  # True to skip rendering.
  protected $skip_view = false;
  
  private $rendering_time = 0;
  
  function __construct()
  {
    ActionController_Rescue::__construct();
    ActionController_Caching::__construct();
  }
  
  function __get($attr)
  {
    if ($attr == 'format') {
      return $this->request->format();
    }
    return parent::__get($attr);
  }
  
  function __set($attr, $value)
  {
    if ($attr == 'format') {
      return $this->request->format($value);
    }
    return parent::__set($attr, $value);
  }
  
  # @private
  function process($request=null, $response=null)
  {
    $request_time = microtime(true);
    
    @Session::start(isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : null);
    
    $this->request  = ($request  !== null) ? $request  : new ActionController_CgiRequest();
    $this->response = ($response !== null) ? $response : new ActionController_AbstractResponse();
    $this->flash    = new ActionController_Flash();
    
    cfg::set('base_url',
      $this->request->protocol().
      $this->request->host().
      $this->request->port_string().
      $this->request->relative_url_root()
    );
    
    $this->params = $this->request->parameters();
    $this->action = $this->params[':action'];
    
    require_once(ROOT."/app/helpers/{$this->params[':controller']}_helper.php");
    $this->view_path = $this->params[':controller'];
    
    $this->logger->info(sprintf("Processing %s::%s (for %s at %s) [%s] ",
      get_class($this), $this->action, isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli', date('Y-m-d H:i:s'),
      strtoupper($this->request->method())
    ));
    if (isset($_SESSION)) {
      $this->logger->info("  Session: ".session_id());
    }
    $this->logger->info("  Parameters: ".json_encode($this->params));
    
    try
    {
      $this->before_filters();
      
      if ($this->shall_we_cache_action()) {
        $this->cache_action();
      }
      else {
        $this->process_action();
      }
      
      if ($this->shall_we_cache_page()) {
        $this->cache_page();
      }
      
#      $this->after_filters();
    }
    catch(Exception $exception) {
      $this->rescue_action($exception);
    }
    
    $this->request_time = (microtime(true) - $request_time) / 1000;
    
    $this->logger->info(sprintf("Completed in %.5f (%d reqs/sec) | Rendering: %.5f (%d%%) | %d %s [%s]\n",
      $this->request_time, floor(1 / $this->request_time),
      $this->rendering_time, round(100 / $this->request_time * $this->rendering_time),
      $this->response->headers['Status'], $this->response->status(),
      $this->request->url()
    ));
  }
  
  # @private
  protected function process_action()
  {
    $this->{$this->action}();
    if (!$this->already_rendered and !$this->skip_view) {
      $this->render($this->action);
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
  #   # use a particular layout:
  #   render(array('action' => 'create', 'layout' => 'admin'));
  #     => renders create.html.tpl inside admin.html.tpl
  # 
  #   # no layout at all:
  #   render(array('layout' => false));
  #   
  #   # use a particular format:
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
    $rendering_time = microtime(true);
    
    if (!is_array($options)) {
      $options = array('action' => ($options === null) ? $this->action : $options);
    }
    if (!isset($options['format'])) {
      $options['format'] = $this->format;
    }
    
    if (isset($options['location'])) {
      $this->response->redirect($options['location'], isset($options['status']) ? $options['status'] : 302);
    }
    elseif (isset($options['status'])) {
      $this->response->status($options['status']);
    }
    
    if (array_key_exists('xml', $options))
    {
      $this->logger->debug("Rendering XML");
      $this->response->content_type('application/xml');
      $this->response->body = '<?xml version="1.0"?>'.
        (is_string($options['xml']) ? $options['xml'] : $options['xml']->to_xml());
      
    }
    elseif (array_key_exists('json', $options))
    {
      $this->logger->debug("Rendering JSON");
      $this->response->content_type('application/json');
      $this->response->body = is_string($options['json']) ? $options['json'] : $options['json']->to_json();
    }
    elseif (array_key_exists('text', $options))
    {
      $this->logger->debug("Rendering plain text");
      $this->response->body = $options['text'];
    }
    else
    {
      if (!isset($options['template']))
      {
        $action = isset($options['action']) ? $options['action'] : $this->action;
        $options['template'] = $this->view_path.'/'.$action;
      }
      $this->logger->debug("Rendering {$options['template']}");
      
      $view = new ActionView_Base($this);
      $this->response->content_type_from_format($options['format']);
      $this->response->body = $view->render($options);
    }
    
    $this->rendering_time = (microtime(true) - $rendering_time) / 1000;
    $this->already_rendered = true;
  }
  
  # Renders a view or exports a resource, returned as a string.
  # See +render()+ for documentation.
  function render_to_string($options=null)
  {
    $this->render($options);
    return $this->response->body;
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
        $url = $this->request->relative_url_root().$url;
      }
    }
    
    $this->logger->debug("Redirected to $url");
    
#    if (DEBUG < 2) {
      $this->response->redirect($url, $status);
#    }
#    else
#    {
#      $status_text = $this->response->status($status);
#      echo "<p style=\"text-align:center\"><a href=\"$url\" style=\"font-weight:bold\">Redirect to: $url</a> [status: $status $status_text]</p>";
#    }
    exit;
  }
  
  # Returns current user IP (REMOTE_ADDR), trying to bypass proxies (HTTP_X_FORWARDED_FOR & HTTP_CLIENT_IP).
  protected function remote_ip()
  {
    return $this->request->remote_ip();
  }
  
  # Checks wether the request is made from AJAX.
  protected function is_xml_http_request()
  {
    return $this->request->is_xml_http_request();
  }
  
  
  protected function before_filters()
  {
    
  }
  
#  protected function after_filters($body)
#  {
#    return $body;
#  }
}

?>
