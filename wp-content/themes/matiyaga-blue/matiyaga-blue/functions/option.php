<?php

class Option {

	protected $keyword;
	protected $value;
	protected $name;
	protected $type;
	protected $description;
	protected $width;
	protected $height;

	function __construct($keyword,$name,$description='',$type='textbox',$width='500',$height='100') {
		$this->keyword = $keyword;
		$this->value = get_settings($keyword);
		$this->name = $name;
		$this->description = $description;
		$this->type = $type;
		$this->width = $width;
		$this->height = $height;
	}

	public function getKeyword() {
		return $this->keyword;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
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
		switch ($this->type) {
			default:
				$strHTML .= '		<input name="' . $this->keyword . '" type="text" id="' . $this->keyword . '" value="' . $this->value . '" style="width: ' . $this->width . 'px" />';
				break;
			case 'textarea':
				$strHTML .= '		<textarea name="' . $this->keyword . '" id="' . $this->keyword . '" style="width: ' . $this->width . 'px; height: ' . $this->height . 'px;">' . stripslashes($this->value). '</textarea>';
				break;
		}
		$strHTML .= '		<br /><small>' . $this->description . '</small>';
		$strHTML .= '	</td>';
		$strHTML .= '</tr>';
		return $strHTML;
	}

}

?>
