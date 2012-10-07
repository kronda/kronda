<?php
class ExcerptLength Extends Feature
{
  public function SetSettings()
  {
    $this->title        = __( 'Excerpt length', 'developer-tools' );
    $this->fields       = array(
      array( 
        'fieldType' => 'TextInput', 
        'label' => __( 'Character length', 'developer-tools' ),
        'name' => 'length',
        'cssClass' => 'small_int',
        'characterSet' => 'numeric'
      )
    );
  }
                
  public function Enabled($value)
  {
    $this->value = $value;
    add_filter( 'excerpt_length', array(&$this, 'ExcerptLength' ) );     
  }
  
  public function ExcerptLength()
  {
    return $this->value; 
  }
}
