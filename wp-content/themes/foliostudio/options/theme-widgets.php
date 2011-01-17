<?php
/*
* Register the new widget classes here so that they show up in the widget list
*/
function load_widgets() {
    register_widget('LatestTweets');
    register_widget('FolioArchiveWidget');
    register_widget('SocialMediaWidget');
    register_widget('TestimonialWidget');
    register_widget('BannerAds');
    register_widget('MostPopularPosts');
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
        $this->WP_Widget('theme-widget-latest-tweets', 'Latest Tweets', $widget_opts);
        $this->custom_fields = array(
        	array(
		        'name'=>'title',
        		'type'=>'text',
        		'title'=>'Title',
        		'default'=>'Live Tweets'
        	),
        	array(
		        'name'=>'username',
        		'type'=>'text',
        		'title'=>'Username',
        		'default'=>'cnn'
        	),
        	array(
		        'name'=>'count',
        		'type'=>'text',
        		'title'=>'Number of Tweets to show',
        		'default'=>'3'
        	),
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
    	extract($args);
    	$tweets = TwitterHelper::get_tweets($instance['username'], $instance['count']);
    	$tweet_count = 0;
        ?>
		<div class="tweets">
			<h3><?php echo $instance['title']; ?> <a href="http://twitter.com/<?php echo $instance['username']; ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/live-tweet<?php if(get_option('color_scheme') == 'dark') echo '_dark' ?>.jpg" alt="" /></a></h3>
			<div class="side-box">
				<div class="top">
					<div class="bottom">
			        	<?php foreach ($tweets as $tweet): $tweet_count++; ?>
							<div class="tweet <?php echo ($tweet_count == 1) ? ' first-tweet ' : ''; echo ($tweet_count == count($tweets)) ? ' last-tweet' : ''; ?>">
								<?php echo $tweet->tweet_text; ?>
								<br />
								<a href="<?php echo $tweet->tweet_link; ?>"><?php echo date('h:i A M jS', $tweet->timestamp); ?></a>
							</div>
			        	<?php endforeach ?>											
					</div>
				</div>
			</div>
		</div>
        <?php
    }
}


class FolioArchiveWidget extends ThemeWidgetBase {
	/*
	* Register widget function. Must have the same name as the class
	*/
    function FolioArchiveWidget() {
        $widget_opts = array(
	        'classname' => 'theme-widget-archive', // class of the <li> holder
            'description' => __( 'Displays custom monthly archive of your site’s posts ' ) // description shown in the widget list
        );
        // Additional control options. Width specifies to what width should the widget expand when opened
        $control_ops = array(
        	//'width' => 350,
        );
        // widget id, widget display title, widget options
        $this->WP_Widget('theme-widget-archive', 'Theme Widget - Archive', $widget_opts, $control_ops);
        $this->custom_fields = array(
        	array(
		        'name'=>'title', // field name
        		'type'=>'text', // field type (text, textarea, integer etc.)
        		'title'=>'Title', // title displayed in the widget form
        		'default'=>'Archive' // default value
        	)
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
    	extract($args);
		echo $before_title . $instance['title'] . $after_title;
		
		$archive_tree = $this->get_archive_tree();
		$month_names = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "Novemeber", "December");
        ?>
        <ul>
        	<?php foreach ($archive_tree as $year => $months): ?>
        		<?php foreach ($months as $month => $posts): ?>
        			<li><a href="<?php echo get_month_link($year, $month) ?>"><?php echo $month_names[intval($month)-1] ?> <span>(<?php echo $posts ?>)</span></a></li>
        		<?php endforeach ?>
        	<?php endforeach ?>
		</ul>
		<a class="btn-archive notext" href="<?php echo get_permalink(geT_page_id_by_path('archive')) ?>">all archive</a>
        <?php
    }
    
    function get_archive_tree() {
    	global $wpdb;
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'wp_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'wp_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		
		$archive_tree = array();
		
		if ( $arcresults ) {
			foreach ( (array) $arcresults as $arcresult ) {
				$archive_tree[$arcresult->year][$arcresult->month] = $arcresult->posts;
			}
		}
		return $archive_tree;
    }
}


class TestimonialWidget extends ThemeWidgetBase {
	/*
	* Register widget function. Must have the same name as the class
	*/
    function TestimonialWidget() {
        $widget_opts = array(
	        'classname' => 'theme-widget-testimonial', // class of the <li> holder
            'description' => __( 'Displays testimonial' ) // description shown in the widget list
        );
        // Additional control options. Width specifies to what width should the widget expand when opened
        $control_ops = array(
        	//'width' => 350,
        );
        // widget id, widget display title, widget options
        $this->WP_Widget('theme-widget-testimonial', 'Theme Widget - Testimonial', $widget_opts, $control_ops);
        $this->custom_fields = array(
        	array(
		        'name'=>'title', // field name
        		'type'=>'text', // field type (text, textarea, integer etc.)
        		'title'=>'Title', // title displayed in the widget form
        		'default'=>'Testimonials' // default value
        	),
        	array(
		        'name'=>'content', // field name
        		'type'=>'textarea', // field type (text, textarea, integer etc.)
        		'title'=>'Content', // title displayed in the widget form
        		'default'=>'' // default value
        	),
        	array(
        		'name'=>'name',
        		'type'=>'text',
        		'title'=>'Author Name', 
        		'default'=>''
        	),
        	array(
        		'name'=>'position',
        		'type'=>'text',
        		'title'=>'Author Position', 
        		'default'=>''
        	),
        	array(
        		'name'=>'company',
        		'type'=>'text',
        		'title'=>'Company', 
        		'default'=>''
        	),
        	array(
        		'name'=>'url',
        		'type'=>'text',
        		'title'=>'Company Url', 
        		'default'=>''
        	),
        	array(
        		'name'=>'logo',
        		'type'=>'text',
        		'title'=>'Company Logo Url', 
        		'default'=>''
        	),
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
    	extract($args);
		echo $before_title . $instance['title'] . $after_title;
        ?>
        <div class="testimonial">
    		<div class="testimonial-top">
    			<div class="testimonial-bottom">
    				<div class="testimonial-text">
    					<p><?php echo $instance['content'] ?>”</p>
    				</div>
    				<div class="testimonial-author">
    					<?php if ( !empty($instance['logo']) ): ?>
	    					<div class="testimonial-author-image">
	    						<img alt="" src="<?php echo $instance['logo'] ?>">
	    					</div>
    					<?php endif ?>
    					<span><strong><?php echo $instance['name'] ?></strong><?php if(!empty($instance['position'])) echo ', ' . $instance['position']; ?>
    						<?php if ( !empty($instance['company']) ): ?>
	    						<?php if ( !empty($instance['url']) ): ?>
	    							<a href="<?php echo $instance['url'] ?>"><?php echo $instance['company'] ?></a>
	    						<?php else: ?>
	    							<?php echo $instance['company'] ?>
	    						<?php endif ?>
    						<?php endif ?>
						</span>
    					<div class="cl">&nbsp;</div>
    				</div>
    			</div>
    		</div>
    	</div>
        <?php
    }
}


class SocialMediaWidget extends ThemeWidgetBase {
	/*
	* Register widget function. Must have the same name as the class
	*/
    function SocialMediaWidget() {
        $widget_opts = array(
	        'classname' => 'theme-widget-social-media', // class of the <li> holder
            'description' => __( 'Displays links to popular social sites' ) // description shown in the widget list
        );
        // Additional control options. Width specifies to what width should the widget expand when opened
        $control_ops = array(
        	//'width' => 350,
        );
        // widget id, widget display title, widget options
        $this->WP_Widget('theme-widget-social-media', 'Theme Widget - Social Media', $widget_opts, $control_ops);
        $this->custom_fields = array(
        	array(
		        'name'=>'title', // field name
        		'type'=>'text', // field type (text, textarea, integer etc.)
        		'title'=>'Title', // title displayed in the widget form
        		'default'=>'Stay Connected' // default value
        	),
        	array(
		        'name'=>'twitter', // field name
        		'type'=>'text', // field type (text, textarea, integer etc.)
        		'title'=>'Twitter', // title displayed in the widget form
        		'default'=>'' // default value
        	),
        	array(
        		'name'=>'facebook',
        		'type'=>'text',
        		'title'=>'Facebook', 
        		'default'=>''
        	),
        	array(
        		'name'=>'linkedin',
        		'type'=>'text',
        		'title'=>'LinkedIn', 
        		'default'=>''
        	),
        	array(
        		'name'=>'subscription',
        		'type'=>'select',
        		'title'=>'Show RSS link', 
        		'default'=>'y',
        		'options' => array('y' => 'Yes', 'n' => 'No')
        	),
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
    	extract($args);
		echo $before_title . $instance['title'] . $after_title;
		
		$fields = array('Twitter', 'Facebook', 'LinkedIn');
        ?>
        <div class="social">
        	<?php foreach ($fields as $field_name): if( empty($instance[strtolower($field_name)]) ) continue; ?>
    		<div class="cl">&nbsp;</div>
    		<a class="icon <?php echo strtolower($field_name) ?>" href="#"><?php echo $field_name ?></a>
    		<p>Follow our <a href="<?php echo $instance[strtolower($field_name)] ?>"><?php echo $field_name ?></a></p>
        	<?php endforeach ?>
        	<?php if ( $instance['subscription'] != 'n' ): ?>
    		<div class="cl">&nbsp;</div>
    		<a class="icon rss" href="<?php bloginfo('rss2_url'); ?>">RSS</a>
    		<p>Subscribe our <a href="<?php bloginfo('rss2_url'); ?>">Blog</a></p>
        	<?php endif ?>
    		<div class="cl">&nbsp;</div>
    	</div>
        <?php
    }
}

/*
* An example widget
*/
class BannerAds extends ThemeWidgetBase {
	/*
	* Register widget function. Must have the same name as the class
	*/
    function BannerAds() {
        $widget_opts = array(
	        'classname' => 'theme-widget', // class of the <li> holder
            'description' => __( 'Displays up to 4 banner ads' ) // description shown in the widget list
        );
        // Additional control options. Width specifies to what width should the widget expand when opened
        $control_ops = array(
        	//'width' => 350,
        );
        // widget id, widget display title, widget options
        $this->WP_Widget('theme-widget-banner-ads', 'Theme Widget - Banner Ads', $widget_opts, $control_ops);
        for($i = 1; $i <= 4; $i++) {
        	$this->custom_fields[] = array(
		        'name'=>'banner_' . $i . '_img', // field name
        		'type'=>'text', // field type (text, textarea, integer etc.)
        		'title'=>'Banner ' . $i . ' Image', // title displayed in the widget form
        		'default'=> get_bloginfo('stylesheet_directory') . '/images/banner125x125.jpg' // default value
        	);
        	$this->custom_fields[] = array(
		        'name'=>'banner_' . $i . '_link', // field name
        		'type'=>'text', // field type (text, textarea, integer etc.)
        		'title'=>'Banner ' . $i . ' Link', // title displayed in the widget form
        		'default'=>'#' // default value
        	);
        }
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
        ?>
        <div class="cl">&nbsp;</div>
		<div class="ad-baner">
			<ul>
				<?php $loopID = 0; ?>
				<?php for($i = 1; $i <= 4; $i++ ):
						$image = $instance['banner_' . $i . '_img'];
						$link = $instance['banner_' . $i . '_link'];
						?>
						<?php if ($image && $link): $loopID++;?>
							<li <?php echo ($loopID % 2 == 0) ? 'class="last"' : ''; ?>>
								<a href="<?php echo $link; ?>"><img src="<?php echo $image; ?>" alt="" width="125" height="125" /></a>
							</li>							
						<?php endif ?>

				<?php endfor; ?>
			</ul>
			<div class="cl">&nbsp;</div>
		</div>
		<div class="cl">&nbsp;</div>
        <?php
    }
}

class MostPopularPosts extends ThemeWidgetBase {
    function MostPopularPosts() {
        $widget_opts = array(
	        'classname' => 'theme-widget',
            'description' => 'Displays most popular posts'
        );
        $this->WP_Widget('theme-widget-popular-posts', 'Most Popular Posts', $widget_opts);
        $this->custom_fields = array(
        	array(
		        'name'=>'title',
        		'type'=>'text',
        		'title'=>'Title',
        		'default'=>'Popular Posts'
        	),
        	array(
		        'name'=>'count',
        		'type'=>'text',
        		'title'=>'Number of posts',
        		'default'=>'4'
        	),
        );
    }
    
    /*
	* Called when rendering the widget in the front-end
	*/
    function front_end($args, $instance) {
    	global $wpdb;
        ?>
		<div class="popular-posts">
			<h3><?php echo $instance['title']; ?></h3>
			<?php $popular_posts = $wpdb->get_results("SELECT id,post_title,post_content,post_status FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND `post_type`='post' ORDER BY comment_count DESC LIMIT 0," . $instance['count']); ?>
			<ul>
				<?php if ($popular_posts): $c = 0; ?>
					<?php foreach ($popular_posts as $p): $c++; ?>
					    <li <?php echo ($c == 1) ? 'class="first"' : ''; ?>>
					    	<img src="<?php bloginfo('stylesheet_directory'); ?>/images/popular-post-image.jpg" alt="" class="left"/>
					    	<h2><a href="<?php echo get_permalink($p->ID); ?>"><?php echo $p->post_title; ?></a></h2>
					    	<p>
					    		<?php echo shortalize($p->post_content, 10); ?>
					    	</p>
					    	<div class="cl">&nbsp;</div>
					    </li>			
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>        
        <?php
    }
}

?>