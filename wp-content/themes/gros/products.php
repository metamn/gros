<div id="products">
  <?php
    global $post;
    $args = array('category_name' => 'produse', 'order' => 'ASC' );
    $myposts = get_posts( $args );
    
    
    
    foreach( $myposts as $post ) :	setup_postdata($post);       
      global $more;    // Declare global $more (before the loop).
      $more = 0; // Display only the excerpt 
    ?>
      <div id="product">         
        <div id="body">
          <a href="<?php the_permalink(); ?>"><?php the_content(''); ?></a>
        </div>
        <div id="title">
          <h1>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          </h1>
        </div>                   
      </div>
    <?php endforeach; ?>
</div>
