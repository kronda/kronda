<?php get_header(); ?>
<section id="content" role="main">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<header>
<?php if ( is_singular() ) {echo '<h1 class="entry-title">';} else {echo '<h2 class="entry-title">';} ?><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a><?php if ( is_singular() ) {echo '</h1>';} else {echo '</h2>';} ?> <?php edit_post_link(); ?>
<section class="entry-meta">
<span class="author vcard"><?php the_author_posts_link(); ?></span>
<span class="meta-sep"> | </span>
<span class="entry-date"><?php the_time( get_option('date_format') ); ?></span>
</section>
</header>
<section class="entry-content">
<?php if ( has_post_thumbnail() ) { the_post_thumbnail(); } ?>
<?php the_content(); ?>
<div class="entry-links"><?php wp_link_pages(); ?></div>
</section>
<footer class="entry-footer">
<span class="cat-links"><?php _e( 'Categories: ', 'writer' ); ?><?php the_category(', '); ?></span>
<span class="tag-links"><?php the_tags(); ?></span>
</footer> 
</article>
<?php if ( ! post_password_required() ) comments_template('', true); ?>
<?php endwhile; endif; ?>
<footer class="footer">
<nav id="nav-below" class="navigation" role="navigation">
<div class="nav-previous"><?php previous_post_link('%link', '<span class="meta-nav">&larr;</span> %title'); ?></div>
<div class="nav-next"><?php next_post_link('%link', '%title <span class="meta-nav">&rarr;</span>'); ?></div>
</nav>
</footer>
</section>
<?php get_footer(); ?>