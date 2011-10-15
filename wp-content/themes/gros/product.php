<div id="product" class="c<?php echo $index ?> <?php echo $active ?>">           
  <div id="title">
    <h1>
      <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h1>
  </div>   
  <div id="body">
    <?php the_content(''); ?>
  </div>                
  <div id="shopping">
	  <?php 
	    if ( is_user_logged_in() ) { 
	      echo do_shortcode('[eshop_addtocart]');  
	    } else { ?>
	      <p>
	        Pentru a vedea preturile, va rugam <a title="Intrare in cont" class="button" href="<?php echo esc_url( home_url( '/intrare-cont' ) ); ?>">sa va autentificati</a>. 
	      </p>
	      <p>
	        Daca nu aveti inca cont la noi, va rugam <a title="Inregistrare cont" class="button" href="<?php echo esc_url( home_url( '/inregistrare-cont' ) ); ?>">sa va inregistrati aici</a>.	      
        </p>	      
	   <?php } ?>
	</div>
</div>

