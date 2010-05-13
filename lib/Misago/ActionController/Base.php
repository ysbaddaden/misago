<?php
namespace Misago\ActionController;
use Misago\ActionView;

# Actions Controllers are the core logic of your application.
# They process HTTP requests, manipulate your models, pass data
# to views and return HTTP responses.
# 
# A sample controller looks like this:
# 
#   class PostsController extends Misago\ActionController\Base
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
# - <tt>Misago\ActionController\Base</tt>
# - <tt>Misago\ActionController\Caching</tt>
# - <tt>Misago\ActionController\Filters</tt>
# - <tt>Misago\ActionController\Rescue</tt>
# 
# And uses some objects:
# 
# [+$cache+]   <tt>ActiveSupport\Cache\Store</tt>
# [+$flash+]   <tt>Misago\ActionController\Flash</tt>
# [+$request+] <tt>Misago\ActionController\AbstractRequest</tt>
# 
# =Renders
# 
# Actions, by default, render a view from +app/views+ using the name of
# the controller for the folder, the name of the action for the template,
# and the current format (defaults to +html+).
# 
# For instance calling +/posts/index+ will process +PostsController::index()+
# and render +app/views/posts/show.html.tpl+.
# 
# ==Attributes
# 
# Any attribute you define in your action to your controller, will then be
# available to your view. For instance:
#
#   class PostsController extends Misago\ActionController\Base
#   {
#     function show() {
#       $this->post = new Post($this->params[':id']);
#     }
#   }
# 
# Then in your view you can access +$this->post+:
# 
#   <h1><\?= $this->post->title ?\></h1>
# 
abstract class Base extends RequestForgeryProtection
{
  public $helpers = ':all';
  
  # See <tt>Misago\ActionController\Flash</tt>.
  public $flash;
  
  # The currently executed action.
  public $action;
  
  # Merged GET and POST parameters plus PATH parameters from route.
  public $params;
  
  # Template folder containing views.
  public $view_path;
	
	# Request data. See <tt>Misago\ActionController\AbstractRequest</tt>.
  protected $request;
  
  # Response data. See <tt>Misago\ActionController\AbstractResponse</tt>.
  protected $response;
  
  # :private:
  protected $already_rendered = false;
  
  # Default layout to use.
  protected $default_layout;

  # True to skip rendering.
  protected $skip_view = false;
  
  private $rendering_time = 0;
  
  function __construct()
  {
    Rescue::__construct();
    Caching::__construct();
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
  
  # :nodoc:
  function process($request=null, $response=null)
  {
    \Misago\Session::start(isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : null);
    
    $this->request  = ($request  !== null) ? $request  : new CgiRequest();
    $this->response = ($response !== null) ? $response : new AbstractResponse();
    $this->flash    = new Flash();
    
    cfg_set('misago.current_controller', $this);
    
    $this->params = $this->request->parameters();
    $this->action = $this->params[':action'];
    
    $helper_file = str_replace(array('Controller', '\\'), array('Helper', '/'), get_called_class());
    require_once ROOT."/app/helpers/$helper_file.php";
    
    $this->view_path = str_replace('\\', '/', $this->params[':controller']);
    
    if ($this->logger->log_info())
    {
      $this->logger->info(sprintf("Processing %s->%s (for %s at %s) [%s] ",
        get_called_class(), $this->action,
        isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli',
        date('Y-m-d H:i:s'), strtoupper($this->request->method())
      ));
      if (isset($_SESSION)) {
        $this->logger->info("  Session: ".session_id());
      }
      $this->logger->info("  Parameters: ".array_to_string($this->params));
    }
    
    try
    {
      $this->process_before_filters();
      
      if ($this->shall_we_cache_action()) {
        $this->cache_action();
      }
      else {
        $this->process_action();
      }
      
      if ($this->shall_we_cache_page()) {
        $this->cache_page();
      }
      
      $this->process_after_filters();
    }
    catch(FailedFilter $exception) {}
    catch(\Exception $exception) {
      $this->rescue_action($exception);
    }
    
    if ($this->logger->log_info())
    {
      $this->request_time = (microtime(true) - $_SERVER['REQUEST_TIME']);
      
      $this->logger->info(
        sprintf("Completed in %.5f (%.1f reqs/sec) | Rendering: %.5f (%d%%) | %d %s [%s]\n",
        $this->request_time, round(1 / $this->request_time, 2),
        $this->rendering_time, round(100 / $this->request_time * $this->rendering_time),
        $this->response->headers['Status'], $this->response->status(),
        $this->request->url()
      ));
    }
  }
  
  # :nodoc:
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
    $headers = is_array($status_or_headers) ?
      $status_or_headers : array('status' => $status_or_headers);
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
  # By default a particular layout per controller will be rendered, for instance
  # +layouts/products.html.tpl+ for +ProductsController+. If no such layout
  # exists it will fall back on +layouts/default.html.tpl+.
  # 
  #   # use a particular layout:
  #   render(array('action' => 'create', 'layout' => 'admin'));
  #     => renders create.html.tpl inside layouts/admin.html.tpl
  # 
  #   # use a particular format:
  #   render(array('action' => 'index', 'layout' => 'feeds', 'format' => 'rss'));
  #     => renders index.rss.tpl inside layouts/feeds.rss.tpl
  # 
  #   # no layout at all:
  #   render(array('layout' => false));
  # 
  # You may also override the layout you want to use for the whole controller.
  # For instance in the following example +layouts/website.html.tpl+ will be
  # used for all view:
  # 
  #   ProductsController extends Misago\ActionController\Base {
  #     protected $default_layout = 'website';
  #   }
  # 
  # To render the page content in the layout, as well as pass data from the
  # template to the layout, use the +yield()+ method:
  # 
  #   # my_template.html.tpl
  #   <\? $this->yield('page_title') ?\>
  #   
  #   <section id="main"> ... </section>
  #   <aside id="sidebar"> ... </aside>
  # 
  #   # my_layout.html.tpl
  #   <!DOCTYPE html>
  #   <html>
  #   <head>
  #     <title><\?= $this->yield('page_title') ?\></title>
  #   </head>
  #   <body>
  #     <div id="content"><\?= $this->yield('content') ?\></div>
  #   </body>
  #   </html>
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
  #   # => exports $this->products as JSON
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
  # - +action+ -   render the view associated to the current action.
  # - +format+ -   use this particular format.
  # - +json+ -     export resource as JSON.
  # - +layout+ -   use a particular layout (false for no layout).
  # - +locals+ -   pass some variables to be available in template's scope.
  # - +location+ - set HTTP location header.
  # - +status+ -   set HTTP status header.
  # - +template+ - renders a template, from template root path (eg: 'errors/404').
  # - +text+ -     render some text, with no processing --useful for pushing cached html.
  # - +xml+ -      export resource as XML.
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
    
    if (isset($options['location']))
    {
      $this->response->redirect($options['location'],
        isset($options['status']) ? $options['status'] : 302);
    }
    elseif (isset($options['status'])) {
      $this->response->status($options['status']);
    }
    
    if (array_key_exists('xml', $options))
    {
      $this->logger->log_info() && $this->logger->info("Rendering XML");
      $this->response->content_type('application/xml');
      $this->response->body = '<?xml version="1.0"?>'.
        (is_string($options['xml']) ? $options['xml'] : $options['xml']->to_xml());
    }
    elseif (array_key_exists('json', $options))
    {
      $this->logger->log_info() && $this->logger->info("Rendering JSON");
      $this->response->content_type('application/json');
      $this->response->body = is_string($options['json']) ?
        $options['json'] : $options['json']->to_json();
    }
    elseif (array_key_exists('text', $options))
    {
      $this->logger->log_info() && $this->logger->info("Rendering plain text");
      $this->response->body = $options['text'];
    }
    else
    {
      $view = new ActionView\Base($this);
      
      # view
      if (!isset($options['template']))
      {
        $action = isset($options['action']) ? $options['action'] : $this->action;
        $options['template'] = $this->view_path.'/'.$action;
      }
      $this->logger->log_info() && $this->logger->info("Rendering {$options['template']}");
      $content = $view->render($options);
      
      # layout
      $layout = isset($options['layout']) ? $options['layout'] : $this->default_layout;
      
      if ($layout === false) {
        $this->response->body = $content;
      }
      else
      {
        if (empty($layout))
        {
          $layout = $view->template_exists("layouts/{$this->view_path}", $options['format']) ?
            $this->view_path : 'default';
        }
        $options['template'] = "layouts/$layout";
        $view->yield('content', $content);
        
        $this->logger->log_info() && $this->logger->info("Rendering layout {$layout}");
        $this->response->body = $view->render($options);
      }
      
      $this->response->content_type_from_format($options['format']);
    }
    
    $this->rendering_time = (microtime(true) - $rendering_time) / 1000;
    $this->already_rendered = true;
  }
  
  # Renders a view or exports a resource, returned as a string.
  # See <tt>render()</tt> for documentation.
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
  # By default a '302 Moved' header status is sent, but you may customize it:
  # 
  #   redirect_to('/posts/45.xml', 301);  # 301 Found
  #   redirect_to('/posts/45.xml', 201);  # 201 Created
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
    
    $this->logger->log_info() && $this->logger->info("Redirected to $url");
    $this->response->redirect($url, $status);
    $this->already_rendered = true;
  }
  
  # Returns IP of current user (REMOTE_ADDR), trying to bypass proxies
  # (HTTP_X_FORWARDED_FOR & HTTP_CLIENT_IP).
  protected function remote_ip() {
    return $this->request->remote_ip();
  }
  
  # Checks wether the request is made from AJAX.
  protected function is_xml_http_request() {
    return $this->request->is_xml_http_request();
  }
  
  # Shortcut for <tt>is_xml_http_request()</tt>.
  protected function is_xhr() {
    return $this->is_xml_http_request();
  }
  
  # Resolves an URL (reverse routing).
  # 
  # Options:
  # 
  # - +anchor+     - adds an anchor to the URL.
  # - +path_only+  - false to return an absolute URI, true to return the path only (defaults to true).
  # - +protocol+   - overwrites the current protocol.
  # - +host+       - overwrites the current host.
  # - +port+       - overwrites the current port.
  # - +user+       - username for HTTP login.
  # - +password+   - password for HTTP login.
  # 
  # Example:
  # 
  #   url_for(array(':controller' => 'products', ':action' => 'show', ':id' => '67'))
  #   # => http://www.domain.com/products/show/67
  # 
  # Any unknown option that isn't a symbol is added to the query string:
  # 
  #   url_for(array(':controller' => 'products', 'order' => 'asc'))
  #   # => http://www.domain.com/products?order=asc
  # 
  #   url_for(array(':controller' => 'products', ':action' => 'show', ':id' => 13, 'comments' => 'show'))
  #   # => http://www.domain.com/products/show/13?comments=show
  # 
  # You may also add an anchor:
  # 
  #   url_for(array(':controller' => 'about', 'anchor' => 'me'))
  #   # => http://www.domain.com/about#me
  # 
  # <tt>url_for</tt> will fill a few missing path parameters for you:
  # 
  # * -+:controller+ - will always be filled with the current request controller;
  # * -+:action+     - will be filled with the current action if the linked controller is the current one.
  # 
  # For example, if we are in the +stories+ controller and +feed+ action:
  # 
  #   url_for();
  #   # => "/pages/feed"
  # 
  #   url_for(array(':action' => 'index'));
  #   # => "/pages"
  # 
  #   url_for(array(':action' => 'show', ':id' => 2));
  #   # => "/stories/show/2"
  # 
  #   url_for(array(':controller' => 'pages'));
  #   # => "/pages"
  # 
  # Using REST resources, you may pass an <tt>\Misago\ActiveRecord</tt> directly.
  # For instance:
  # 
  #   $product = new Product(43);
  #   $url = url_for($product);    # => http://www.domain.com/products/3
  # 
  function url_for($options=array())
  {
    if ($options instanceof \Misago\ActiveRecord\Record)
    {
      $named_route = \Misago\ActiveSupport\String::underscore(get_class($options)).'_url';
      return $named_route($options);
    }
    
    $default_options = array(
      'anchor'                 => null,
      'path_only'              => true,
      'protocol'               => $this->request->protocol(),
      'host'                   => $this->request->host(),
      'port'                   => $this->request->port(),
      'user'                   => null,
      'password'               => null,
      'skip_relative_url_root' => false,
    );
    
    $mapping = array_diff_key($options, $default_options);
    if (!isset($mapping[':controller'])) {
      $mapping[':controller'] = $this->params[':controller'];
    }
    if (!isset($mapping[':action'])
      and $mapping[':controller'] == $this->params[':controller'])
    {
      $mapping[':action'] = $this->params[':action'];
    }
    
    $options = array_merge($default_options, $options);
    $map     = \Misago\ActionController\Routing\Routes::draw();
    $keys    = array();
    $query_string = array();
    
    foreach($mapping as $k => $v)
    {
      if (is_symbol($k)) {
        $keys[$k] = $v;
      }
      else {
        $query_string[$k] = $v;
      }
    }
    $path = $map->reverse($keys);
    
    if (!empty($query_string))
    {
      ksort($query_string);
      $path .= '?'.http_build_query($query_string);
    }
    if (!empty($options['anchor'])) {
      $path .= '#'.$options['anchor'];
    }
    
    if ($options['path_only']) {
      return $path;
    }
    
    $url  = strpos($options['protocol'], '://') ? $options['protocol'] : $options['protocol'].'://';
    $url .= $options['host'];
    if ($options['port'] != 80 and $options['port'] != 443) {
      $url .= ':'.$options['port'];
    }
    $url .= $this->request->relative_url_root().$path;
    return $url;
    #return cfg_get('action_controller.base_url').$path;
  }
}

?>
