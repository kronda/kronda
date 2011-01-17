<div id="sidebar">
	<ul>
		<?php if(!dynamic_sidebar()) : ?>
			<?php  
			$args = array(
			    'before_widget' => '<li id="search-3" class="widget widget_search">',
			    'after_widget' => '</li>',
			    'before_title' => '<h3 class="widgettitle">',
			    'after_title' => '</h3>',
			);
			?>
			<?php the_widget('WP_Widget_Search', array('title' => ''), $args); ?> 
			<?php $args['before_widget'] = '<li id="%1$s" class="widget theme-widget">'; ?>
			<?php the_widget('ApolloLatestPosts', array('title' => 'Recent Posts', 'count' => '3'), $args); ?> 
			<?php $args['before_widget'] = '<li id="%1$s" class="widget theme-widget">'; ?>
			<?php the_widget('ApolloRecentComments', array('title' => 'Recent Posts', 'count' => '3'), $args); ?> 
			<?php $args['before_widget'] = '<li id="%1$s" class="widget widget_archive">'; ?>
			<?php the_widget('WP_Widget_Archives', array('title' => 'Archives', 'count' => '3', 'dropdown'=>'0'), $args); ?> 
		<?php endif ?>
	</ul>
</div>
