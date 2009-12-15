<?php
namespace Misago\ActiveRecord;

# Handles exceptions for ActiveRecord classes.
class Exception extends \Misago\Exception
{
  protected $default_code = 500;
  
  function __construct($message, $code=null) {
    parent::__construct($message, $code ? $code : $this->default_code);
  }
}

class AdapterNotFound extends Exception {}

class AdapterNotSpecified extends Exception {}

class AssociationTypeMismatch extends Exception {}

class AttributeAssignmentError extends Exception {}

class ConfigurationError extends Exception {}

class ConnectionNotEstablished extends Exception {}

class DangerousAttributeError extends Exception {}

class MissingAttributeError extends Exception {}

class ReadOnlyRecord extends Exception {}

class RecordInvalid extends Exception {}

class RecordNotFound extends Exception {}

class RecordNotSaved extends Exception {}

class StatementInvalid extends Exception {}

?>
