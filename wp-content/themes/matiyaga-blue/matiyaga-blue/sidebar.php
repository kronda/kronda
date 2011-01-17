        		<?  
			  	$delicious_id = get_option('padd_delicious_id');
                $delicious_url = "http://www.delicious.com/" . $delicious_id; 
		
                $digg_id = get_option('padd_digg_id');
                $digg_url = "http://digg.com/users/" . $digg_id;
				
				$facebook_id = get_option('padd_facebook_id');
                $facebook_url = "http://www.facebook.com/" . $facebook_id;

                $stumbleupon_id = get_option('padd_stumbleupon_id');
                $stumbleupon_url = "http://" . $stumbleupon_id . ".stumbleupon.com/" ;
				                
	            $technorati_id = get_option('padd_technorati_id');
                $technorati_url = "http://technorati.com/people/technorati/" . $technorati_id;                 

				$twitter_id = get_option('padd_twitter_username');
                $twitter_url = "http://www.twitter.com/" . $twitter_id;
              		
			?>

<div id="sidebar">
	<div class="search-social">
			<div id="search">
				<form id="searchform" method="get" action="<?php bloginfo('home'); ?>/index.php?">
					<input type="text" name="s" id="s" size="15" value="Search" class="searchbox" />
                    
                    <input type="submit" name="3" id="3" value="" class="button"  />
				</form>				
			</div>    	
    </div>
	<div class="search-social">
<div id="socialbookmark">
                <div id="socialbookmark-wrapper">
                    <ul >
                        <li >
                        		<a href="<?php echo (!empty($delicious_id)) ? $delicious_url : '#'; ?>" class="bookmark" title="Delicious" target="_blank">
                                <img alt="Delicious Link" src="<?php echo get_bloginfo('template_url') . '/images/delicious-ico.png'; ?>" />
                                </a>
                        </li>
                          
                        <li>
                        		<a href="<?php echo (!empty($facebook_id)) ? $facebook_url : '#'; ?>" class="bookmark" title="Facebook" target="_blank">
                                <img alt="Facebook Link" src="<?php echo get_bloginfo('template_url') . '/images/fb-ico.png'; ?>" />
                                </a>
                        </li>
                        <li >
                        		<a href="<?php echo (!empty($digg_id)) ? $digg_url : '#'; ?>" class="bookmark" title="Digg" target="_blank">
                                <img alt="Digg Link" src="<?php echo get_bloginfo('template_url') . '/images/digg-ico.png'; ?>" />
                                </a>
                        </li>                             
						<li>
                        		<a href="<?php bloginfo('rss2_url'); ?>" class="bookmark" title="RSS Feeds" target="_blank">
                                <img alt="RSS Link" src="<?php echo get_bloginfo('template_url') . '/images/rss-ico.png'; ?>" />
                                </a>
                        </li>
                        <li >
                        		<a href="<?php echo (!empty($stumbleupon_id)) ? $stumbleupon_url : '#'; ?>" class="bookmark" title="StumbleUpon" target="_blank">
                                <img alt="StumbleUpon Link" src="<?php echo get_bloginfo('template_url') . '/images/su-ico.png'; ?>" />
                                 </a>
                        </li>
                        <li >
                        		<a href="<?php echo (!empty($technorati_id)) ? $technorati_url : '#'; ?>" class="bookmark" title="Technorati" target="_blank">
                                <img alt="Technorati Link" src="<?php echo get_bloginfo('template_url') . '/images/techno-ico.png'; ?>" />
                                </a>
                        </li>                        
                        <li >
                        		<a href="<?php echo (!empty($twitter_id)) ? $twitter_url : '#'; ?>" class="bookmark" title="Twitter" target="_blank">
                                <img alt="Twitter Link" src="<?php echo get_bloginfo('template_url') . '/images/twit-ico.png'; ?>" />
                                </a>
                        </li>						
                    
                    </ul>
                </div>
            </div>      	
    </div>    
	<div class="box box-adverts">
		<h2>Sponsors</h2>
		<div class="interior">
				<!-- Your ads start -->
				<?php
					$banner_img = get_option('padd_banner_250_img_url');
					$banner_url = get_option('padd_banner_web_url');
					$defaut_url = "http://www.paddsolutions.com";
					$default = get_bloginfo('template_url') . '/images/padd250x250.jpg';
				?>
				<p class="banner" >
					<a href="<?php echo (!empty($banner_url)) ? $banner_url : $defaut_url ; ?>" >
						<img alt="Advertisement" src="<?php echo (!empty($banner_img)) ? $banner_img : $default; ?>" />
					</a>
				</p>
				<!-- Your ads end -->
		
			<div class="row row-1">
				<?php
					$default = get_bloginfo('template_url') . '/images/padd125x125.png';
					$defaut_url = "http://www.paddsolutions.com";
					$sqbtn_1_img = get_option('padd_sqbtn_1_img_url');
					$sqbtn_1_url = get_option('padd_sqbtn_1_web_url');
					$sqbtn_2_img = get_option('padd_sqbtn_2_img_url');
					$sqbtn_2_url = get_option('padd_sqbtn_2_web_url');
				?>
				<a class="ads-1 ads-l" href="<?php echo (!empty($sqbtn_1_url)) ? $sqbtn_1_url : $defaut_url; ?>"><img alt="Advertisement" src="<?php echo (!empty($sqbtn_1_img)) ? $sqbtn_1_img : $default; ?>" /></a>
				<a class="ads-2 ads-r" href="<?php echo (!empty($sqbtn_2_url)) ? $sqbtn_2_url : $defaut_url; ?>"><img alt="Advertisement" src="<?php echo (!empty($sqbtn_2_img)) ? $sqbtn_2_img : $default; ?>" /></a>
			</div>
			<div class="row row-2">
				<?php
					$sqbtn_3_img = get_option('padd_sqbtn_3_img_url');
					$sqbtn_3_url = get_option('padd_sqbtn_3_web_url');
					$sqbtn_4_img = get_option('padd_sqbtn_4_img_url');
					$sqbtn_4_url = get_option('padd_sqbtn_4_web_url');
				?>
				<a class="ads-3 ads-l" href="<?php echo (!empty($sqbtn_3_url)) ? $sqbtn_3_url : $defaut_url; ?>"><img alt="Advertisement" src="<?php echo (!empty($sqbtn_3_img)) ? $sqbtn_3_img : $default; ?>" /></a>
				<a class="ads-4 ads-r" href="<?php echo (!empty($sqbtn_4_url)) ? $sqbtn_4_url : $defaut_url; ?>"><img alt="Advertisement" src="<?php echo (!empty($sqbtn_4_img)) ? $sqbtn_4_img : $default; ?>" /></a>
			</div>
		</div>
        <div class="bottom"> </div>
	</div>
    
    <?php 
		$twitter_username = get_option('padd_twitter_username');
		if (!empty($twitter_username)) {

	?>
	<div class="box box-twitter">
		<h2>Follow my <a href="http://twitter.com/<?php echo $twitter_username; ?>" title="Twitter">Tweets</a></h2>
		<div class="interior">
			<?php echo themefunction_twitter_get_recent_entries($twitter_username); ?>
		</div>
        <div class="bottom"> </div>
	</div>
	<?php 
		}
	?>
    
    
	<?php 
		$youtube_code = get_option('padd_youtube_code');
		if (!empty($youtube_code)) {
	?>
	<div class="box box-video">
		<h2>Featured Video</h2>
		<div class="interior">
			<?php echo stripslashes($youtube_code); ?>
			<div class="clearer"></div>
		</div>
         <div class="bottom"> </div>
	</div>

	<div class="box box-flickr">
		<h2>Featured Photos</h2>
        
		<div class="interior">
			<?php get_flickrRSS(); ?>
			<div class="clearer"></div>
         
		</div>
        <div class="bottom"></div>
	</div>
	<div class="box ">
		<h2>Featured Links</h2>
		<div class="interior">
        <ul>
			<?php wp_list_bookmarks('title_li=&categorize=0'); ?>
		</ul>

		</div>
	 <div class="bottom"> </div>
	</div>     
	<?php } ?>    
	<?php 
		if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Side Bar') ) { 
		
		 echo "<div class=\"bottom\"> </div>";
		}
	?>
	
</div>
