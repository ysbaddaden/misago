<?php
namespace Misago\ActionView;

# Renders templates.
# 
# See <tt>Misago\ActionController\Base::render()</tt> for actual help, since
# you shall not need to use this object directly, except for
# a few methods, like <tt>yield</tt> or rendering partials.
# 
class Base extends \Misago\Object
{
  public    $view_path;
  public    $view_format;
  protected $controller;
  protected $params;
  
  protected $yields = array(
    'title' => ''
  );
  
  function __construct($controller=null)
  {
    if ($controller !== null)
    {
      $this->controller = $controller;
      $this->view_path  = $controller->view_path;
      $helpers          = $this->controller->helpers;
    }
    
    if (!isset($helpers) or $helpers == ':all')
    {
      $helpers = apc_fetch(TMP.'/list_of_helpers', $success);
      
      if ($success === false)
      {
        $helpers = array();
        $this->_find_helpers($helpers, __DIR__.'/Helpers');
        $this->_find_helpers($helpers, ROOT.'/app/helpers');
        apc_store(TMP.'/list_of_helpers', $helpers, strtotime('+24 hours'));
      }
    }
    
    if (is_array($helpers))
    {
      foreach($helpers as $helper) {
        require_once "{$helper}Helper.php";
      }
    }
  }
  
  # :private:
  protected function _find_helpers(&$helpers, $path)
  {
    $dh = opendir($path);
    if ($dh)
    {
      while(($file = readdir($dh)) !== false)
      {
        if (is_file("$path/$file")
          and strpos($file, 'Helper.php'))
        {
          $helpers[] = $path.'/'.str_replace('Helper.php', '', $file);
        }
        elseif(is_dir("$path/$file")
          and $file != '.'
          and $file != '..')
        {
          $this->_find_helpers($helpers, "$path/$file");
        }
      }
      closedir($dh);
    }
  }
  
  # Renders a template (view, layout or partial).
  # 
  # = Generic option(s):
  # 
  # - +format+ - which format to use? defaults to +html+
  # 
  # = Render a view:
  # 
  #   render(array('action' => 'index'));
  # 
  # This will render the view +index.html.tpl+.
  # 
  # = Render a partial:
  # 
  #   render(array('partial' => 'form'));
  #   render(array('partial' => 'form', 'locals' => array('f' => $f)));
  # 
  # This will render the partial +_form.html.tpl+.
  # 
  # = Render a collection of partials:
  # 
  #   render(array('partial' => 'product', 'collection' => $products));
  # 
  # The collection must be an array or an iterable object.
  # This will create the following local vars: +$product+ and +$product_counter+.
  # 
  # = Rendering shared partials
  # 
  # You may share partials between controllers.
  # 
  #   render(array('partial' => 'posts/post'));
  #   render(array('partial' => 'posts/post', 'collection' => $posts));
  # 
  # This will render the partial +posts/_post.html.tpl+, regardless of
  # which controller this is being called from.
  # 
  # IMPROVE: Partial shortcut when rendering forms: render(array('partial' => $f)).
  function render($options)
  {
    # locals
    if (!empty($options['locals']))
    {
      foreach($options['locals'] as $k => $v) {
        $$k = $v;
      }
    }
    
    # template
    if (isset($options['template']))
    {
      $this->view_format = isset($options['format']) ? $options['format'] : 'html';
      $__template_file = "{$options['template']}.{$this->view_format}.tpl";
      
      if (file_exists(ROOT."/app/views/{$__template_file}"))
      {
        if ($this->controller !== null) {
          $this->copy_controller_vars();
        }
        
        ob_start();
        include ROOT."/app/views/{$__template_file}";
        return ob_get_clean();
      }
      
      throw new \Misago\Exception("Template not found: '{$__template_file}'", 404);
    }
    
    # partial (or collection of partials)
    elseif (isset($options['partial']))
    {
      if (strpos($options['partial'], '/'))
      {
        $__partial_file = explode('/', $options['partial']);
        $__partial_file = implode('/', array_slice($__partial_file, 0, -1)).
          '/_'.array_pop($__partial_file);
      }
      else {
        $__partial_file = $this->view_path.'/_'.$options['partial'];
      }
      $__view_format = isset($options['format']) ? $options['format'] : $this->view_format;
      $__partial_file .= ".{$__view_format}.tpl";
      
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
          $__partial_var      = basename($options['partial']);
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
      
      throw new \Misago\Exception("Partial template not found: '{$__partial_file}'", 404);
    }
  }
  
  # Passes data from a template to another.
  # For instance from view to layout, partial to view, etc.
  function yield($name, $content=null)
  {
    if ($content === null) {
      return isset($this->yields[$name]) ? $this->yields[$name] : null;
    }
    $this->yields[$name] = $content;
  }
  
  # Returns true if a given template exists.
  function template_exists($template, $format=null)
  {
    if ($format === null) $format = $this->view_format;
    return file_exists(ROOT."/app/views/$template.$format.tpl");
  }
  
  # Copies public vars from controller, not overriding view's own class vars.
  # :private:
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
