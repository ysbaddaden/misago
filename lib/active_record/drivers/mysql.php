<?php

# DEPRECATED: In favor of ActiveRecord_ConnectionAdapters_MysqlAdapter
class DBO_Mysql extends DBO_Base
{
  private   $link;
  protected $field_quote = "`";
  protected $value_quote = "'";

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

  function execute($sql)
  {
    if (!$this->link) {
      $this->start();
    }
    
  }
  
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
