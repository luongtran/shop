<?php
/**
 * Single Post Template
 *
 * …
 * 
 * @package Thematic
 * @subpackage Templates
 */

    // calling the header.php
    get_header();

    // action hook for placing content above #container
    thematic_abovecontainer();
?>

		<div id="main-content">
			
			<?php
				// action hook for placing content above #content
				thematic_abovecontent();
	            // start the loop
	            while ( have_posts() ) : the_post();
    	        
    	        // create the navigation above the content
				thematic_navigation_above();
		
    	        // action hook creating the single post
    	        thematic_singlepost();
    	        // create the navigation below the content
				
    	        //thematic_comments_template();
    	        
    	        // end the loop
        		endwhile;
		
    	        // calling the widget area 'single-bottom'
    	        
			?>
			
			<?php
				// action hook for placing content below #content
				thematic_belowcontent();
			?> 
		</div><!-- #container -->
		
<?php 
    // action hook for placing content below #container
    thematic_belowcontainer();
    // calling footer.php
    get_footer();
?>