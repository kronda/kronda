<?php
/*
* SimpleValidator v1.2.1 (last build: 09.02.2010)
* by CaTaHaC
* Validates forms (both php and js) and auto-fills in fields on error (optional).
* DOES NOT support post arrays
*/

/*
* Dependencies: 
	- php 4.0+ (not tested, works on 5.0+)
	- jquery 1.3.2+
*/

/*
 * Future plans:
 * --- Post array support & validation
*/

/* Changelog 1.0->1.1:
* - JS alert when captcha field is left empty on submit.
* - fixed a bug with captcha checking (typo in captch_source instead of captchA_source)
* - <?=$variable?> replaced with <?php echo $variable; ?>
*/
/* Changelog 1.1->1.2:
* - Fixed fill from post from breaking on quotes (").
* - Validate gpc does not change post_input anymore
* - Throws "Exception" instead of "CMSException" if "CMSException" is not defined
* - buildJS() now takes $form_selector (string) instead of $form_id (string) parameter
* - Added $no_conflict parameter to buildJS() to include (function ($) {})(jQuery)
* - validateRules() now loops through rules instead of post_input (as it should have)
* - Missing fields in the post_input are now considered as empty strings (e.g. unchecked checkboxes)
* - post_input array values are now imploded in a comma-separated list
* - Added nice_values (optional, passed by reference) argument to validateRules() - it is filled with the actual
	values which the validator uses (imploded arrays etc.)
* - Rules array format changed - check below for detailed format
*/
/* Changelog 1.2->1.2.1:
* - Added new rule type - R_OPTIONS. It requires an additional rule field "options". No JS validation provided (yet?)
* - Fixed js error alerts from breaking on quotes (").
*/

class SimpleValidator {
	const R_NOT_EMPTY = 'not-empty';
	const R_MAIL = 'mail';
	const R_CAPTCHA = 'captcha';
	const R_OPTIONS = 'options';
    public $rules = array();
    public $errors = array();
    public $captcha_source = '';
    public $mail_reg = '~^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$~';
    
    /*
	 * Rules Format:
	 	- [field name] => array(
	 		(required) 'rule'=>Rule ID (refer to constants above),
	 		(required) 'error'=> Error Message
	 		(optional) 'value_glue'=> The glue used to implode the value if if is an array
	 		(optional) 'not'=> 
	 	);
    */
    function __construct($rules) {
    	$this->rules = $rules;
    }
    
    /*
     * Sets the captcha code to match captcha fields with
    */
    function setCaptchaSource($captcha) {
    	$this->captcha_source = $captcha;
    }
    
    /*
	 * Clears errors array
    */
    function clearErrors() {
    	$this->errors = array();
    }
    
    /*
     * Returns the errors array
    */
    function getErrors() {
    	return $this->errors;
    }
    
    /*
     * Returns true if no validation errors occured, false if otherwise
    */
    function validateRules($post_input, $gpc = false, &$nice_values = array()) {
    	/* OLD - Wrong logic - it looped though the post input rather than through the rules
    	foreach ($post_input as $field => &$value) {
    		if ($gpc) { $value = get_magic_quotes_gpc() ? $value : addslashes($value); }
    		if (isset($this->rules[$field])) {
    			switch ($this->rules[$field][0]) {
    				case self::R_NOT_EMPTY: {
    					if ($value == '') {
    						$this->errors[] = $this->rules[$field][1];
    					}
    					break;
    				}
    				case self::R_MAIL: {
    					if (!preg_match($this->mail_reg, $value)) {
    						$this->errors[] = $this->rules[$field][1];
    					}
    					break;
    				}
    				case self::R_CAPTCHA: {
    					if ($value != $this->captcha_source) {
    						$this->errors[] = $this->rules[$field][1];
    					}
    					break;
    				}
    				default: {
    					throw new SimpleValidatorException('Unknown validation method (' . $this->rules[$field][0] . ')');
    				}
    			}
    		}
    	}*/
    	
    	foreach ($this->rules as $field => $field_options) {
    		$value = (!isset($post_input[$field])) ? '' : $post_input[$field];
    		if (is_array($value)) {
    			$glue = (isset($field_options['value_glue'])) ? $field_options['value_glue'] : ',';
    			$value = implode($glue, $value);
    		}
    		$nice_values[$field] = $value;
    		if ($gpc) { $value = get_magic_quotes_gpc() ? $value : addslashes($value); }
			switch ($field_options['rule']) {
				case self::R_NOT_EMPTY: {
					if ($value == '') {
						$this->errors[] = $field_options['error'];
					}
					break;
				}
				case self::R_MAIL: {
					if (!preg_match($this->mail_reg, $value)) {
						$this->errors[] = $field_options['error'];
					}
					break;
				}
				case self::R_CAPTCHA: {
					if ($value != $this->captcha_source) {
						$this->errors[] = $field_options['error'];
					}
					break;
				}
				case self::R_OPTIONS: {
					if (array_search($value, $field_options['options']) === FALSE) {
						$this->errors[] = $field_options['error'];
					}
					break;
				}
				default: {
					throw new SimpleValidatorException('Unknown validation method (' . $field_options['rule'] . ')');
				}
			}
			if ( isset($field_options['not']) && $value == $field_options['not'] ) {
				$this->errors[] = $field_options['error'];
			}
    	}
    	
    	if (empty($this->errors)) {
    		return true;
    	}
    	return false;
    }
    
    /*
     * Builds the javascript code for the validation
    */
    function buildJS($form_selector, $fill_from_post = false, $no_conflict = false, $invalid_class = "non-valid") { ?>
    	<script type="text/javascript" charset="utf-8">
    	<?php if ($no_conflict) : ?>
    	(function ($) {
    	<?php endif; ?>
    		$("<?php echo $form_selector; ?>").submit(function () {
    			$(this).find('input, select, textarea').removeClass("<?php echo $invalid_class; ?>");
    			var field = {};
    			var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    			<?php
    			foreach ($this->rules as $field => $rules) {
    				echo 'field = $("' . $form_selector . ' *[name=\'' . $field . '\']");'."\n";
    				switch ($rules['rule']) {
	    				case self::R_NOT_EMPTY: ?>
	    					if ($(field).val() == '') {
	    						alert("<?php echo str_replace('"', '\"', $rules['error']); ?>");
	    						$(field).addClass("<?php echo $invalid_class; ?>");
	    						$(field).focus();
	    						return false;
	    					}
	    					<?php
	    					break;
	    				case self::R_MAIL: ?>
	    					if (!reg.test($(field).val())) {
	    						alert("<?php echo str_replace('"', '\"', $rules['error']); ?>");
	    						$(field).addClass("<?php echo $invalid_class; ?>");
	    						$(field).focus();
	    						return false;
	    					}
	    					<?php
	    					break;
	    				case self::R_CAPTCHA: ?>
	    					if ($(field).val() == '') {
	    						alert("<?php echo str_replace('"', '\"', $rules['error']); ?>");
	    						$(field).addClass("<?php echo $invalid_class; ?>");
	    						$(field).focus();
	    						return false;
	    					}
	    					<?php
	    					break;
    				}
    				if ( !empty($rules['not']) ) { ?>
    					if ($(field).val() == '<?php echo $rules['not'] ?>') {
    						alert("<?php echo str_replace('"', '\"', $rules['error']); ?>");
    						$(field).addClass("<?php echo $invalid_class; ?>");
    						$(field).focus();
    						return false;
    					}
    				<?php
    				}
    			} ?>
    			return true;
    		});
    		<?php if ($fill_from_post) {
    			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    				foreach ($_POST as $field => $value) { ?>
    					<?php if (is_array($value)) { continue; } ?>
    					$("<?php echo $form_selector; ?> *[name='<?php echo $field; ?>']").val("<?php echo htmlspecialchars($value); ?>");
    				<?php }
    			}
    		} ?>
    	<?php if ($no_conflict) : ?>
    	})(jQuery)
    	<?php endif; ?>
    	</script>
    <?php }
}

if (class_exists('CMSException')) {
	class SimpleValidatorException extends CMSException {}
} else {
	class SimpleValidatorException extends Exception {}
}
?>