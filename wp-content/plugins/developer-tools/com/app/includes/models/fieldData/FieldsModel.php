<?php
class FieldsModel
{
  public static $data;
  
  private function __construct(){}
  
  public static function getInstance() 
  {
      if (!isset(self::$data)) {
          $c = __CLASS__;
          self::$data = new $c;
      }

      return self::$data;
  }

  public function GetData()
  {
    $fields = "TextArea|Textarea\n";
		$fields .= "TextInput|Text input\n";
		$fields .= "SelectList|Select list\n";
    $fields .= "Checkbox|Checkbox\n";
    $this->data = $fields;
  }
  
  public function __clone(){}
}
