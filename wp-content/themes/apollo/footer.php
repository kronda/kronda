	<div id="footer">
		<div class="shell">
			<div class="fr"><?php echo get_option('footer_text') ?></div>
			<p><?php echo get_option('footer_copyright_text') ?></p>
		</div>
	</div>
	<?php if ( !in_array('disable_cufon', get_option('advanced_settings', array()) ) ): ?>
		<script type="text/javascript">Cufon.now();</script>
	<?php endif ?>
	<?php wp_footer(); ?>
</body>
</html>