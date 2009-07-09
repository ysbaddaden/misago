<?php

# Renders templates.
# 
# See ActionController_base::render() for actual help, since
# you shall not need to use this object directly, except for
# a few methods, like `yield()`.
# 
class ActionView_Base extends Object
{
  public    $view_path;
  public    $view_format;
  protected $controller;
  
  protected $yields = array(
    'title' => ''
  );
  
  function __construct($controller=null)
  {
    if ($controller !== null)
    {
      $this->controller = $controller;
      $this->view_path = String::underscore(str_replace('Controller', '', get_class($this->controller)));
      $helpers         = $this->controller->helpers;
    }
    
    if (!isset($helpers) or $helpers == ':all')
    {
      $helpers = apc_fetch(TMP.'/list_of_helpers', &$success);
      
      if ($success === false)
      {
        $helpers = array();
        $this->_find_helpers($helpers, MISAGO.'/lib/action_view/helpers/');
        $this->_find_helpers($helpers, ROOT.'/app/helpers/');
        apc_store(TMP.'/list_of_helpers', $helpers, strtotime('+24 hours'));
      }
    }
    
    if (is_array($helpers))
    {
      foreach($helpers as $helper) {
        require_once "{$helper}_helper.php";
      }
    }
  }
  
  # @private
  protected function _find_helpers(&$helpers, $path)
  {
    $dh = opendir($path);
    if ($dh)
    {
      while(($file = readdir($dh)) !== false)
      {
        if (is_file($path.$file)) {
          $helpers[] = str_replace('_helper.php', '', $file);
        }
      }
      closedir($dh);
    }
  }
  
  # Renders a template (view, layout or partial).
  # 
  # = Generic option(s):
  # 
  # - format: which format to use? defaults to 'html'
  #
  # = Render a view:
  # 
  #   render(array('action' => 'index'));
  # 
  # This will render the view 'index.html.tpl'.
  # 
  # = Render a view inside a layout:
  # 
  #   render(array('action' => 'index', 'layout' => 'products'));
  # 
  # This will render the view 'index.html.tpl' inside the layout 'products.html.tpl'.
  # 
  # = Render a partial:
  # 
  #   render(array('partial' => 'form'));
  #   render(array('partial' => 'form', 'locals' => array('f' => $f)));
  # 
  # This will render the partial '_form.html.tpl'.
  # 
  # = Render a collection of partials:
  # 
  #   render(array('partial' => 'product', 'collection' => $products));
  # 
  # The collection must be an array or an iterable object.
  # This will create the following local vars: $product and $product_counter.
  # 
  # = Rendering shared partials
  # 
  # You may share partials between controllers.
  # 
  #   render(array('partial' => 'posts/post'));
  #   render(array('partial' => 'posts/post', 'collection' => $posts));
  # 
  # This will render the partial 'posts/_post.html.tpl', regardless of
  # which controller this is being called from.
  # 
  function render($options)
  {
    # locals
    if (!empty($options['locals']))
    {
      foreach($options['locals'] as $k => $v) {
        $$k = $v;
      }
    }
    
    # view (+layout)
    if (isset($options['action']))
    {
      $this->view_format = isset($options['format']) ? $options['format'] : 'html';
      $__view_file = "{$this->view_path}/{$options['action']}.{$this->view_format}.tpl";
      
      if (file_exists(ROOT."/app/views/{$__view_file}"))
      {
        if ($this->controller !== null) {
          $this->copy_controller_vars();
        }
        
        # view
        ob_start();
        include ROOT."/app/views/{$__view_file}";
        $this->yield('content', ob_get_clean());
        
        # layout
        if (isset($options['layout']))
        {
          $__layout_file = "{$options['layout']}.{$this->view_format}.tpl";
          if (file_exists(ROOT."/app/views/layouts/{$__layout_file}"))
          {
            ob_start();
            include ROOT."/app/views/layouts/{$__layout_file}";
            return ob_get_clean();
          }
          else {
            throw new MisagoException("Layout template not found: '{$options['layout']}'", 404);
          }
        }
        else
        {
          $__layout_file = "{$this->view_path}.{$this->view_format}.tpl";
          if (file_exists(ROOT."/app/views/layouts/{$__layout_file}"))
          {
            ob_start();
            include ROOT."/app/views/layouts/{$__layout_file}";
            return ob_get_clean();
          }
          elseif (file_exists(ROOT."/app/views/layouts/default.{$this->view_format}.tpl"))
          {
            ob_start();
            include ROOT."/app/views/layouts/default.{$this->view_format}.tpl";
            return ob_get_clean();
          }
        }
        return $this->yield('content');
      }
      
      throw new MisagoException("View template not found: '{$__view_file}'", 404);
    }
    
    # partial (or collection of partials)
    elseif (isset($options['partial']))
    {
      if (strpos($options['partial'], '/'))
      {
        $__partial_file = explode('/', $options['partial'], 2);
        $__partial_file = $__partial_file[0].'/_'.$__partial_file[1];
      }
      else {
        $__partial_file = $this->view_path.'/_'.$options['partial'];
      }
      $__partial_file .= ".{$this->view_format}.tpl";
      
      if (file_exists(ROOT."/app/views/{$__partial_file}"))
      {
        ob_start();
        
        if (!isset($options['collection']))
        {
          # partial
          include ROOT."/app/views/{$__partial_file}";
        }
        else
        {
          # collection of partials
          $__partial_var      = $options['partial'];
          $__partial_counter  = "{$__partial_var}_counter";
          $$__partial_counter = 0;
          
          foreach($options['collection'] as $__partial_value)
          {
            $$__partial_var      = $__partial_value;
            $$__partial_counter += 1;
            include ROOT."/app/views/{$__partial_file}";
          }
        }
        return ob_get_clean();
      }
      
      throw new MisagoException("Partial template not found: '{$__partial_file}'", 404);
    }
  }
  
  # Passes data from a template to another.
  # For instance from view to layout, partial to view, etc.
  function yield($name, $content=null)
  {
    if ($content === null) {
      return $this->yields[$name];
    }
    $this->yields[$name] = $content;
  }
  
  # Copies public vars from controller, not overriding view's own class vars.
  # @private
  protected function copy_controller_vars()
  {
    $this->params =& $this->controller->params;
    
    $controller_vars = get_object_vars($this->controller);
    $view_vars       = get_class_vars(get_class($this));
    
    $vars = array_diff_key($controller_vars, $view_vars);
    foreach($vars as $k => $v) {
      $this->$k = $v;
    }
  }
}
?>
