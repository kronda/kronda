
<?php get_header(); ?>

<div id="content">


          
	<div class="postgroup">

			<?php while (have_posts()) : the_post(); ?>
		<div class="post indexpost >
			
           	<div class="entry">
            	
                	      
                    <div class="title">
    
                        <h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
					   
						<div class="postmeta">
                        	    
								<div class="section-1"> By <?php the_author_posts_link(); ?> on <?php the_time('F. j. Y'); ?> | <?php comments_popup_link('No Comments', '1 Comment', '% Comments'); ?> </div> 
								
						</div>
				
				   </div> 
				   <img class="header" src="<?php bloginfo('template_url'); ?>/functions/timthumb.php?src=
						<?php echo themefunction_catch_that_image() ?>&amp;w=540&amp;h=191&amp;zc=1" alt=""/>   	
                	<div class="text">
                    	<div class="twtbutton"><? if (function_exists('tweetmeme')) echo tweetmeme(); ?></div>               
                        <?php themefunction_content(200,'');?>
                    </div>
			
            </div>
      
		
        <div class="post-bot"></div>
		<?php endwhile; ?>
		
	</div>

</div>

<?php get_sidebar(); ?>
<div id="pagenav">
<div id="pagenav-top"> </div>
    <?php 
        if (function_exists('wp_pagenavi')) : 
            wp_pagenavi();  
        else : 
    ?>
    <div class="simplenavi">
        <?php posts_nav_link(' &nbsp;&nbsp;','&laquo; Previous Entries','Next Entries &raquo;') ?></div>
    </div>
    <?php
        endif;
    ?>
</div>
<div id="pagenav-bottom"> </div>
<?php get_footer(); ?>
