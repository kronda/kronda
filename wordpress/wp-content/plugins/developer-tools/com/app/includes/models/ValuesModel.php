<?php
class ValuesModel
{
	public static $values;
	
  private function __construct(){}
  
  public static function getInstance() 
  {
      if (!isset(self::$values)) {
          $c = __CLASS__;
          self::$values = new $c;
      }

      return self::$values;
  }

  public function GetData()
  {	
  	$this->values = get_option('developer-tools-values');
  }
  
  public function __clone(){}    
}