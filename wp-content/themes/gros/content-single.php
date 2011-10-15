<?php
/**
 * The template for displaying content in the single.php template
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

<?php
    $current = $post;
    
    global $post;
    $args = array('category_name' => 'produse', 'order' => 'ASC' );
    $myposts = get_posts( $args );
    
    global $more;    // Declare global $more (before the loop).
    $more = 1; // Display only the excerpt 
    $index = 1;
    foreach( $myposts as $post ) :	setup_postdata($post);       
      if ($current->ID == $post->ID) {
        $active = 'active';
      } else {
        $active = '';
      }
      include("product.php");
			$index += 1;			
    endforeach; 
?>

