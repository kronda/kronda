<?php
class TaxonomiesModel
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
		$taxonomies = get_taxonomies(array('public' => true),'objects');
		foreach( $taxonomies as $taxonomy )
			if( $taxonomy->name != 'nav_menu' && $taxonomy->name != 'post_format' ) //dont need nav menu
				$checkboxes .= $taxonomy->name.'|'.$taxonomy->label."\n";
    	$this->data = $checkboxes;
    }
    
    public function __clone(){}    
}