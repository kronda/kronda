<?php
/*
Plugin Name: Wordpress Tabs Slides
Plugin URI: http://ibad.bebasbelanja.com/wordpress-tabs-slides.html
Description: Wordpress Tabs Slides is plugin based on "<a href="http://www.joomlaworks.gr/">joomlaworks Tabs & Slides Mambots</a>" for Mambo/Joomla. Tabs and Slides (in content items) Plugin gives you the ability to easily add content tabs and/or content slides. The tabs emulate a multi-page structure, while the slides emulate an accordion-like structure, inside a single page!
Version: 1.9
Author: Abdul Ibad
Author URI: http://ibad.bebasbelanja.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
	
*/

// Show tags html on title, 
// REPLACE = < will be replace with &lt;
// STRIP = Strip html tags
// NOFILTER = Don't filter (Not Recommend) 

define('SHOW_TITLE_HTML','REPLACE');

// The Activation
function wp_tabsSlides_activation(){
	$options['slide-speed'] =  600;
	$options['optimized'] = "on";
	$options['frontenable'] = "on";
	add_option("wp_tabs_slides",$options);
	}
	
function wp_tabsSlides_filter_title($text)
{

	switch(SHOW_TITLE_HTML){
		case 'REPLACE':
			$text = str_replace('<','&lt;',$text);
		break;
		case 'STRIP':
			$text = strip_tags($text);
		break;
		case 'NOFILTER':
			$text = $text;
		break;
	}

	return $text;
}
	
function wp_tabsSlides_strip_punctuation( $text )
{
	    $text = strip_tags($text);
		$text = ereg_replace("[^A-Za-z0-9]", "", $text );
	    return preg_replace("/[^A-Za-z0-9\s\s+\.\:\-\/%+\(\)\*\&\$\#\!\@\"\';\n\t\r\~]/","",$text);
}	

// Custom Head
function wp_tabsSlides_customhead(){	
	
	/* Get all Options */
	$dir = str_replace('\\','/',dirname(__FILE__));
	$home = get_option('siteurl');
	$start = strpos($dir,'/wp-content/');
	$end = strlen($dir);
	$plugin_url = $home.substr($dir,$start,$end);
	$options = get_option('wp_tabs_slides');
	$optimized = $options['optimized'];
	/* End get options */


	if(is_front_page() || is_home()){
		return;
	}
	
	
	wp_enqueue_script('jquery');
?>	
<link rel="stylesheet" type="text/css" href="<?php echo $plugin_url ;?>/ts/tabs_slides.css" />
<style type="text/css" media="print">.jwts_tabbernav{display:none;}</style>
<script type="text/javascript" src="<?php echo $plugin_url ;?>/ts/tabs_slides.js"></script>
<?php

	$use_optimized_loader = ($optimized=="on") ? true:false;
	
	if($use_optimized_loader) {
		$header = '<script type="text/javascript" src="'.$plugin_url.'/ts/tabs_slides_opt_loader.js"></script>';
	} else {
		$header = '<script type="text/javascript" src="'.$plugin_url.'/ts/tabs_slides_def_loader.js"></script>';
	}
		
		echo $header;	
	
}

	// the content filter
	function wp_tabsSlides( $content ){
		global $post,$slidesspeed,$wptabs_detector;
		
	// if post empty (check from the title) then return false
	if(empty($post->post_title)){
		return $content;
	}
				
	$b=1;
   if (preg_match_all("/{tab=.+?}{tab=.+?}|{tab=.+?}|{\/tabs}/", $content, $matches, PREG_PATTERN_ORDER) > 0) { 	
    foreach ($matches[0] as $match) {	
      if($b==1 && $match!="{/tabs}") {
    	$tabs[] = 1;
    	$b=2;
      }
      elseif($match=="{/tabs}"){
      	$tabs[]=3;
      	$b=1;
      }
      elseif(preg_match("/{tab=.+?}{tab=.+?}/", $match)){
      	$tabs[]=2;
      	$tabs[]=1;
      	$b=2;
      }
      else {
      	$tabs[]=2;
      }
    }
   }
   @reset($tabs);
   $tabscount = 0;
  if (preg_match_all("/{tab=.+?}|{\/tabs}/", $content, $matches, PREG_PATTERN_ORDER) > 0) {
    foreach ($matches[0] as $match) {
      if($tabs[$tabscount]==1) {
      	$match = str_replace("{tab=", "", $match);
        $match = str_replace("}", "", $match);
        $content = str_replace( "{tab=".$match."}", "
		<div class=\"jwts_tabber\" id=\"jwts_tab".$tabid."\"><div class=\"jwts_tabbertab\" title=\"".$match."\"><h2><a href=\"#".$match."\" name=\"advtab\">".$match."</a></h2>", $content );        
        $tabid++;
      } elseif($tabs[$tabscount]==2) {
      	$match = str_replace("{tab=", "", $match);
        $match = str_replace("}", "", $match);
      	$content = str_replace( "{tab=".$match."}", "</div><div class=\"jwts_tabbertab\" title=\"".$match."\"><h2><a href=\"#".$match."\">".$match."</a></h2>", $content );
      } elseif($tabs[$tabscount]==3) {
      	$content = str_replace( "{/tabs}", "</div></div><br class=\"jwts_clr\" />", $content );
      }
      $tabscount++;
    }   
	
	$copyright = true;
	  
  }    	
	$uniqueSlideID = 0;
	$uniqueToggleID = 0;
	
	$options = get_option('wp_tabs_slides');
	
	$sliderspeed = intval($options['slide-speed']);
	
	// if slider speed <= 0 than change speed to normal
	if($sliderspeed <= 0){
			$sliderspeed = '"normal"';
	}
	
	// Make toggle id more unique with post id
	$pid = "p".$post->ID;
	
 if (preg_match_all("/{slide=.+?}/", $content, $matches, PREG_PATTERN_ORDER) > 0) {
    foreach ($matches[0] as $match) {
      $match = str_replace("{slide=", "", $match);
      $match = str_replace("}", "", $match);
      $title =  wp_tabsSlides_filter_title($match);
      $link = wp_tabsSlides_strip_punctuation(str_replace(" ","-",strtolower($match)));
      
      $content = str_replace( "{slide=".$match."}", "<div class=\"wts_title\"><div class=\"wts_title_left\"><a id=\"".$link."\" href=\"javascript:void(null);\" title=\"Click to open!\" class=\"jtoggle\" onclick=\"wtsslide('#hideslide".$uniqueToggleID.$pid."',$sliderspeed);\">".$title."</a></div></div><div class=\"wts_slidewrapper sliderwrapper".$uniqueSlideID."\" id=\"hideslide".$uniqueSlideID.$pid."\">", $content );
      
      $content = str_replace( "{/slide}", "</div>", $content );
      $uniqueSlideID++;
	  $uniqueToggleID++;
    }   
		$copyright = true;
   }

 if (preg_match_all("/{accordion=.+?}/", $content, $matches, PREG_PATTERN_ORDER) > 0) {
    foreach ($matches[0] as $match) {
      $match = str_replace("{accordion=", "", $match);
      $match = str_replace("}", "", $match);
      $title =  wp_tabsSlides_filter_title($match);
       $link = wp_tabsSlides_strip_punctuation(str_replace(" ","-",strtolower($match)));

      $content = str_replace( "{accordion=".$match."}", "<div class=\"wts_title\"><div class=\"wts_title_left\"><a id=\"".$link."\" href=\"javascript:void(null);\" title=\"Click to open!\" class=\"jtoggle\" onclick=\"wtsaccordion('.wts_accordionwrapper".$pid."','#hideslide".$uniqueSlideID.$pid."',$sliderspeed);\">".
$title."</a></div></div><div class=\"wts_accordionwrapper".$pid." slideraccordion\" id=\"hideslide".$uniqueSlideID.$pid."\">", $content );

      $content = str_replace( "{/accordion}", "</div>", $content );
      $uniqueSlideID++;
	  $uniqueToggleID++;
    }   

 		$copyright = true;
   }

	/*
	 Copyright is disabled, do what do you want. if you find this plugin is useful, please donate. or the cat will die.
	if(	$copyright ){
			$content .= "<a href=\"http://ibad.bebasbelanja.com/wordpress-tabs-slides.html\" style=\"display: none;\">Powered By Wordpress Tabs Slides</a>";
	}*/

  
	return $content;
	}
	
	
	// The options page
	function wp_tabsSlides_options(){
		
		$options = $newoptions = get_option('wp_tabs_slides');
		
		if(isset($_POST['submit'])){
			$newoptions['slide-speed'] = intval($_POST['speed']);
			$newoptions['optimized'] = $_POST['optimized'];
			$newoptions['frontenable'] = $_POST['frontenable'];
			
			if($options != $newoptions){
				update_option('wp_tabs_slides',$newoptions);
				$message = "Options Saved";
			}
		}
		
		$options = get_option('wp_tabs_slides');
		
		$slidespeed = $options['slide-speed'];
		$optimized = ($options['optimized']=="on") ? " checked=\"checked\" ":" ";
		$frontenable = ($options['frontenable']=="off") ? " checked=\"checked\" ":" ";

	if(!empty($message)){
		echo '<div class="updated fade" id="message"><p><strong>'.$message.'</strong></p></div>';
	}
	?>
	<div class="wrap">
	<h2>Wordpress Tabs Slides</h2>
	<form action="" method="post">
	<table class="widefat fixed">
<tr valign="top">
<th scope="row" width="150px">Slides Speed</th>
<td><input type="text" name="speed" value="<?php echo $slidespeed;?>" /><br /><small>miliseconds</small></td>
</tr>	
<tr>
<th scope="row">Use optimized loader</th>
<td><input type="checkbox" name="optimized" value="on"<?php echo $optimized;?>/><br /></td>
</tr>
<tr>
<th scope="row">Disable on Frontpage</th>
<td><input type="checkbox" name="frontenable" value="off"<?php echo $frontenable;?>/>
	<small>Disable script on frontpage</td>
</table>
<p class="submit">
<input class="button-primary" type="submit" name="submit" value="Save Changes" />
</p>
</form>
<hr />
<a href="http://ibad.bebasbelanja.com/wordpress-tabs-slides.html" target="_blank">Visit Plugin Home</a> | 
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=3ZM74BGUXB7EE&amp;lc=ID&amp;item_name=Wordpress%20Tabs%20Slides&amp;currency_code=USD&amp;bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" title="Click to donate">Make A Donation</a>
</div>

<?php
	}
	
// Add to wordpress menu
function wp_tabsSlides_menu(){
	add_options_page('Wordpress Tabs Slides','Tabs Slides',10,'tabs-slides','wp_tabsSlides_options');
}


register_activation_hook(__FILE__, 'wp_tabsSlides_activation');


add_action('wp_head','wp_tabsSlides_customhead');

add_action('admin_menu','wp_tabsSlides_menu');
add_filter('the_content','wp_tabsSlides');
add_filter('the_excerpt','wp_tabsSlides');

?>