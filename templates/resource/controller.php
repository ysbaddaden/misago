<?php

# TODO: Add respond_to() to handle specific :format requests.
class #{Controller}Controller extends ApplicationController
{
  # GET /#{controller}
  function index()
  {
    $#{model} = new #{Model}();
    $this->#{model_plural} = $#{model}->find(':all');
  }
  
  # GET /#{controller}/:id
  function show()
  {
    $this->#{model} = new #{Model}($this->params[':id']);
  }
  
  # GET /#{controller}/new
  function neo()
  {
    $this->#{model} = new #{Model}();
  }
  
  # POST /#{controller}
  function create()
  {
    $#{model} = new #{Model}();
    $this->#{model} = $#{model}->create($this->params['#{model}']);
    
    if ($this->#{model} !== null) {
      HTTP::redirect(show_#{model}_path($this->params[':id']), 201);
    }
    elseif(!$this->#{model}->errors->is_empty()) {
      HTTP::status(412);
    }
    else {
      HTTP::status(500);
    }
    $this->render('edit');
  }
  
  # GET /#{controller}/:id/edit
  function edit()
  {
    $this->#{model} = new #{Model}($this->params[':id']);
  }
  
  # PUT /#{controller}/:id
  function update()
  {
    $#{model} = new #{Model}();
    $this->#{model} = $#{model}->update($this->params[':id'], $this->params['#{model}']);
    
    if ($this->#{controller} !== null) {
      HTTP::redirect(show_#{model}_path($this->params[':id']), 200);
    }
    elseif (!$this->#{model}->errors->is_empty()) {
      HTTP::status(412);
    }
    else {
      HTTP::status(500);
    }
    $this->render('edit');
  }
  
  # DELETE /#{controller}/:id
  function delete()
  {
    $#{model} = new #{Model}();
    if ($#{model}->delete($this->params[':id'])) {
      HTTP::status(410);
    }
    else {
      HTTP::status(500);
    }
  }
}

?>
