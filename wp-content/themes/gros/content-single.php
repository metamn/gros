<?php
/**
 * The template for displaying content in the single.php template
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h1 class="entry-title"><a title="<?php the_title(); ?>" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
		<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', 'twentyeleven' ) . '</span>', 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->

<aside>
  <div id="products">
    <ul>
      <?php
        $current = $post->ID;
        
        global $post;
        $args = array('category_name' => 'produse', 'order' => 'ASC' );
        $myposts = get_posts( $args );
        
        foreach( $myposts as $post ) :	setup_postdata($post);
          if ($post->ID != $current) { ?>
          <li>
            <h1>
              <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h1>
            <?php the_content(); ?>
          </li>
        <?php } endforeach; ?>
    </ul>
  </div>
</aside>

<div class="clear"></div>
