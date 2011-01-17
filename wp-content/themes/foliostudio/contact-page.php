<?php 
/*
Template Name: Contact Page
*/

$errors = false;
if ( isset($_POST['submit']) && isset($_POST['is_contact']) ) {
	$errors = f_handle_contact_submit();
}

$validator = new SimpleValidator(f_get_contact_rules());

get_header(); if (have_posts()) : the_post(); ?>	
	<div class="container">
		<div id="top-text-section">
			<div class="doubleborder">
				<?php 
				$title = get_post_meta(get_the_id(), '_custom_teaser_text', true);
				if ( empty($title) ) {
					$title = get_option('default_teaser_text');
				}
			 	?>
				<h2 class="tc cufon-plain"><?php echo htmlize($title); ?></h2>		
			</div>	
		</div>
		<?php print_breadcrumbs('<div id="pagination">', ' / ', '</div>'); ?>
		<div id="contact">
			<h2 class="cufon-plain"><?php the_title(); ?></h2>
			<div class="contact-form">
				<div class="entry">
					<?php the_content(); ?>
				</div>
				<form action="" method="post" id="contact-form">
					<div id="cf">
						<div class="top">
							<div class="bottom">
								<?php if ( $errors != false): ?>
									<ul class="errors-list">
									<?php foreach ($errors as $error): ?>
										<li><?php echo $error ?></li>
									<?php endforeach ?>
									</ul>
								<?php elseif(isset($_POST['submit'])): ?>
									<h3 class="success-msg">Thank you for contacting us!</h3>
								<?php endif ?>
								<?php $always_default = ($errors == false && isset($_POST['submit']) ) ?>
								<div>
									<input type="text" class="tfield blink" title="Name(required)"  value="<?php from_post('ctc-name', 'Name(required)', $always_default) ?>" name="ctc-name"/>
									<input type="text" class="tfield blink" title="Email(not published, required)" name="ctc-email" value="<?php from_post('ctc-email', 'Email(not published, required)', $always_default) ?>"/>
									<input type="text" class="tfield blink" title="Website"  value="<?php from_post('ctc-website', 'Website', $always_default) ?>" name="ctc-website"/>
									<textarea name="ctc-comment" class="field" id="cf-comment" rows="" cols=""><?php from_post('ctc-comment', $always_default) ?></textarea>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" name="is_contact" value="true">
					<input type="submit" class="btn-submit" title=""  value="Submit" name="submit"/>
				</form>
				<?php echo $validator->buildJS('#contact-form', true, true) ?>
				<div class="cl">&nbsp;</div>
			</div>
			<div class="contact-map">
				<div class="cols">
					<div class="col1">
						<p><?php echo nl2br(get_post_meta(get_the_id(), '_address', true)) ?></p>
					</div>
					<div class="col2">
						<strong>Phone:</strong> <?php echo get_post_meta(get_the_id(), '_phone', true); ?><br />
						<strong>Fax:</strong> <?php echo get_post_meta(get_the_id(), '_fax', true); ?><br />
						<strong>Email:</strong> <?php echo get_post_meta(get_the_id(), '_email', true); ?>
					</div>
					<div class="cl">&nbsp;</div>
				</div>
				<div class="map-holder">
					<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo geT_option('google_map_api_key'); ?>" type="text/javascript"></script>
					<div class="cl">&nbsp;</div>
					<div id="map" style="width: 410px; height: 310px; border: 0 none; overflow: hidden;"></div>
					<script type="text/javascript" charset="utf-8">
					    var map = new GMap2(document.getElementById("map"));
					    map.addControl(new GLargeMapControl());
					    map.addControl(new GMapTypeControl());
					    
				        map.setCenter(new GLatLng(<?php echo get_post_meta(get_the_id(), '_location', true); ?>), 10);
				        var marker = new GMarker(new GLatLng(<?php echo get_post_meta(get_the_id(), '_location', true); ?>), {'draggable': true});
				        marker.enableDragging();
				        GEvent.addListener(marker, 'dragend', change_coords);
				        map.addOverlay(marker);        
				     
					    
					   map.enableScrollWheelZoom();
					   map.disableDoubleClickZoom();
					    function change_coords(point) {
					        document.getElementById("map").value = point.lat() + "," + point.lng();
					    }
					    function set_coords(overlay, point) {
					       map.clearOverlays();
					        if (point) {
					            var marker = new GMarker(point, {'draggable': true});
					            marker.enableDragging();
					            GEvent.addListener(marker, 'dragend', change_coords);
					           map.addOverlay(marker);
					        }
					        change_coords(point);
					        return false;
					    }
					    GEvent.addListener(map, "dblclick", set_coords);
					</script>
				</div>
			</div>
			<div class="cl">&nbsp;</div>
		</div>
	</div>
<?php endif; get_footer(); ?>	
