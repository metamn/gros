<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 */

get_header(); ?>

    <div id="primary">
		  <div id="content" role="main">
        
        <div id="info" class="col col-1">
          <div id="question">
            <p>
              Aveti un magazin si cautati un furnizor pentru noi produse? 
              <br/><br/>
              Sunteti o firma de publicitate sau companie?
            </p>
          </div>
          <div class="triangle triangle-bottom"></div>
          <div id="answer">
            <p>
              Este un produs de succes, foarte simplu si uimitor. Daca aveti un magazin sau un shop online, noi dorim sa devenim furnizorul Dvs.
              Afacerea Dvs. este mult apreciata! 
            </p>
          </div>
        </div>
        
        <div id="products" class="col col-2">
          <ul>
            <?php
              global $post;
              $args = array('category_name' => 'produse', 'order' => 'ASC' );
              $myposts = get_posts( $args );
              
              foreach( $myposts as $post ) :	setup_postdata($post); ?>
	              <li>
	                <h1>
	                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	                </h1>
	                <?php the_content(''); ?>
	              </li>
              <?php endforeach; ?>
          </ul>
        </div>
        
        
			</div><!-- #content -->
		</div><!-- #primary -->		
		
<?php get_footer(); ?>

