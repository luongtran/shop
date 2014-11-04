
<?php
    get_header();

    // action hook for placing content above #container
    thematic_abovecontainer();
?>
               
            <div id="main-content" class="pakaging">
		<?php	
	            // start the loop
	            while ( have_posts() ) : the_post();
                    // action hook for placing content above #post
	            thematic_abovepost();
	        ?>
    	     		
				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?> > 
	                
					<div class="entry-content row">
                                            <div class="col-sm-4" id="package-page-content">
                                                <h1><?php the_title() ?></h1>
                                                <div>
                                                    <?php
                                                        the_content();

                                                        wp_link_pages( "\t\t\t\t\t<div class='page-link'>" . __( 'Pages: ', 'thematic' ), "</div>\n", 'number' );

                                                        edit_post_link( __( 'Edit', 'thematic' ), "\n\t\t\t\t\t\t" . '<span class="edit-link">' , '</span>' . "\n" );
                                                    ?>
                                                </div>
                                            </div>
					</div><!-- .entry-content -->
					
				</div><!-- #post -->
	
			<?php
				// action hook for inserting content below #post
	        	thematic_belowpost();
	        		        
       			// action hook for calling the comments_template
       			//thematic_comments_template();
        		
	        	// end loop
        		endwhile;
	        
	        ?>
	
			</div><!-- #content -->
			
			<?php 
				// action hook for placing content below #content
				thematic_belowcontent(); 
			?> 
			

<?php 
    // action hook for placing content below #container
    thematic_belowcontainer();

    // calling footer.php
    get_footer();
?>