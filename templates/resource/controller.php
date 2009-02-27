<?php

# TODO: Set HTTP status codes.
#class #{Class}Controller extends ApplicationController
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
  # POST /#{class_plural}
  function create()
  {
    if (!empty($this->params['#{class}']))
    {
      $#{class} = new #{Class}();
      $this->#{class} = $#{class}->create($this->params['#{class}']);
    }
    else {
      $this->#{class} = new #{Class}();
    }
  }
  
  # PUT /#{class_plural}/:id
  function update()
  {
    if (!empty($this->params['#{class}']))
    {
      $#{class} = new #{Class}();
      $this->#{class} = $#{class}->update($this->params[':id'], $this->params['#{class}']);
      
      if ($this->#{class} !== null) {
        $this->redirect(show_#{class}_path($this->params[':id']));
      }
    }
    else {
      $this->#{class} = new #{Class}();
    }
  }
  
  # DELETE /#{class_plural}/:id
  function delete()
  {
    $#{class} = new #{Class}();
    $#{class}->delete($this->params[':id']);
  }
}

?>
