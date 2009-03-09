<?php

# TODO: Add respond_to() to handle specific :format requests.
class #{Class}Controller extends ApplicationController
{
  # GET /#{class_plural}
  function index()
  {
    $#{class} = new #{Class}();
    $this->#{class_plural} = $#{class}->find(':all');
  }
  
  # GET /#{class_plural}/:id
  function show()
  {
    $this->#{class} = new #{Class}($this->params[':id']);
  }
  
  # GET /#{class_plural}/create
  function neo()
  {
    $this->#{class} = new #{Class}();
  }
  
  # POST /#{class_plural}
  function create()
  {
    $#{class} = new #{Class}();
    $this->#{class} = $#{class}->create($this->params['#{class}']);
    
    if ($this->#{class} !== null) {
      HTTP::redirect(show_#{class}_path($this->params[':id']), 201);
    }
    elseif(!$this->#{class}->errors->is_empty()) {
      HTTP::status(412);
    }
    else {
      HTTP::status(500);
    }
    $this->render('edit');
  }
  
  # GET /#{class_plural}/edit/:id
  function edit()
  {
    $this->#{class} = new #{Class}($this->params[':id']);
  }
  
  # PUT /#{class_plural}/:id
  function update()
  {
    $#{class} = new #{Class}();
    $this->#{class} = $#{class}->update($this->params[':id'], $this->params['#{class}']);
    
    if ($this->#{class} !== null) {
      HTTP::redirect(show_#{class}_path($this->params[':id']), 200);
    }
    elseif (!$this->#{class}->errors->is_empty()) {
      HTTP::status(412);
    }
    else {
      HTTP::status(500);
    }
    $this->render('edit');
  }
  
  # DELETE /#{class_plural}/:id
  function delete()
  {
    $#{class} = new #{Class}();
    if ($#{class}->delete($this->params[':id'])) {
      HTTP::status(410);
    }
    else {
      HTTP::status(500);
    }
  }
}

?>
