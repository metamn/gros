<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

	</div><!-- #main -->

	<footer id="colophon" role="contentinfo">
			<div id="copyright"  class="col col-1">
		    &copy; 2011 <a title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		    <?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?></a>
		  </div>
			
			<div  class="col col-2">
			  <nav id="access" role="navigation" class="footer">
		      <?php wp_nav_menu( array( 'theme_location' => 'footer' ) ); ?>
		    </nav>
		  </div>
		  
	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
