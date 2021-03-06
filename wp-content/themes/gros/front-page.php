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
        
        <div class="col col-1">
          <?php include 'products.php' ?>
        </div>
        
        <div id="info" class="col col-2">
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
        
        
        
        
			</div><!-- #content -->
		</div><!-- #primary -->		
		
<?php get_footer(); ?>

