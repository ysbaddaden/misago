<?php
/**
 * @package ActionView
 */
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
    if ($controller instanceof ActionController_Base)
    {
      $this->controller = $controller;
      $this->view_path  = String::underscore(str_replace('Controller', '', $this->controller->name));
    }
    
    # loads helpers
    require_once 'action_view/helpers/html.php';
    require_once 'action_view/helpers/form.php';
    require_once 'action_view/helpers/form_helper.php';
  }
  
  # Renders a template (view, layout or partial).
  # 
  # Generic option(s):
  #   format: which format to use? defaults to 'html'
  #
  # Render a view:
  #   render(array('action' => 'index'));
  # 
  # Render a view inside a layout:
  #   render(array('action' => 'index', 'layout' => 'products'));
  # 
  # Render a partial:
  #   render(array('partial' => 'form'));
  #   render(array('partial' => 'form', 'locals' => array('f' => $f)));
  # 
  # Render a collection of partials:
  #   render(array('partial' => 'product', 'collection' => $products));
  #   
  #   The collection must be an array or an iterable object.
  #   It creates the following local vars: $product and $product_counter.
  #
  function render($options)
  {
    # view (+layout)
    if (isset($options['action']))
    {
      $this->view_format = isset($options['format']) ? $options['format'] : 'html';
      $__view_file = "{$this->view_path}/{$options['action']}.{$this->view_format}.tpl";
      
      if (file_exists(ROOT."/app/views/{$__view_file}"))
      {
        $this->copy_controller_vars();
        
        # view
        ob_start();
        include ROOT."/app/views/{$__view_file}";
        $this->yield('content', ob_get_clean());
        
        # layout
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
        
        return $this->yield('content');
      }
      
      throw new MisagoException("View template not found: '{$__view_file}'", 404);
    }
    
    # partial or collection of partials
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
        # locals
        if (!empty($options['locals']))
        {
          foreach($options['locals'] as $k => $v) {
            $$k = $v;
          }
        }
        
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
  protected function copy_controller_vars()
  {
    $this->params =& $this->controller->params;
    
    $controller_vars = get_object_vars($this->controller);
    $view_vars = get_class_vars(get_class($this));
    
    $vars = array_diff_key($controller_vars, $view_vars);
    foreach($vars as $k => $v) {
      $this->$k =& $v;
    }
  }
}
?>
