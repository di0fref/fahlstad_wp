<?php 
/*
Template Name: Wide
*/

get_header(); ?>
<div id="content_wide">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
										
				<h2 class="entry_title"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_attr( sprintf( __( 'Permanent Link to %s' ), the_title_attribute( 'echo=false' ) ) ); ?>"><?php the_title(); ?></a></h2>
				<p class="entry_meta"></p>
				<div class="entry clear">
					<?php the_content(); ?>					
				</div><!--end entry-->
				<div class="entry_footer">
						<div class="entry_footer_block"></div>
						<div class="entry_footer_block right"><?php comments_popup_link( __( '0' ), __( '1' ), __( '%' ), "comment_link has_icon" ); ?></div>
				<?php edit_post_link("edit")?>
				</div><!--end post footer-->
			</div><!--end post-->
		<?php endwhile; /* rewind or continue if all posts have been fetched */ ?>
	<?php endif; ?>
</div><!--end content-->
<?php get_footer(); ?>