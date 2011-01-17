					</div>
					
					<div id="footer">
						<div id="footer-inner">
							<ul>
								<?php dynamic_sidebar('Footer Sidebar') ?>
							</ul>
							<div class="cl">&nbsp;</div>
						</div>
						<div id="footer-bottom">&nbsp;</div>
					</div>
					
				</div>
			</div>
			<div class="cl">&nbsp;</div>
		</div>
		<div id="page-bottom">&nbsp;</div>
		
		<div id="copyright">
			<?php echo get_option('footer_text', ''); ?>
		</div>
		
	</div>

	<script type="text/javascript"> Cufon.now(); </script>
	<?php wp_footer(); ?>
</body>
</html>