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
				</div>
				
				<div class="col col-2">
				  <?php include 'products.php' ?>
				</div>
				
				<div id="userinfo" class="hidden">
				  <?php
				    $current_user = wp_get_current_user();
            if (!($current_user->ID == 0)) { ?>
              <span id="name"><?php echo $current_user->user_firstname . $current_user->user_lastname ?></span>
				      <span id="email"><?php echo $current_user->user_email ?></span>
          <?php } ?>				  
				</div>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>
