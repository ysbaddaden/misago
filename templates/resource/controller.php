<?php

class #{Controller}Controller extends ApplicationController
{
  function index()
  {
    $#{model} = new #{Model}();
    $this->#{model_plural} = $#{model}->find(':all');
    
    switch($this->format)
    {
      case 'html': break; # index.html.tpl
      case 'xml': $this->render(array('xml' => $this->#{model_plural})); break;
    }
  }
  
  function show()
  {
    $this->#{model} = new #{Model}($this->params[':id']);
    
    switch($this->format)
    {
      case 'html': break; # show.html.tpl
      case 'xml': $this->render(array('xml' => $this->#{model})); break;
    }
  }
  
  function neo()
  {
    $this->#{model} = new #{Model}();
    $this->render('new');
  }
  
  function create()
  {
    $#{model} = new #{Model}();
    $this->#{model} = $#{model}->create($this->params['#{model}']);
    
    if ($this->#{model}->errors->is_empty()) {
      HTTP::redirect(show_#{model}_path($this->#{model}->id), 201);
    }
    else
    {
      switch($this->format)
      {
        case 'html': $this->render('new'); break;
        case 'xml':  $this->render(array('xml' => $this->#{model}->errors, 'status' => 412)); break;
      }
    }
  }
  
  function edit()
  {
    $this->#{model} = new #{Model}($this->params[':id']);
  }
  
  function update()
  {
    $#{model} = new #{Model}();
    $this->#{model} = $#{model}->update($this->params[':id'], $this->params['#{model}']);
    
    if ($this->#{model}->errors->is_empty()) {
      HTTP::redirect(show_#{model}_path($this->#{model}->id), 200);
    }
    else
    {
      switch($this->format)
      {
        case 'html': $this->render('edit'); break;
        case 'xml':  $this->render(array('xml' => $this->#{model}->errors, 'status' => 412)); break;
      }
    }
  }
  
  function delete()
  {
    $#{model} = new #{Model}();
    if ($#{model}->delete($this->params[':id'])) {
      HTTP::redirect(#{model}s_path(), 410);
    }
    else {
      HTTP::status(500);
    }
  }
}

?>
