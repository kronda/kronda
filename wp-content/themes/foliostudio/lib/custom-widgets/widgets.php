<?php
/*
* Base Theme Widget Class. Extend this class when adding new widgets instead of WP_Widget.
* Handles updating, displaying the form in wp-admin and $before_widget/$after_widget
*/
class ThemeWidgetBase extends WP_Widget {
	// Should $before_widget and $after_widget be printed
	var $print_wrappers = true;
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		foreach ($this->custom_fields as $field) {
			if ($field['type'] == 'integer') {
				$instance[$field['name']] = intval($new_instance[$field['name']]);
			} else {
				$instance[$field['name']] = $new_instance[$field['name']];
			}
		}
		
		return $instance;
	}
 
	function form($instance) {
		$defaults = array();
		foreach ($this->custom_fields as $field) {
			$defaults[$field['name']] = $field['default'];
		}
		$instance = wp_parse_args( (array) $instance, $defaults);
		?>
		<?php if (empty($this->custom_fields)) : ?>
			<p>There are no available options for this widget</p>
		<?php endif; ?>
		<?php foreach ($this->custom_fields as $field) : ?>
			<?php call_user_func_array('widget_field_'.$field['type'], array($this, $instance, $field['name'], $field['title'], $field))?>
		<?php endforeach; ?>
		<?php
	}
	
	function widget($args, $instance) {
        extract($args);
        if ($this->print_wrappers) {
        	echo $before_widget;
        }
        $this->front_end($args, $instance);
        if ($this->print_wrappers) {
        	echo $after_widget;
        }
    }
    
    /*abstract*/ function front_end($args, $instance) {
    	
    }
}

/*
* Field rendering functions. Called in the admin when showing the widget form.
*/
function widget_field_text($obj, $instance, $fieldname, $fieldtitle) {
	$value = $instance[$fieldname];
	?>
	<p>
		<label for="<?php echo $obj->get_field_id($fieldname); ?>"><?php echo $fieldtitle; ?>:</label>
		<input class="widefat" id="<?php echo $obj->get_field_id($fieldname); ?>" name="<?php echo $obj->get_field_name($fieldname); ?>" type="text" value="<?php echo attribute_escape($value); ?>" />
	</p>
	<?php
}
function widget_field_integer($obj, $instance, $fieldname, $fieldtitle) {
	$value = intval($instance[$fieldname]);
	?>
	<p>
		<label for="<?php echo $obj->get_field_id($fieldname); ?>"><?php echo $fieldtitle; ?>:</label>
		<input class="widefat" id="<?php echo $obj->get_field_id($fieldname); ?>" name="<?php echo $obj->get_field_name($fieldname); ?>" type="text" value="<?php echo attribute_escape($value); ?>" />
	</p>
	<?php
}
function widget_field_textarea($obj, $instance, $fieldname, $fieldtitle) {
	$value = $instance[$fieldname];
	?>
	<p>
		<label for="<?php echo $obj->get_field_id($fieldname); ?>"><?php echo $fieldtitle; ?>:</label>
		<textarea class="widefat" id="<?php echo $obj->get_field_id($fieldname); ?>" name="<?php echo $obj->get_field_name($fieldname); ?>" style="height: 150px;" type="text"><?php echo attribute_escape($value); ?></textarea>
	</p>
	<?php /* REMOVED due to $control_opts in each widget which auto-resize the form ?>
	<script type="text/javascript" charset="utf-8">
	(function ($) {
		var parent = $("#<?php echo $obj->get_field_id($fieldname); ?>").parents('.widget:eq(0)');
		var default_width = $(parent).width();
		$(parent).find('.widget-title-action a').bind('click', function () {
			if ($(parent).find('.widget-inside').is(':visible')) {
				$(parent).width(default_width).css('margin-left', '0px');;
			} else {
				$(parent).width(400).css('margin-left', '-135px');
			}
		});
	})(jQuery)
	</script>
	<?php */ ?>
	<?php
}
function widget_field_select($obj, $instance, $fieldname, $fieldtitle, $field_array) {
	$value = $instance[$fieldname];
	?>
	<p>
		<label for="<?php echo $obj->get_field_id($fieldname); ?>"><?php echo $fieldtitle; ?>:</label><br />
		<select name="<?php echo $obj->get_field_name($fieldname); ?>" id="<?php echo $obj->get_field_id($fieldname); ?>" style="width: 100%;">
			<?php foreach ($field_array['options'] as $val => $name) : ?>
				<option value="<?php echo $val; ?>" <?php echo ($val == attribute_escape($value)) ? 'selected="selected"' : ''; ?>><?php echo $name; ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}
function widget_field_set($obj, $instance, $fieldname, $fieldtitle, $field_array) {
	$value = $instance[$fieldname];
	?>
	<p>
		<label for="<?php echo $obj->get_field_id($fieldname); ?>"><?php echo $fieldtitle; ?>:</label><br />
		<?php foreach ($field_array['options'] as $val => $name) : ?>
			<input type="checkbox" name="<?php echo $obj->get_field_name($fieldname); ?>[]" value="<?php echo $val; ?>" <?php echo (!(in_array($val, $value) === FALSE)) ? 'checked="checked"' : ''; ?>>&nbsp;<?php echo $name; ?><br />
		<?php endforeach; ?>
	</p>
	<?php
}
?>