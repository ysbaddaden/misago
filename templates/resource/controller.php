<?php

class #{Controller}Controller extends ApplicationController
{
  function index()
  {
    $this->#{model_plural} = #{Model}::all();
    
    switch($this->format)
    {
      case 'html': break; # index.html.tpl
      case 'xml':
      case 'json': $this->render(array($this->format => $this->#{model_plural})); break;
    }
  }
  
  function show()
  {
    $this->#{model} = new #{Model}($this->params[':id']);
    
    switch($this->format)
    {
      case 'html': break; # show.html.tpl
      case 'xml':
      case 'json': $this->render(array($this->format => $this->#{model})); break;
    }
  }
  
  function neo()
  {
    $this->#{model} = new #{Model}();
    $this->render('new');
  }
  
  function edit()
  {
    $this->#{model} = new #{Model}($this->params[':id']);
  }
  
  function create()
  {
    $this->#{model} = new #{Model}($this->params['#{model}']);
    
    if ($this->#{model}->save())
    {
      $this->flash['notice'] = t('#{Model} was successfully created.', '#{controller}.create');
      
      switch($this->format)
      {
        case 'html': $this->redirect_to(show_#{model}_path($this->#{model})); break;
        case 'xml': case 'json':
          $this->render(array(
            $this->format => $this->#{model},
            'status'      => 201,
            'location'    => show_#{model}_url($this->#{model})));
        break;
      }
    }
    else
    {
      switch($this->format)
      {
        case 'html': $this->render('new'); break;
        case 'xml': case 'json':
          $this->render(array($this->format => $this->#{model}->errors, 'status' => 412));
        break;
      }
    }
  }
  
  function update()
  {
    $this->#{model} = new #{Model}($this->params[':id']);
    
    if ($this->#{model}->update_attributes($this->params['#{model}']))
    {
      $this->flash['notice'] = t('#{Model} was successfully updated.', '#{controller}.create');
      
      switch($this->format)
      {
        case 'html': $this->redirect_to(show_#{model}_path($this->#{model})); break;
        case 'xml': case 'json':
          $this->render(array($this->format => $this->#{model}, 'status' => 200));
        break;
      }
    }
    else
    {
      switch($this->format)
      {
        case 'html': $this->render('edit'); break;
        case 'xml': case 'json':
          $this->render(array($this->format => $this->#{model}->errors, 'status' => 412));
        break;
      }
    }
  }
  
  function delete()
  {
    if (#{Model}::delete($this->params[':id']))
    {
      $this->flash['notice'] = t('#{Model} was successfully deleted.', '#{controller}.create');
      switch($this->format)
      {
        case 'html': $this->redirect_to(#{model}s_path()); break;
        case 'xml':
        case 'json': $this->head(410); break;
      }
    }
    else {
      $this->head(500);
    }
  }
}

?>
