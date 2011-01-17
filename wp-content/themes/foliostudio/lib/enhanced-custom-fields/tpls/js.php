<script type="text/javascript" charset="utf-8">
(function ($) {
	<?php if (isset($this->parent_page)): ?>
	    $('#parent_id').change(function () {
			var cont = $('#<?php echo $this->id ?>');
	        if ($(this).val()==<?php echo $this->parent_page->ID ?>) {
	        	cont.slideDown();
	        } else {
	        	cont.slideUp();
	        }
	    }).change();
	<?php endif ?>
	<?php if (isset($this->cat)): ?>
		$('#categories-all input, #categories-pop input').change(function() {
			var cont = $('#<?php echo $this->id ?>');
	        if ($(this).val()==<?php echo $this->cat->term_id ?> && $(this).is(':checked')) {
	        	cont.slideDown();
	        } else if($(this).val()==<?php echo $this->cat->term_id ?>) {
	        	cont.slideUp();
	        }
		});
		if (!$('#categories-all input[value=<?php echo $this->cat->term_id ?>], #categories-pop input[value=<?php echo $this->cat->term_id ?>]').is(':checked')) {
			$('#<?php echo $this->id ?>').hide();
		};
	<?php endif ?>
	<?php if (isset($this->template_name)): ?>
	    $('#page_template').change(function () {
			var cont = $('#<?php echo $this->id ?>');
	        if ($(this).val()=='<?php echo $this->template_name . '.php'; ?>') {
	        	cont.slideDown();
	        } else {
	        	cont.slideUp();
	        }
	    }).change();		
	<?php endif; ?>
})(jQuery);
</script>