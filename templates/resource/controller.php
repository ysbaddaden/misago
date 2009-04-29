<?php

# TODO: Add respond_to() to handle specific :format requests.
class #{Controller}Controller extends ApplicationController
{
  function index()
  {
    $#{model} = new #{Model}();
    $this->#{model_plural} = $#{model}->find(':all');
  }
  
  function show()
  {
    $this->#{model} = new #{Model}($this->params[':id']);
  }
  
  function neo()
  {
    $this->#{model} = new #{Model}();
    $this->render('create');
  }
  
  function create()
  {
    $#{model} = new #{Model}();
    $this->#{model} = $#{model}->create($this->params['#{model}']);
    
    if ($this->#{model}->errors->is_empty()) {
      HTTP::redirect(show_#{model}_path(array(':id' => $this->#{model}->id)), 201);
    }
#    else {
#      HTTP::status(412);
#    }
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
      HTTP::redirect(show_#{model}_path(array(':id' => $this->#{model}->id)), 200);
    }
#    else {
#      HTTP::status(412);
#    }
    $this->render('edit');
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
