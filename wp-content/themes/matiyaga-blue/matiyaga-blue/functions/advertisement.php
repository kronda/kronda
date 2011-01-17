<?php

class Advertisement {

	protected $keyword;
	protected $value_img;
	protected $value_web;
	protected $name;
	protected $description;

	function __construct($keyword,$name,$description='') {
		$this->keyword = $keyword;
		$this->value_img = get_settings($keyword. '_img_url');
		$this->value_web = get_settings($keyword. '_web_url');
		$this->name = $name;
		$this->description = $description;
	}

	public function getKeyword($context) {
		switch (strtolower($context)) {
			case 'img':
				return $this->keyword . '_img_url';
				break;
			case 'web':
				return $this->keyword . '_web_url';
				break;
		}
	}

	public function getValue($context) {
		switch (strtolower($context)) {
			case 'img':
				return $this->value_img;
				break;
			case 'web':
				return $this->value_web;
				break;
		}
	}

	public function setValue($context,$value) {
		switch (strtolower($context)) {
			case 'img':
				$this->value_img = $value;
				break;
			case 'web':
				$this->value_web = $value;
				break;
		}
	}

	public function getName() {
		return $this->name;
	}

	public function getType() {
		return $this->type;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function toHTMLForm() {
		$strHTML  = '';
		$strHTML .= '<tr valign="top">';
		$strHTML .= '	<th scope="row"><label for="' . $this->keyword . '">' . $this->name . '</label></th>';
		$strHTML .= '	<td>';
		$strHTML .= '		<label for="' . $this->keyword. '_img_url">Image URL</label><br />';
		$strHTML .= '		<input name="' . $this->keyword . '_img_url" type="text" id="' . $this->keyword . '_img_url" value="' . $this->value_img . '" size="80" /><br />';
		$strHTML .= '		<label for="' . $this->keyword. '_web_url">Website</label><br />';
		$strHTML .= '		<input name="' . $this->keyword . '_web_url" type="text" id="' . $this->keyword . '_web_url" value="' . $this->value_web . '" size="80" />';
		$strHTML .= '		<br /><small>' . $this->description . '</small>';
		$strHTML .= '	</td>';
		$strHTML .= '</tr>';
		return $strHTML;
	}

}

?>
