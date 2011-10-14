<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

        <div id="info" class="col col-1">
				  <?php the_post(); ?>
				  <?php get_template_part( 'content', 'page' ); ?>
				  <?php comments_template( '', true ); ?>
				</div>
				
				
				<?php include 'products.php' ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>
