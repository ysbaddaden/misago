<?php

class DBO_Mysql extends DBO_Base
{
  private   $link;
  protected $field_quote = "`";
  protected $value_quote = "'";

  # TODO: DBO::Mysql::start()
  function start()
  {
    if (!$this->link)
    {
      
    }
  }
  
  function stop()
  {
    if ($this->link) {
      mysql_close($this->link);
    }
  }
  
  function __destruct()
  {
    $this->stop();
  }

  # TODO: DBO::Mysql::execute();
  function execute($sql)
  {
    if (!$this->link) {
      $this->start();
    }
    
  }
  
  # TODO: DBO::Mysql::parse_resultset();
  function parse_resultset($resultset, $scope=null)
  {
    
  }
  
  function value($value)
  {
    if (!$this->link) {
      $this->start();
    }
    return "'".mysql_real_escape_string(trim($value), $this->link)."'";
  }
}

?>
