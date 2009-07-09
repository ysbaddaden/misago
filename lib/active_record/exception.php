<?php

# Handles exceptions for ActiveRecord classes.
class ActiveRecord_Exception extends MisagoException
{
  protected $default_code = 500;
  
  function __construct($message, $code=null)
  {
    parent::__construct($message, $code ? $code : $this->default_code);
  }
}

class ActiveRecord_AdapterNotFound extends ActiveRecord_Exception {}

class ActiveRecord_AdapterNotSpecified extends ActiveRecord_Exception {}

#class ActiveRecord_AssociationTypeMismatch extends ActiveRecord_Exception {}

#class ActiveRecord_AttributeAssignmentError extends ActiveRecord_Exception {}

#class ActiveRecord_ConfigurationError extends ActiveRecord_Exception {}

class ActiveRecord_ConnectionNotEstablished extends ActiveRecord_Exception {}

class ActiveRecord_DangerousAttributeError extends ActiveRecord_Exception {}

class ActiveRecord_MissingAttributeError extends ActiveRecord_Exception {}

class ActiveRecord_ReadOnlyRecord extends ActiveRecord_Exception {}

class ActiveRecord_RecordInvalid extends ActiveRecord_Exception {}

class ActiveRecord_RecordNotFound extends ActiveRecord_Exception {}

class ActiveRecord_RecordNotSaved extends ActiveRecord_Exception {}

class ActiveRecord_StatementInvalid extends ActiveRecord_Exception {}

?>
