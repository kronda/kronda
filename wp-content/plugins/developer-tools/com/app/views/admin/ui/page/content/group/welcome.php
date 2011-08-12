<?php
	$rss = fetch_feed( 'http://developertools.kjmeath.com/?feed=rss2&tag=' . DEVELOPER_TOOLS_VERSION );
	if (!is_wp_error( $rss ) ) :
	  $maxitems = $rss->get_item_quantity(5); 
	  $rss_items = $rss->get_items(0, $maxitems); 
	endif;
	if ($maxitems > 0 ) : ?>
	<div id="plugin_updates">
		<h4><span>!</span>Attention</h4>
		<div class="rss-widget">
			<ul>
			    <?php foreach ( $rss_items as $item ) : ?>
			    <li>
			        <div class="rss-title"><a href="<?php print $item->get_permalink() ?>" target="_blank"><?php echo $item->get_title(); ?></a></div>
              <div class="rss-date">Posted: <?php print str_replace(' ', '&nbsp;', $item->get_date('F j, Y') ); ?></div>
							<div class="rss-content"><?php print $item->get_content(); ?></div>
			    </li>
			    <?php endforeach; ?>
			</ul>
		</div>
	</div>
<?php endif; ?>
<div id="donate_to_me">
	<form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="WWX899P8YXHF4">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
</div>
<div id="welcome_description">
  <p><?php _e( 'When this plugin is enabled, you have access to', 'developer-tools' ) ?> <a href="http://krumo.sourceforge.net/" target="_blank">krumo( )</a> <?php _e( 'in the theme files. Instead of using', 'developer-tools' ) ?> <span style="color:red">&lt;?php</span> var_dump( $foo ); <span style="color:red">?&gt;</span> <?php _e( 'or', 'developer-tools' ) ?> <span style="color:red">&lt;?php</span> print_r( $foo ); <span style="color:red">?&gt;</span> <?php _e( 'to debug PHP variables, use', 'developer-tools' ) ?> <span style="color:red">&lt;?php</span> krumo( $variable ); <span style="color:red">?&gt;</span></p>
  <p><strong><?php _e( 'Find a bug? Having issues? Want other features?', 'developer-tools' ) ?></strong><br /><?php _e( 'Go to the', 'developer-tools' ) ?> <a href="http://wordpress.org/tags/developer-tools/" target="_blank"><?php _e( 'support forum.', 'developer-tools' ) ?></a></p>
</div>
<div class="clear"></div>