<?php
/*
* Register the new widget classes here so that they show up in the widget list
*/
function load_widgets() {
    register_widget('LatestTweets');
    register_widget('NewsletterContact');
    register_widget('ApolloLatestPosts');
    register_widget('ApolloRecentComments');
    register_widget('ApolloFlickrGallery');
    register_widget('ApolloAboutUs');
    register_widget('ApolloTestimonials');
    // register_widget('ThemeWidgetExample');
}
add_action('widgets_init', 'load_widgets');

/*
* Displays a block with latest tweets from particular user
*/
class LatestTweets extends ThemeWidgetBase {
    function LatestTweets() {
        $widget_opts = array(
	        'classname' => 'theme-widget',
            'description' => 'Displays a block with your latest tweets'
        );
        $this->WP_Widget('theme-widget-latest-tweets', 'Apollo - Latest Tweets', $widget_opts);
        $this->custom_fields = array(
        	array(
		        'name'=>'title',
        		'type'=>'text',
        		'title'=>'Title',
        		'default'=>'Twitter Updates'
        	),
        	array(
		        'name'=>'username',
        		'type'=>'text',
        		'title'=>'Username',
        		'default'=>''
        	),
        	array(
		        'name'=>'count',
        		'type'=>'text',
        		'title'=>'Number of Tweets to show',
        		'default'=>'4'
        	),
        	array(
		        'name'=>'follow_me_text',
        		'type'=>'text',
        		'title'=>'"Follow Me"" text',
        		'default'=>'Follow Me On Twitter!'
        	),
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
    	extract($args);
    	$tweets = TwitterHelper::get_tweets($instance['username'], $instance['count']);
    	if (empty($tweets)) {
    		return;
    	}
		if ($instance['title'])
			echo $before_title . $instance['title'] . $after_title;
        ?>
    	<div class="twitter-updates">
	        <ul>
	        	<?php foreach ($tweets as $tweet): ?>
	        		<li>
	        			<p><?php echo $tweet->tweet_text ?></p>
	        			<span><?php echo $tweet->time_distance ?> ago</span>
	    			</li>
	        	<?php endforeach ?>
	        </ul>
    		<p class="ar"><a href="http://twitter.com/<?php echo $instance['username'] ?>"><?php echo $instance['follow_me_text'] ?></a></p>
        </div>
        <?php
    }
}

class NewsletterContact extends ThemeWidgetBase {
	function NewsletterContact() {
        $widget_opts = array(
	        'classname' => 'wide-widget',
            'description' => 'Newsletter & Quick Connect forms'
        );
        $this->WP_Widget('theme-widget-newsletter-contact', 'NewsletterContact', $widget_opts);
        $this->custom_fields = array(
			array(
			    'name'=>'newsletter-title',
				'type'=>'text',
				'title'=>'Newsletter Title',
				'default'=>'Join the Newsletter!'
			),
			array(
			    'name'=>'contact-title',
				'type'=>'text',
				'title'=>'Quick Contact Title',
				'default'=>'Quick Contact'
			),
        );
	}
    function front_end($args, $instance) {
    	handle_newsletter_contact();
    	$fb_form = get_option('feedburner_form');
		?>
		<h3><?php echo $instance['newsletter-title'] ?></h3>
		<div class="newsletter">
			<?php if ( empty($fb_form) ): ?>
			<form action="" method="post" class="apollo-newsletter-form">
				<fieldset>
					<div class="field"><input type="text" name="nl_email" class="blink email" title="Your Email Address"  value="Your Email Address" /></div>
					<input type="submit" class="submit" value="Join" name="nl_submit" />
				</fieldset>
			</form>
			<?php else: ?>
				<?php echo $fb_form ?>
				<script type="text/javascript" charset="utf-8">
					jQuery(function($) {
						$('.newsletter form').attr('style', '');
						$('.newsletter form p:first, .newsletter form p:last').remove();
						$('.newsletter form p').css('display', 'inline').addClass('field');
						$('.newsletter form p input').addClass('blink email').attr('title', 'Your Email Address').css('width', '160px').val('Your Email Address');
						$('.newsletter form input[type=submit]').addClass('submit').val('JOIN');
					});
				</script>
			<?php endif ?>
		</div>
		
		<h3><?php echo $instance['contact-title'] ?></h3>
		
		<div class="quick-contact">
			<form action="" method="post" class="apollo-contact-form">
				<fieldset>
					<div class="field fl"><input type="text" name="c_name" class="blink name" title="Your Name"  value="Your Name" /></div>
					<div class="field fr"><input type="text" name="c_email" class="blink email" title="Your Email"  value="Your Email" /></div>
					<div class="cl">&nbsp;</div>
					<textarea cols="30" rows="5" class="blink message" title="Your Message" name="c_message">Your Message</textarea>						
					<input type="submit" class="submit" value="Send" name="c_submit" />
				</fieldset>
			</form>
		</div>
        <?php
    }
}

class ApolloAboutUs extends ThemeWidgetBase {
	function ApolloAboutUs() {
        $widget_opts = array(
	        'classname' => 'wide-widget',
            'description' => 'About Us area'
        );
        $this->WP_Widget('theme-widget-about-us', 'Apollo - About Us', $widget_opts);
        $this->custom_fields = array(
			array(
			    'name'=>'title',
				'type'=>'text',
				'title'=>'Title',
				'default'=>'About Us'
			),
			array(
			    'name'=>'img_url',
				'type'=>'text',
				'title'=>'Image url',
				'default'=> get_bloginfo('stylesheet_directory'). '/images/about-img.jpg'
			),
			array(
			    'name'=>'img_href',
				'type'=>'text',
				'title'=>'Image link',
				'default'=> '#'
			),
			array(
			    'name'=>'text',
				'type'=>'textarea',
				'title'=>'Description',
				'default'=>'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.'
			),
        );
	}
    function front_end($args, $instance) {
    	extract($args);
    	echo $before_title . $instance['title'] . $after_title;
		?>
		<div class="about-us-widget-content">
			<a href="<?php echo $instance['img_href'] ?>" class="img"><img alt="" src="<?php echo $instance['img_url'] ?>"/></a>
			<div class="text"><?php echo $instance['text'] ?></div>
		</div>
        <?php
    }
}

class ApolloLatestPosts extends ThemeWidgetBase {
    function ApolloLatestPosts() {
        $widget_opts = array(
	        'classname' => 'theme-widget',
            'description' => 'Displays recent posts'
        );
        $this->WP_Widget('theme-widget-latest-posts', 'Apollo - Recent Posts', $widget_opts);
        $this->custom_fields = array(
        	array(
		        'name'=>'title',
        		'type'=>'text',
        		'title'=>'Title',
        		'default'=>'Recent Posts'
        	),
        	array(
		        'name'=>'count',
        		'type'=>'text',
        		'title'=>'Number of Posts to show',
        		'default'=>'5'
        	),
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
    	extract($args);
    	$posts = get_posts('numberposts=' . $instance['count'] . '&order=ASC');
    	$posts_count = count($posts);
    	echo $before_title . $instance['title'] . $after_title;
        ?>
        <div class="recent-posts">
	        <ul>
	        	<?php $i=0; foreach ($posts as $post): $i++; ?>
			    <li <?php if( $i == $posts_count) { echo 'class="last"'; } ?>>
					<?php  
					$image = get_post_meta($post->ID, 'custom_post_image_square', true);
					if ( empty($image) ) {
						$image = get_bloginfo('stylesheet_directory') . '/images/recent-post-img1.gif';
					} else {
						$image = get_upload_url() . '/' . $image;
					}
					?>
			    	<a href="<?php echo get_permalink($post->ID) ?>" class="img"><img src="<?php echo $image ?>" alt="" width="60"/></a>
			    	<div class="hld">
			    		<h4><a href="<?php echo get_permalink($post->ID) ?>"><?php echo get_the_title($post->ID) ?></a></h4>
			    		<a href="<?php echo get_permalink($post->ID) ?>#comments">
			    			<?php if ( $post->comment_count == '0'):  ?>
			    			No Comments
			    			<?php elseif($post->comment_count == '1'): ?>
			    			1 Comment
			    			<?php else: ?>
			    			<?php echo $post->comment_count ?> Comments
			    			<?php endif; ?>
			    		</a>
			    	</div>
			    	<div class="cl">&nbsp;</div>
			    </li>
	        	<?php endforeach ?>
	        </ul>
        </div>
        <?php
    }
}

class ApolloTestimonials extends ThemeWidgetBase {
    function ApolloTestimonials() {
        $widget_opts = array(
	        'classname' => 'testimonials-widget',
            'description' => 'Displays testimonials'
        );
        $this->WP_Widget('theme-widget-testimonials', 'Apollo - Testimonials', $widget_opts);
        $this->custom_fields = array(
        	array(
		        'name'=>'title',
        		'type'=>'text',
        		'title'=>'Title',
        		'default'=>'Testimonials'
        	),
        	array(
		        'name'=>'contents',
        		'type'=>'textarea',
        		'title'=>'Testimonials<br/><small>separated by [break]</small>',
        		'default'=>"Lorem ipsum dolor sit amet, consectetur\n[break]\nSed ut perspiciatis unde omnis iste natus"
        	),
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
    	extract($args);
    	echo $before_title . $instance['title'] . $after_title;
    	$testimonials = explode('[break]', $instance['contents']);
    	
    	$count = count($testimonials)-1;
    	foreach ($testimonials as $key => $value) {
    		if (strpos($value, '<p>') === FALSE) {
    			$testimonials[$key] = '<p>' . $value . '</p>';
    		}
    	}
        foreach ($testimonials as $key => $value): $last = $count==$key; ?>
			<div class="testimonial <?php if($last) echo 'testimonial-last'; ?>">
				<?php echo $value ?>	
			</div>
		<?php endforeach;
    }
}

class ApolloRecentComments extends ThemeWidgetBase {
    function ApolloRecentComments() {
        $widget_opts = array(
	        'classname' => 'theme-widget',
            'description' => 'Displays recent comments'
        );
        $this->WP_Widget('theme-widget-recent-comments', 'Apollo - Recent Comments', $widget_opts);
        $this->custom_fields = array(
        	array(
		        'name'=>'title',
        		'type'=>'text',
        		'title'=>'Title',
        		'default'=>'Recent Comments'
        	),
        	array(
		        'name'=>'count',
        		'type'=>'text',
        		'title'=>'Number of Comments to show',
        		'default'=>'5'
        	),
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
    	extract($args);
    	$comments = get_comments('number=' . $instance['count']);
    	echo $before_title . $instance['title'] . $after_title;
        ?>
        <div class="recent-comments">
		<?php foreach ($comments as $comment): ?>
    		<div class="comment">
    			<div class="box">
	    			<div class="bottom">
	    				<div class="top">
	    					<p><?php echo $comment->comment_content ?></p>
	    				</div>
	    			</div>
	    		</div>	    	
	    		<span><?php echo $comment->comment_author ?></span>					
    		</div>
		<?php endforeach ?>
        </div>
        <?php
    }
}

class ApolloFlickrGallery extends ThemeWidgetBase {
    function ApolloFlickrGallery() {
        $widget_opts = array(
	        'classname' => 'theme-widget',
            'description' => 'Flickr Gallery'
        );
        $this->WP_Widget('theme-widget-flickr-gallery', 'Apollo - Flickr Gallery', $widget_opts);
        $this->custom_fields = array(
        	array(
		        'name'=>'title',
        		'type'=>'text',
        		'title'=>'Title',
        		'default'=>'Flickr Gallery'
        	),
        	array(
		        'name'=>'user_id',
        		'type'=>'text',
        		'title'=>'User ID',
        		'default'=>'78656712@N00'
        	),
        	array(
		        'name'=>'tags',
        		'type'=>'text',
        		'title'=>'Tags<br/><small>comma separated</small>',
        		'default'=>''
        	),
        	array(
		        'name'=>'num_rows',
        		'type'=>'text',
        		'title'=>'Number of rows to show',
        		'default'=>'2'
        	),
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
    	extract($args);
	    echo $before_widget;
	    echo $before_title. $instance['title']. $after_title;
	    //apollo_show_flickr_feed_gallery();
	    
	    $gallery = new Apollo_Flickr_Feed_Gallery();
	    $options = $gallery->get_flickr_feed_options();
	    $options->user_id = $instance['user_id'];
	    $options->image_limit = $instance['num_rows'] *3;
	    $options->tags = $instance['tags'];
	    
	    $url = $gallery->get_flickr_json_url($options);
	    $widget_id_safe = str_replace('-', '_', $widget_id);
		?>
		<div class="flickr-gallery"><div class="cl">&nbsp;</div><span id='apollo_ffg_marker_span' style='display:none;'></span>
			<script> 
			var apollo_ffg_options_<?php echo $widget_id_safe ?> = ['<?php echo $gallery->get_flickr_json_url($options) ?>','_s','<?php echo $options->image_limit ?>']; 
			(function() {
				jQuery(document).ready(apollo_ffg_show_image_content);
			})();
			function apollo_ffg_show_image_content() {
				jQuery.getJSON(apollo_ffg_options_<?php echo $widget_id_safe ?>[0],
					function(result) {
						jQuery.each(result.items,
						function(i,item) {
							apollo_ffg_append_img_content(i,item);
							if(i+1>=apollo_ffg_options_<?php echo $widget_id_safe ?>[2]) return false;
						})
					});
			}
			
			function apollo_ffg_append_img_content(i,item) {
				var img_url = item.media.m;
				var iu = img_url.replace("_m",apollo_ffg_options_<?php echo $widget_id_safe ?>[1]);
				var middle_class = (i%3 == 1)? 'middle' : '' ;
				var content = '<a title="'+item.title+'" href="'+item.link+'" class="img fl ' + middle_class + '" id="ffg_link_'+i+'">'
				content = content+ '<img alt="" class="ffg_img_css" id="ffg_img_'+i+'"'+' src="'+iu+'">';
				content = content + '</a>';
				if ( i%3 == 0) { content = content + '<div class="cl">&nbsp</div'; };
				jQuery(content).insertAfter('#<?php echo $widget_id ?> #apollo_ffg_marker_span');
			}
			</script>
		</div>
	    <?php
	    echo $after_widget;
    }
}

/*
* An example widget
*/
class ThemeWidgetExample extends ThemeWidgetBase {
	/*
	* Register widget function. Must have the same name as the class
	*/
    function ThemeWidgetExample() {
        $widget_opts = array(
	        'classname' => 'theme-widget', // class of the <li> holder
            'description' => __( 'Displays a block with title/text' ) // description shown in the widget list
        );
        // Additional control options. Width specifies to what width should the widget expand when opened
        $control_ops = array(
        	//'width' => 350,
        );
        // widget id, widget display title, widget options
        $this->WP_Widget('theme-widget-example', 'Theme Widget - Example', $widget_opts, $control_ops);
        $this->custom_fields = array(
        	array(
		        'name'=>'title', // field name
        		'type'=>'text', // field type (text, textarea, integer etc.)
        		'title'=>'Title', // title displayed in the widget form
        		'default'=>'Hello World!' // default value
        	),
        	array(
        		'name'=>'text',
        		'type'=>'textarea',
        		'title'=>'Content', 
        		'default'=>'Lorem Ipsum dolor sit amet'
        	),
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
        ?>
        <div class="cl">&nbsp;</div>
        <div class="widget-contact">
			<h3><?php echo $instance['title'];?></h3>
			<p><?php echo $instance['text'];?></p>
		</div>
		<div class="cl">&nbsp;</div>
        <?php
    }
}

