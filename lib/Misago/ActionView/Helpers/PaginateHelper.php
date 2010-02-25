<?php
use Misago\ActiveSupport\String;

# Builds pagination links.
# 
# Options:
# 
# - +params+     - parameters to build links
# - +param_name+ - page parameter in links (defauts to 'page')
# - +page_links+ - set to false to display next and previous links only
# 
function paginate($collection, $options=array())
{
  if ($collection->total_pages == 1) return null;
  
  $link_options  = isset($options['params'])     ? $options['params']     : array();
  $param_name    = isset($options['param_name']) ? $options['param_name'] : 'page';
  $ul_content    = array();
  
  $previous_page = ($collection->current_page > 1) ? $collection->current_page - 1 : null;
  $next_page     = ($collection->current_page < $collection->total_pages) ? $collection->current_page + 1 : null;
  
  # renderer
  $render_link = function($page, $label) use($param_name, $link_options)
  {
    if ($page === null) return $label;
    
    $link_options[$param_name] = $page;
    return link_to($label, url_for($link_options));
  };
  
  # previous page
  $ul_content[] = tag('li', $render_link($previous_page, t('previous')), array('class' => 'previous'));
  
  # page links
  if (!isset($options['page_links']) or $options['page_links'])
  {
    $start_page = max(1, $collection->current_page - 5);
    $end_page   = min($collection->current_page + 5, $collection->total_pages);
    
    for($p=$start_page; $p<=$end_page; $p++) {
      $ul_content[] = tag('li', $render_link(($p === $collection->current_page) ? null : $p, $p));
    }
  }

  # next page
  $ul_content[] = tag('li', $render_link($next_page,     t('next')),     array('class' => 'next'));
  
  # container
  $ul_options = array('class' => 'pagination');
  if (isset($options['id']) and $options['id']) {
    $ul_options['id'] = String::pluralize(String::underscore($collection->model)).'_pagination';
  }
  return tag('ul', implode(' ', $ul_content), $ul_options);
}

?>
