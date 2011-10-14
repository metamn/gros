<div id="products" class="col col-2">
  <ul>
    <?php
      global $post;
      $args = array('category_name' => 'produse', 'order' => 'ASC' );
      $myposts = get_posts( $args );
      
      
      
      foreach( $myposts as $post ) :	setup_postdata($post);       
        global $more;    // Declare global $more (before the loop).
        $more = 0; // Display only the excerpt 
      ?>
        <li>
          <h1>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          </h1>
          <?php the_content(''); ?>
        </li>
      <?php endforeach; ?>
  </ul>
</div>
